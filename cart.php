<?php
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/image.php';

$page_title = 'Mon panier';
$page_desc  = 'Votre panier GreenAmal · finalisez votre commande de produits naturels du Maroc.';
$nav        = '';
$noindex    = true;
$body_class = 'gd-2026';
$extra_css  = ['/assets/css/home.css'];

$cart     = cart_get();
$subtotal = cart_subtotal();
$shipping = $subtotal >= FREE_SHIPPING_THRESHOLD || $subtotal === 0.0 ? 0 : SHIPPING_FEE;

$coupon   = $_SESSION['coupon'] ?? null;
$discount = 0;
if ($coupon) {
    if ($coupon['type'] === 'free_shipping') {
        $shipping = 0;
    } else {
        $discount = coupon_discount($coupon, $cart);
    }
}
$total = max(0, $subtotal + $shipping - $discount);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['coupon_code'])) {
    csrf_verify();
    if (!rate_limit('coupon_apply', 8, 60)) {
        $_SESSION['coupon_error'] = 'Trop de tentatives. Réessayez dans une minute.';
        redirect('panier');
    }
    $code = strtoupper(trim((string) $_POST['coupon_code']));
    $c = db_one("
        SELECT * FROM coupons
        WHERE code = ?
          AND status = 'active'
          AND (starts_at  IS NULL OR starts_at  <= NOW())
          AND (expires_at IS NULL OR expires_at >  NOW())
          AND (max_uses   IS NULL OR uses_count <  max_uses)
    ", [$code]);

    if (!$c) {
        $_SESSION['coupon_error'] = 'Code invalide ou expiré.';
    } elseif ($subtotal < (float) $c['min_order']) {
        $_SESSION['coupon_error'] = 'Commande minimum : ' . price($c['min_order']) . '.';
    } elseif ($c['type'] !== 'free_shipping' && coupon_eligible_subtotal($c, $cart) <= 0) {
        $_SESSION['coupon_error'] = 'Aucun produit éligible à ce coupon dans votre panier.';
    } else {
        $_SESSION['coupon'] = $c;
        unset($_SESSION['coupon_error']);
    }
    redirect('panier');
}

require __DIR__ . '/includes/header.php';
?>

<section class="cart-page">
  <div class="container">
    <div class="crumbs">
      <a href="/">Accueil</a><span class="sep">/</span><span>Mon panier</span>
    </div>

    <div class="cart-head">
      <h1>Mon <em>panier</em>.</h1>
      <p class="muted"><?= cart_count() ?> article<?= cart_count() > 1 ? 's' : '' ?></p>
    </div>

    <?php if (empty($cart)): ?>
      <div class="cart-empty">
        <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4">
          <path d="M5 7h14l-1.5 11.5a2 2 0 0 1-2 1.5h-7a2 2 0 0 1-2-1.5L5 7Z"/>
          <path d="M9 7V5a3 3 0 0 1 6 0v2"/>
        </svg>
        <h2 class="h-serif">Votre panier est vide</h2>
        <p>Découvrez nos produits naturels du Maroc.</p>
        <a href="/boutique" class="h-btn h-btn-primary h-btn-lg">
          Voir la boutique
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </a>
      </div>
    <?php else: ?>

      <?php if ($subtotal < FREE_SHIPPING_THRESHOLD): ?>
        <div class="cart-ship-bar">
          <p>Plus que <strong><?= price(FREE_SHIPPING_THRESHOLD - $subtotal) ?></strong> pour la livraison gratuite</p>
          <div class="progress-track"><div class="progress-fill" style="width: <?= min(100, ($subtotal / FREE_SHIPPING_THRESHOLD) * 100) ?>%;"></div></div>
        </div>
      <?php else: ?>
        <div class="cart-ship-bar is-unlocked">
          <p>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="9 12 12 15 16 10"/></svg>
            <strong>Livraison gratuite débloquée !</strong>
          </p>
          <div class="progress-track"><div class="progress-fill" style="width: 100%;"></div></div>
        </div>
      <?php endif; ?>

      <div class="cart-grid">
        <div class="cart-list">
          <?php foreach ($cart as $item): ?>
            <article class="cart-item" data-product-id="<?= (int) $item['id'] ?>">
              <a href="/product?slug=<?= e($item['slug'] ?? '') ?>" class="cart-item-thumb">
                <?= picture_tag($item['image'], $item['name'], ['lazy' => true, 'width' => 120, 'height' => 120]) ?>
              </a>
              <div class="cart-item-info">
                <h4 class="cart-item-name"><a href="/product?slug=<?= e($item['slug'] ?? '') ?>"><?= e($item['name']) ?></a></h4>
                <div class="cart-item-meta"><?= price($item['price']) ?> · l'unité</div>
                <div class="qty-mini">
                  <button type="button" aria-label="Diminuer">−</button>
                  <input type="text" value="<?= (int) $item['qty'] ?>" readonly>
                  <button type="button" aria-label="Augmenter">+</button>
                </div>
              </div>
              <div class="cart-item-side">
                <span class="cart-item-price"><?= price($item['price'] * $item['qty']) ?></span>
                <button type="button" class="cart-item-remove" data-cart-remove="<?= (int) $item['id'] ?>" aria-label="Supprimer">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-2 14a2 2 0 01-2 2H9a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/></svg>
                  Supprimer
                </button>
              </div>
            </article>
          <?php endforeach; ?>

          <a href="/boutique" class="cart-keep-shopping">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            Continuer mes achats
          </a>
        </div>

        <aside class="cart-summary">
          <h3 class="h-serif">Récapitulatif</h3>

          <div class="summary-row">
            <span>Sous-total (<?= cart_count() ?> art.)</span>
            <span><?= price($subtotal) ?></span>
          </div>
          <div class="summary-row">
            <span>Livraison</span>
            <span><?= $shipping > 0 ? price($shipping) : '<b style="color:var(--forest-700);">Gratuite</b>' ?></span>
          </div>
          <?php if ($discount > 0): ?>
            <div class="summary-row is-discount">
              <span>Réduction <span class="muted">(<?= e($coupon['code']) ?>)</span></span>
              <span>−<?= price($discount) ?></span>
            </div>
          <?php endif; ?>

          <form method="post" class="cart-coupon">
            <?= csrf_field() ?>
            <input type="text" name="coupon_code" placeholder="Code promo" value="<?= e($coupon['code'] ?? '') ?>">
            <button type="submit">Appliquer</button>
          </form>
          <?php if (!empty($_SESSION['coupon_error'])): ?>
            <p class="cart-coupon-error"><?= e($_SESSION['coupon_error']) ?></p>
            <?php unset($_SESSION['coupon_error']); ?>
          <?php endif; ?>

          <div class="summary-row total">
            <span>Total</span>
            <span class="total-amount"><?= price($total) ?></span>
          </div>

          <a href="/paiement" class="h-btn h-btn-primary h-btn-lg" style="width:100%; margin-top:20px;">
            Passer la commande
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          </a>

          <div class="cart-secure">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V8a5 5 0 0110 0v3"/></svg>
            Paiement sécurisé · Données chiffrées
          </div>

          <div class="cart-cod-badge">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="6" width="18" height="13" rx="2"/><path d="M3 10h18"/></svg>
            Paiement à la livraison disponible
          </div>
        </aside>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
