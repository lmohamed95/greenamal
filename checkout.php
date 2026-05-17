<?php
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/mail.php';

$cart = cart_get();
if (empty($cart)) {
    redirect('boutique');
}

$page_title = 'Commande · Finaliser';
$page_desc  = 'Finalisez votre commande GreenAmal · paiement à la livraison ou carte bancaire.';
$nav        = '';
$noindex    = true;
$body_class = 'gd-2026';
$extra_css  = ['/assets/css/home.css'];

$subtotal = cart_subtotal();
$shipping = $subtotal >= FREE_SHIPPING_THRESHOLD ? 0 : SHIPPING_FEE;

$coupon = $_SESSION['coupon'] ?? null;
$discount = 0;
if ($coupon) {
    if ($coupon['type'] === 'free_shipping') {
        $shipping = 0;
    } else {
        $discount = coupon_discount($coupon, $cart);
    }
}
$total = max(0, $subtotal + $shipping - $discount);

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $f = $_POST;
    $required = ['shipping_name', 'shipping_email', 'shipping_phone', 'shipping_address', 'shipping_city'];
    foreach ($required as $r) {
        if (empty(trim($f[$r] ?? ''))) $errors[] = "Le champ \"$r\" est requis.";
    }
    if (!filter_var($f['shipping_email'] ?? '', FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Adresse email invalide.';
    }

    // Stock guard: refuse the order if any item is out of stock or quantity exceeds stock
    if (empty($errors)) {
        $ids = array_values(array_map(fn($i) => (int) $i['id'], $cart));
        if ($ids) {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stocks = db_all("SELECT id, name, stock, status FROM products WHERE id IN ($placeholders)", $ids);
            $by_id = array_column($stocks, null, 'id');
            foreach ($cart as $item) {
                $p = $by_id[(int) $item['id']] ?? null;
                if (!$p || $p['status'] !== 'active') {
                    $errors[] = 'Le produit « ' . e($item['name']) . ' » n\'est plus disponible.';
                } elseif ((int) $p['stock'] <= 0) {
                    $errors[] = 'Rupture de stock pour « ' . e($p['name']) . ' ». Retirez-le du panier pour continuer.';
                } elseif ((int) $item['qty'] > (int) $p['stock']) {
                    $errors[] = 'Stock insuffisant pour « ' . e($p['name']) . ' » (max : ' . (int) $p['stock'] . ').';
                }
            }
        }
    }

    if (empty($errors)) {
        // Create or fetch customer
        $email = trim($f['shipping_email']);
        $customer = db_one('SELECT id FROM customers WHERE email = ?', [$email]);
        if (!$customer) {
            $customer_id = db_insert('customers', [
                'email'      => $email,
                'first_name' => trim(explode(' ', $f['shipping_name'])[0]),
                'last_name'  => trim(implode(' ', array_slice(explode(' ', $f['shipping_name']), 1))),
                'phone'      => trim($f['shipping_phone']),
                'city'       => trim($f['shipping_city']),
                'segment'    => 'new',
            ]);
        } else {
            $customer_id = (int) $customer['id'];
        }

        // Create order in a single transaction so stock decrement, items and order
        // are all-or-nothing. Atomic UPDATE…WHERE stock>=? prevents overselling
        // when two checkouts race for the last unit.
        $order_id = null;
        $order_number = null;
        $order_error = null;
        $pdo = db();

        try {
            $pdo->beginTransaction();

            // Insert order — retry on the (rare) order_number duplicate race
            for ($attempt = 0; $attempt < 5; $attempt++) {
                try {
                    $order_number = next_order_number();
                    $order_id = db_insert('orders', [
                        'order_number'      => $order_number,
                        'customer_id'       => $customer_id,
                        'status'            => 'pending',
                        'payment_method'    => 'cod',  // COD-only for now (CMI / virement à venir)
                        'payment_status'    => 'pending',
                        'subtotal'          => $subtotal,
                        'shipping'          => $shipping,
                        'discount'          => $discount,
                        'total'             => $total,
                        'shipping_name'     => trim($f['shipping_name']),
                        'shipping_email'    => $email,
                        'shipping_phone'    => trim($f['shipping_phone']),
                        'shipping_address'  => trim($f['shipping_address']),
                        'shipping_city'     => trim($f['shipping_city']),
                        'shipping_postcode' => trim($f['shipping_postcode'] ?? ''),
                        'notes'             => trim($f['notes'] ?? ''),
                        'coupon_code'       => $coupon['code'] ?? null,
                    ]);
                    break;
                } catch (PDOException $e) {
                    if ($e->getCode() !== '23000') throw $e;
                    $order_id = null;
                }
            }
            if (!$order_id) throw new RuntimeException('order_number_collision');

            // Insert order items
            foreach ($cart as $item) {
                db_insert('order_items', [
                    'order_id'      => $order_id,
                    'product_id'    => $item['id'],
                    'product_name'  => $item['name'],
                    'product_image' => $item['image'],
                    'unit_price'    => $item['price'],
                    'quantity'      => $item['qty'],
                    'total'         => $item['price'] * $item['qty'],
                ]);
            }

            // Atomic stock decrement — refuses if another concurrent order
            // already drained the stock since our pre-check.
            foreach ($cart as $item) {
                $stmt = db_query(
                    'UPDATE products SET stock = stock - ?, sales_count = sales_count + ?
                       WHERE id = ? AND stock >= ?',
                    [(int) $item['qty'], (int) $item['qty'], (int) $item['id'], (int) $item['qty']]
                );
                if ($stmt->rowCount() !== 1) {
                    throw new RuntimeException('stock_unavailable:' . $item['name']);
                }
            }

            // Order event
            db_insert('order_events', [
                'order_id'    => $order_id,
                'event_type'  => 'created',
                'description' => 'Commande créée par le client',
                'created_by'  => 'client',
            ]);

            if ($coupon) {
                db_query('UPDATE coupons SET uses_count = uses_count + 1 WHERE id = ?', [$coupon['id']]);
            }

            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $order_id = null;
            if (str_starts_with($e->getMessage(), 'stock_unavailable:')) {
                $errors[] = 'Stock insuffisant pour « ' . e(substr($e->getMessage(), 18)) . ' ». Merci de réessayer.';
            } else {
                error_log('Checkout failed: ' . $e->getMessage());
                $errors[] = 'Impossible de créer la commande, veuillez réessayer.';
            }
        }

        if ($order_id) {
            // Send transactional emails (order confirmation + admin notification)
            $order_row = db_one('SELECT * FROM orders WHERE id = ?', [$order_id]);
            $items_row = db_all('SELECT product_name, quantity, total FROM order_items WHERE order_id = ?', [$order_id]);
            if ($order_row) {
                @mail_order_confirmation($order_row, $items_row);
                @mail_admin_new_order($order_row);
            }

            // Clear cart
            cart_clear();
            unset($_SESSION['coupon']);

            // Allow this browser session to view the just-placed order without leaking
            // every order to anyone who guesses an order number (IDOR fix).
            $_SESSION['allowed_orders'] = $_SESSION['allowed_orders'] ?? [];
            $_SESSION['allowed_orders'][] = (int) $order_id;
            $_SESSION['allowed_orders'] = array_values(array_unique(array_slice($_SESSION['allowed_orders'], -20)));

            // Redirect to thank-you
            redirect('confirmation-commande?order=' . urlencode($order_number));
        }
    }
}

require __DIR__ . '/includes/header.php';
?>

<section class="checkout-page">
  <div class="container">
    <div class="crumbs">
      <a href="/">Accueil</a><span class="sep">/</span><a href="/panier">Panier</a><span class="sep">/</span><span>Finaliser</span>
    </div>

    <div class="checkout-head">
      <h1>Finaliser ma <em>commande</em>.</h1>
      <p class="muted">Sécurisé · Livraison 24-48h · Paiement à la livraison disponible</p>
    </div>

    <?php if (!empty($errors)): ?>
      <div class="checkout-errors">
        <?php foreach ($errors as $err): ?><div>• <?= e($err) ?></div><?php endforeach; ?>
      </div>
    <?php endif; ?>

    <form method="post" class="checkout-grid">
      <?= csrf_field() ?>

      <div class="checkout-main">
        <div class="checkout-card">
          <h3 class="h-serif">Adresse de livraison</h3>
          <div class="gd-form-grid" style="margin-top:14px;">
            <div class="gd-field gd-field-full">
              <label>Nom complet <span class="req">*</span></label>
              <input type="text" name="shipping_name" required value="<?= e($_POST['shipping_name'] ?? '') ?>">
            </div>
            <div class="gd-field">
              <label>Email <span class="req">*</span></label>
              <input type="email" name="shipping_email" required value="<?= e($_POST['shipping_email'] ?? '') ?>">
            </div>
            <div class="gd-field">
              <label>Téléphone <span class="req">*</span></label>
              <input type="tel" name="shipping_phone" required value="<?= e($_POST['shipping_phone'] ?? '') ?>" placeholder="+212 …">
            </div>
            <div class="gd-field gd-field-full">
              <label>Adresse <span class="req">*</span></label>
              <input type="text" name="shipping_address" required value="<?= e($_POST['shipping_address'] ?? '') ?>" placeholder="Rue, n°, appartement, étage…">
            </div>
            <div class="gd-field">
              <label>Ville <span class="req">*</span></label>
              <input type="text" name="shipping_city" required value="<?= e($_POST['shipping_city'] ?? '') ?>">
            </div>
            <div class="gd-field">
              <label>Code postal</label>
              <input type="text" name="shipping_postcode" value="<?= e($_POST['shipping_postcode'] ?? '') ?>">
            </div>
            <div class="gd-field gd-field-full">
              <label>Notes <span class="muted" style="font-weight:400;">(optionnel)</span></label>
              <textarea name="notes" placeholder="Instructions pour le livreur, point de repère…"><?= e($_POST['notes'] ?? '') ?></textarea>
            </div>
          </div>
        </div>

        <div class="checkout-card">
          <h3 class="h-serif">Méthode de paiement</h3>
          <input type="hidden" name="payment_method" value="cod">
          <div class="payment-method active">
            <div class="payment-icon">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="3"/></svg>
            </div>
            <div>
              <strong>Paiement à la livraison</strong>
              <div class="payment-desc">Vous payez en espèces à la réception du colis. Aucun frais supplémentaire.</div>
            </div>
            <div class="payment-check">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="m5 12 5 5L20 7"/></svg>
            </div>
          </div>
          <p class="muted" style="font-size:11.5px; margin-top:12px;">Le paiement par carte bancaire et le virement seront disponibles prochainement.</p>
        </div>
      </div>

      <aside class="cart-summary checkout-summary">
        <h3 class="h-serif">Votre commande</h3>

        <div class="checkout-items">
          <?php foreach ($cart as $item): ?>
            <div class="checkout-line">
              <div class="checkout-line-thumb">
                <img src="<?= e($item['image']) ?>" alt="<?= e($item['name']) ?>" loading="lazy">
                <span class="qty-pill"><?= (int) $item['qty'] ?></span>
              </div>
              <div class="checkout-line-name"><?= e($item['name']) ?></div>
              <strong class="checkout-line-price"><?= price($item['price'] * $item['qty']) ?></strong>
            </div>
          <?php endforeach; ?>
        </div>

        <div class="summary-row"><span>Sous-total</span><span><?= price($subtotal) ?></span></div>
        <div class="summary-row"><span>Livraison</span><span><?= $shipping > 0 ? price($shipping) : '<b style="color:var(--forest-700);">Gratuite</b>' ?></span></div>
        <?php if ($discount > 0): ?>
          <div class="summary-row is-discount"><span>Réduction</span><span>−<?= price($discount) ?></span></div>
        <?php endif; ?>
        <div class="summary-row total">
          <span>Total</span>
          <span class="total-amount"><?= price($total) ?></span>
        </div>

        <button type="submit" class="h-btn h-btn-primary h-btn-lg" style="width:100%; margin-top:20px;">
          Confirmer la commande
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </button>

        <div class="cart-secure">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V8a5 5 0 0110 0v3"/></svg>
          Paiement sécurisé · données chiffrées
        </div>
      </aside>
    </form>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
