<?php
/**
 * GreenAmal · Helpers
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

/** Escape for HTML output */
function e(?string $s): string {
    return htmlspecialchars($s ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Format a price as "129 DH" */
function price(float $amount, int $decimals = 0): string {
    return number_format($amount, $decimals, ',', ' ') . ' ' . CURRENCY_SYMBOL;
}

/** Build a URL relative to the site root */
function url(string $path = ''): string {
    return rtrim(SITE_URL, '/') . '/' . ltrim($path, '/');
}

/** Generate a slug from a string (e.g. "Huile d'argan" → "huile-dargan") */
function slugify(string $str): string {
    $str = mb_strtolower(trim($str), 'UTF-8');
    $str = preg_replace('/[^\p{L}\p{N}\s-]/u', '', $str);
    $str = preg_replace('/[\s-]+/', '-', $str);
    return trim($str, '-');
}

/** "Il y a 3 heures" / "Aujourd'hui" relative time */
function time_ago(string $datetime): string {
    $ts = strtotime($datetime);
    $diff = time() - $ts;
    if ($diff < 60) return 'À l\'instant';
    if ($diff < 3600) return 'Il y a ' . floor($diff / 60) . ' min';
    if ($diff < 86400) return 'Il y a ' . floor($diff / 3600) . ' h';
    if ($diff < 172800) return 'Hier';
    if ($diff < 604800) return 'Il y a ' . floor($diff / 86400) . ' jours';
    return date('j M', $ts);
}

/** Customer initials from a name */
function initials(string $name): string {
    $parts = preg_split('/\s+/', trim($name));
    $first = mb_substr($parts[0] ?? '', 0, 1, 'UTF-8');
    $last = mb_substr(end($parts) ?: '', 0, 1, 'UTF-8');
    return mb_strtoupper($first . $last, 'UTF-8');
}

/** Translate order status to French + CSS class */
function order_status_label(string $status): array {
    return match($status) {
        'pending'    => ['En attente', 'status-warning'],
        'processing' => ['Préparation', 'status-info'],
        'shipped'    => ['Expédié', 'status-info'],
        'delivered'  => ['Livré', 'status-success'],
        'cancelled'  => ['Annulé', 'status-danger'],
        default      => [ucfirst($status), 'status-neutral'],
    };
}

function payment_method_label(string $method): array {
    return match($method) {
        'cmi'      => ['CMI', 'status-info'],
        'cod'      => ['COD', 'status-neutral'],
        'transfer' => ['Virement', 'status-neutral'],
        default    => [ucfirst($method), 'status-neutral'],
    };
}

function product_status_label(string $status): array {
    return match($status) {
        'active'   => ['Actif', 'status-success'],
        'draft'    => ['Brouillon', 'status-warning'],
        'archived' => ['Archivé', 'status-neutral'],
        default    => [ucfirst($status), 'status-neutral'],
    };
}

function customer_segment_label(string $segment): array {
    return match($segment) {
        'vip'      => ['VIP', 'status-success'],
        'regular'  => ['Régulier', 'status-info'],
        'new'      => ['Nouveau', 'status-warning'],
        'inactive' => ['Inactif', 'status-neutral'],
        default    => [ucfirst($segment), 'status-neutral'],
    };
}

/** Stock level → CSS color */
function stock_level(int $stock, int $threshold = 10): array {
    if ($stock === 0)         return ['Rupture', 'danger'];
    if ($stock <= 3)          return [$stock . ' restants', 'danger'];
    if ($stock <= $threshold) return [$stock . ' restants', 'warning'];
    return [$stock . ' en stock', 'success'];
}

/** Mark a nav link as active */
function nav_active(string $current, string $page): string {
    return $current === $page ? ' active' : '';
}

/** Generate next order number (e.g. GA-2026-0313) */
function next_order_number(): string {
    $year = date('Y');
    $count = (int) db_value(
        "SELECT COUNT(*) FROM orders WHERE YEAR(created_at) = ?",
        [$year]
    );
    return sprintf('GA-%d-%04d', $year, $count + 1);
}

/** Cart helpers */
function cart_get(): array {
    return $_SESSION['cart'] ?? [];
}

function cart_count(): int {
    $total = 0;
    foreach (cart_get() as $item) {
        $total += (int) $item['qty'];
    }
    return $total;
}

function cart_subtotal(): float {
    $total = 0.0;
    foreach (cart_get() as $item) {
        $total += (float) $item['price'] * (int) $item['qty'];
    }
    return $total;
}

/**
 * Returns the eligible subtotal for a coupon, given the current cart.
 * Honors applies_to = 'all' | 'products' | 'categories'.
 * Returns 0.0 when nothing in the cart is eligible.
 */
function coupon_eligible_subtotal(array $coupon, array $cart): float {
    if (empty($cart)) return 0.0;
    if (($coupon['applies_to'] ?? 'all') === 'all') {
        $sum = 0.0;
        foreach ($cart as $i) $sum += (float) $i['price'] * (int) $i['qty'];
        return $sum;
    }

    $ids = array_values(array_map(fn($i) => (int) $i['id'], $cart));
    if (!$ids) return 0.0;
    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    if ($coupon['applies_to'] === 'products') {
        $eligible = array_column(
            db_all("SELECT product_id FROM coupon_products WHERE coupon_id = ? AND product_id IN ($placeholders)",
                array_merge([(int) $coupon['id']], $ids)),
            'product_id'
        );
    } else { // categories
        $eligible = array_column(
            db_all("SELECT p.id FROM products p WHERE p.id IN ($placeholders) AND p.category_id IN (
                  SELECT category_id FROM coupon_categories WHERE coupon_id = ?)",
                array_merge($ids, [(int) $coupon['id']])),
            'id'
        );
    }
    $eligible = array_map('intval', $eligible);
    if (!$eligible) return 0.0;

    $sum = 0.0;
    foreach ($cart as $i) {
        if (in_array((int) $i['id'], $eligible, true)) {
            $sum += (float) $i['price'] * (int) $i['qty'];
        }
    }
    return $sum;
}

/**
 * Computes the actual discount amount in DH for a coupon + cart.
 * Free shipping returns 0 (handled separately as shipping cost).
 */
function coupon_discount(array $coupon, array $cart): float {
    $eligible = coupon_eligible_subtotal($coupon, $cart);
    if ($eligible <= 0) return 0.0;
    return match ($coupon['type']) {
        'percent' => round($eligible * ($coupon['value'] / 100), 2),
        'fixed'   => min((float) $coupon['value'], $eligible),
        default   => 0.0,
    };
}

function cart_add(int $product_id, int $qty = 1): bool {
    $product = db_one('SELECT id, name, slug, price, image_main, stock FROM products WHERE id = ? AND status = "active"', [$product_id]);
    if (!$product) return false;
    if ((int) $product['stock'] <= 0) return false;
    $cart = cart_get();
    $key = (string) $product_id;
    $current_qty = $cart[$key]['qty'] ?? 0;
    $new_qty = min($current_qty + $qty, (int) $product['stock']);
    $cart[$key] = [
        'id'    => $product['id'],
        'name'  => $product['name'],
        'slug'  => $product['slug'],
        'price' => (float) $product['price'],
        'image' => $product['image_main'],
        'qty'   => $new_qty,
    ];
    $_SESSION['cart'] = $cart;
    return true;
}

function cart_update(int $product_id, int $qty): void {
    $cart = cart_get();
    $key = (string) $product_id;
    if (!isset($cart[$key])) return;
    if ($qty <= 0) {
        unset($cart[$key]);
    } else {
        $cart[$key]['qty'] = $qty;
    }
    $_SESSION['cart'] = $cart;
}

function cart_remove(int $product_id): void {
    $cart = cart_get();
    unset($cart[(string) $product_id]);
    $_SESSION['cart'] = $cart;
}

function cart_clear(): void {
    $_SESSION['cart'] = [];
}

/* =====================================================================
 * SEO helpers
 * =====================================================================*/

/** Absolute base URL of the site (no trailing slash) */
function seo_base(): string {
    return rtrim(SITE_URL, '/');
}

/** Build an absolute URL from a path-or-URL */
function seo_abs(string $path_or_url): string {
    if (preg_match('#^https?://#i', $path_or_url)) return $path_or_url;
    return seo_base() . '/' . ltrim($path_or_url, '/');
}

/** Current request path with optional canonical query whitelist */
function seo_canonical(?string $override = null): string {
    if ($override !== null) return seo_abs($override);
    $path = strtok($_SERVER['REQUEST_URI'] ?? '/', '?') ?: '/';
    // Whitelist canonical query params. Drop sort, page, utm_*, etc.
    $allow = ['slug', 'cat', 'q'];
    $canonical_qs = [];
    foreach ($allow as $key) {
        if (!empty($_GET[$key])) $canonical_qs[$key] = $_GET[$key];
    }
    $qs = $canonical_qs ? '?' . http_build_query($canonical_qs) : '';
    return seo_base() . $path . $qs;
}

/** Encode a value for safe JSON-LD output (ld+json, escape inline </script>) */
function seo_jsonld(array $data): string {
    return str_replace('</', '<\\/', json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
}

/** Site-wide Organization snippet (used on homepage + about) */
function seo_org_jsonld(): array {
    return [
        '@context' => 'https://schema.org',
        '@type'    => 'Organization',
        'name'     => SITE_NAME,
        'alternateName' => 'Coopérative Al Amal',
        'url'      => seo_base(),
        'logo'     => seo_abs('/assets/img/logo.png'),
        'description' => 'Coopérative féminine basée à Azrou, au cœur du Moyen Atlas marocain. Produits naturels certifiés ONSSA.',
        'foundingDate' => '2008',
        'address' => [
            '@type' => 'PostalAddress',
            'addressLocality' => 'Azrou',
            'addressCountry'  => 'MA',
        ],
        'contactPoint' => [
            '@type' => 'ContactPoint',
            'telephone' => CONTACT_PHONE,
            'email'     => CONTACT_EMAIL,
            'contactType' => 'customer service',
            'availableLanguage' => ['French', 'Arabic'],
        ],
        'sameAs' => [
            'https://facebook.com/greenamal',
            'https://instagram.com/greenamal',
        ],
    ];
}

/** WebSite snippet with sitelinks search box */
function seo_website_jsonld(): array {
    return [
        '@context' => 'https://schema.org',
        '@type'    => 'WebSite',
        'name'     => SITE_NAME,
        'url'      => seo_base(),
        'inLanguage' => 'fr-MA',
        'potentialAction' => [
            '@type'  => 'SearchAction',
            'target' => seo_base() . '/shop.php?q={search_term_string}',
            'query-input' => 'required name=search_term_string',
        ],
    ];
}

/** Build BreadcrumbList JSON-LD from an array of [name, url] pairs */
function seo_breadcrumb_jsonld(array $items): array {
    $list = [];
    foreach ($items as $i => [$name, $url]) {
        $list[] = [
            '@type'    => 'ListItem',
            'position' => $i + 1,
            'name'     => $name,
            'item'     => seo_abs($url),
        ];
    }
    return [
        '@context' => 'https://schema.org',
        '@type'    => 'BreadcrumbList',
        'itemListElement' => $list,
    ];
}

/** Build Product JSON-LD from a DB row */
function seo_product_jsonld(array $product, ?string $category_name = null): array {
    $offer = [
        '@type'         => 'Offer',
        'url'           => seo_canonical(),
        'priceCurrency' => 'MAD',
        'price'         => number_format((float) $product['price'], 2, '.', ''),
        'availability'  => ($product['stock'] > 0)
            ? 'https://schema.org/InStock'
            : 'https://schema.org/OutOfStock',
        'itemCondition' => 'https://schema.org/NewCondition',
        'seller'        => [
            '@type' => 'Organization',
            'name'  => SITE_NAME,
        ],
    ];

    $data = [
        '@context'    => 'https://schema.org',
        '@type'       => 'Product',
        'name'        => $product['name'],
        'description' => $product['description_short'] ?: $product['description_long'],
        'sku'         => $product['sku'],
        'image'       => seo_abs($product['image_main']),
        'brand'       => ['@type' => 'Brand', 'name' => SITE_NAME],
        'offers'      => $offer,
    ];

    if ($category_name) {
        $data['category'] = $category_name;
    }
    if (!empty($product['rating_count']) && (int) $product['rating_count'] > 0) {
        $data['aggregateRating'] = [
            '@type'       => 'AggregateRating',
            'ratingValue' => number_format((float) $product['rating_avg'], 1, '.', ''),
            'reviewCount' => (int) $product['rating_count'],
            'bestRating'  => '5',
        ];
    }

    return $data;
}

/* =====================================================================
 * End SEO helpers
 * =====================================================================*/

/** JSON response helper */
function json_response($data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/** Redirect helper */
function redirect(string $path): void {
    header('Location: ' . $path);
    exit;
}

/* =====================================================================
 * CSRF protection
 * =====================================================================*/

function csrf_token(): string {
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function csrf_field(): string {
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

/** Reject the request if the CSRF token is missing or wrong. */
function csrf_verify(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
    $given = $_POST['_csrf'] ?? '';
    $stored = $_SESSION['_csrf'] ?? '';
    if (!is_string($given) || !is_string($stored) || !hash_equals($stored, $given)) {
        http_response_code(403);
        die('Erreur de sécurité (CSRF). Veuillez recharger la page.');
    }
}

/* =====================================================================
 * Rate limiting (session + IP based)
 * =====================================================================*/

/**
 * Returns true if allowed, false if the rate limit has been hit.
 * $key: a logical bucket name (e.g. "coupon_apply", "client_login")
 * $max: max attempts in $window seconds
 */
function rate_limit(string $key, int $max, int $window): bool {
    $bucket_key = '_rl_' . $key;
    $now = time();
    $arr = $_SESSION[$bucket_key] ?? [];
    $arr = array_values(array_filter($arr, fn($t) => $now - $t < $window));
    if (count($arr) >= $max) {
        $_SESSION[$bucket_key] = $arr;
        return false;
    }
    $arr[] = $now;
    $_SESSION[$bucket_key] = $arr;
    return true;
}

/* =====================================================================
 * Customer authentication
 * =====================================================================*/

function customer_user(): ?array {
    if (empty($_SESSION['customer_id'])) return null;
    static $user = null;
    if ($user === null) {
        $user = db_one(
            'SELECT id, email, first_name, last_name, phone, city, address, postcode, segment
             FROM customers WHERE id = ?',
            [$_SESSION['customer_id']]
        );
    }
    return $user ?: null;
}

function customer_logged_in(): bool {
    return customer_user() !== null;
}

function customer_require_login(string $redirect_to = 'login.php'): void {
    if (!customer_logged_in()) {
        $next = urlencode($_SERVER['REQUEST_URI'] ?? '/account.php');
        redirect($redirect_to . '?next=' . $next);
    }
}

/** Try to log in a customer. Returns ['ok'=>bool, 'msg'=>string]. */
function customer_login(string $email, string $password): array {
    $row = db_one(
        'SELECT id, password_hash FROM customers WHERE email = ? LIMIT 1',
        [strtolower(trim($email))]
    );
    if (!$row || empty($row['password_hash'])) {
        return ['ok' => false, 'msg' => 'Email ou mot de passe incorrect.'];
    }
    if (!password_verify($password, $row['password_hash'])) {
        return ['ok' => false, 'msg' => 'Email ou mot de passe incorrect.'];
    }
    $_SESSION['customer_id'] = (int) $row['id'];
    db_query('UPDATE customers SET last_login_at = NOW() WHERE id = ?', [$row['id']]);
    return ['ok' => true, 'msg' => 'Connecté.'];
}

function customer_logout(): void {
    unset($_SESSION['customer_id']);
}

/* =====================================================================
 * Flash messages
 * =====================================================================*/

function flash_set(string $type, string $msg): void {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

function flash_pop(): ?array {
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

/* =====================================================================
 * Pack composition (a product made of other products)
 * =====================================================================*/

/**
 * Returns the components of a pack product, ordered by display_order.
 * Each row has: id, slug, name, image_main, price, quantity.
 * Returns [] if the product has no components (i.e. it's a regular product).
 */
function product_components(int $pack_id): array {
    return db_all("
        SELECT p.id, p.slug, p.name, p.image_main, p.price, pc.quantity, pc.display_order
        FROM product_components pc
        JOIN products p ON p.id = pc.component_id
        WHERE pc.pack_id = ? AND p.status = 'active'
        ORDER BY pc.display_order, p.name
    ", [$pack_id]);
}

/** Sum of (component price × component qty) · used to show "vs individual" savings. */
function pack_components_value(array $components): float {
    $sum = 0.0;
    foreach ($components as $c) {
        $sum += (float) $c['price'] * (int) $c['quantity'];
    }
    return $sum;
}

/* =====================================================================
 * Coming-soon mode (toggled from admin)
 * =====================================================================*/

function is_coming_soon(): bool {
    static $cached = null;
    if ($cached !== null) return $cached;
    $val = db_value("SELECT setting_value FROM settings WHERE setting_key = 'coming_soon_mode'");
    $cached = ($val === '1' || $val === 1);
    return $cached;
}

/**
 * Redirect public-storefront visitors to coming-soon.php when the mode is on.
 * Admins (logged in to /admin) and the allowed pages stay accessible.
 *
 * Pages that stay reachable even in coming-soon mode:
 *   - coming-soon.php itself
 *   - contact.php (so people can reach out)
 *   - legal pages (CGV/privacy/mentions/returns/cookies · legally required)
 *   - login/register/forgot-password/reset-password (so customers can manage accounts)
 *   - 404/500 (system pages)
 */
function coming_soon_guard(): void {
    if (!is_coming_soon()) return;
    if (!empty($_SESSION['admin_user_id'])) return; // admin override

    $script = basename($_SERVER['SCRIPT_NAME'] ?? '');
    $allowlist = [
        'coming-soon.php',
        'contact.php',
        'cgv.php', 'privacy.php', 'mentions.php', 'returns.php', 'cookies.php',
        'login.php', 'register.php', 'forgot-password.php', 'reset-password.php',
        '404.php', '500.php',
    ];
    if (in_array($script, $allowlist, true)) return;

    // Storefront request blocked → land them on the coming-soon page.
    header('Location: /coming-soon.php');
    exit;
}
