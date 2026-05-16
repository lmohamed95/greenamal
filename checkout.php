<?php
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/mail.php';

$cart = cart_get();
if (empty($cart)) {
    redirect('shop');
}

$page_title = 'Commande · Finaliser';
$page_desc  = 'Finalisez votre commande GreenAmal · paiement à la livraison ou carte bancaire.';
$nav        = '';
$noindex    = true;

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
            redirect('order-confirmation?order=' . urlencode($order_number));
        }
    }
}

require __DIR__ . '/includes/header.php';
?>

<div class="container breadcrumb">
  <a href="/">Accueil</a><span>/</span><a href="cart">Panier</a><span>/</span><span>Finaliser la commande</span>
</div>

<section style="padding-top: 20px; padding-bottom: 80px;">
  <div class="container">
    <div style="margin-bottom: 32px;">
      <h1 style="font-size: 2.2rem;">Finaliser ma commande</h1>
      <p style="color: var(--ink-soft); margin-top: 8px;">Sécurisé · Livraison 24-48h · Paiement à la livraison disponible</p>
    </div>

    <?php if (!empty($errors)): ?>
      <div style="background: var(--danger-bg, #F8E5DF); border-left: 3px solid var(--danger); padding: 14px 18px; border-radius: var(--radius-sm); margin-bottom: 24px; color: var(--danger);">
        <?php foreach ($errors as $err): ?><div>• <?= e($err) ?></div><?php endforeach; ?>
      </div>
    <?php endif; ?>

    <form method="post" class="cart-layout">
      <?= csrf_field() ?>
      <div style="display: flex; flex-direction: column; gap: 16px;">
        <div style="background: var(--white); border: 1px solid var(--line); border-radius: var(--radius); padding: 28px;">
          <h3 style="margin-bottom: 18px;">Adresse de livraison</h3>
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
            <div style="grid-column: 1/-1; display: flex; flex-direction: column; gap: 6px;">
              <label style="font-size: 0.85rem; font-weight: 500;">Nom complet *</label>
              <input type="text" name="shipping_name" required style="padding: 10px 14px; border: 1px solid var(--line); border-radius: var(--radius-sm);" value="<?= e($_POST['shipping_name'] ?? '') ?>">
            </div>
            <div style="display: flex; flex-direction: column; gap: 6px;">
              <label style="font-size: 0.85rem; font-weight: 500;">Email *</label>
              <input type="email" name="shipping_email" required style="padding: 10px 14px; border: 1px solid var(--line); border-radius: var(--radius-sm);" value="<?= e($_POST['shipping_email'] ?? '') ?>">
            </div>
            <div style="display: flex; flex-direction: column; gap: 6px;">
              <label style="font-size: 0.85rem; font-weight: 500;">Téléphone *</label>
              <input type="tel" name="shipping_phone" required style="padding: 10px 14px; border: 1px solid var(--line); border-radius: var(--radius-sm);" value="<?= e($_POST['shipping_phone'] ?? '') ?>" placeholder="+212 ...">
            </div>
            <div style="grid-column: 1/-1; display: flex; flex-direction: column; gap: 6px;">
              <label style="font-size: 0.85rem; font-weight: 500;">Adresse *</label>
              <input type="text" name="shipping_address" required style="padding: 10px 14px; border: 1px solid var(--line); border-radius: var(--radius-sm);" value="<?= e($_POST['shipping_address'] ?? '') ?>" placeholder="Rue, n°, appartement, étage...">
            </div>
            <div style="display: flex; flex-direction: column; gap: 6px;">
              <label style="font-size: 0.85rem; font-weight: 500;">Ville *</label>
              <input type="text" name="shipping_city" required style="padding: 10px 14px; border: 1px solid var(--line); border-radius: var(--radius-sm);" value="<?= e($_POST['shipping_city'] ?? '') ?>">
            </div>
            <div style="display: flex; flex-direction: column; gap: 6px;">
              <label style="font-size: 0.85rem; font-weight: 500;">Code postal</label>
              <input type="text" name="shipping_postcode" style="padding: 10px 14px; border: 1px solid var(--line); border-radius: var(--radius-sm);" value="<?= e($_POST['shipping_postcode'] ?? '') ?>">
            </div>
            <div style="grid-column: 1/-1; display: flex; flex-direction: column; gap: 6px;">
              <label style="font-size: 0.85rem; font-weight: 500;">Notes (optionnel)</label>
              <textarea name="notes" style="padding: 10px 14px; border: 1px solid var(--line); border-radius: var(--radius-sm); min-height: 80px; font-family: inherit;" placeholder="Instructions pour le livreur, point de repère..."><?= e($_POST['notes'] ?? '') ?></textarea>
            </div>
          </div>
        </div>

        <div style="background: var(--white); border: 1px solid var(--line); border-radius: var(--radius); padding: 28px;">
          <h3 style="margin-bottom: 18px;">Méthode de paiement</h3>
          <input type="hidden" name="payment_method" value="cod">
          <div style="display: flex; align-items: center; gap: 14px; padding: 18px 20px; border: 2px solid var(--olive); border-radius: var(--radius-sm); background: var(--sand);">
            <div style="width: 44px; height: 44px; border-radius: 50%; background: var(--olive); color: var(--cream); display: grid; place-items: center; flex-shrink: 0;">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="3"/><line x1="6" y1="12" x2="6.01" y2="12"/><line x1="18" y1="12" x2="18.01" y2="12"/></svg>
            </div>
            <div>
              <strong style="display: block; margin-bottom: 2px;">Paiement à la livraison</strong>
              <div style="font-size: 0.85rem; color: var(--ink-soft);">Vous payez en espèces à la réception du colis. Aucun frais supplémentaire.</div>
            </div>
          </div>
          <p style="font-size: 0.78rem; color: var(--ink-mute); margin-top: 12px;">Le paiement par carte bancaire et le virement seront disponibles prochainement.</p>
        </div>
      </div>

      <aside class="cart-summary">
        <h3>Votre commande</h3>
        <div style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 18px; padding-bottom: 18px; border-bottom: 1px solid var(--line);">
          <?php foreach ($cart as $item): ?>
            <div style="display: grid; grid-template-columns: 56px 1fr auto; gap: 12px; align-items: center;">
              <div style="width: 56px; height: 56px; border-radius: var(--radius-sm); overflow: hidden; background: var(--sand); position: relative;">
                <img src="<?= e($item['image']) ?>" style="width:100%;height:100%;object-fit:cover;">
                <span style="position: absolute; top: -6px; right: -6px; background: var(--olive); color: var(--cream); width: 22px; height: 22px; border-radius: 50%; display: grid; place-items: center; font-size: 0.72rem; font-weight: 700;"><?= (int) $item['qty'] ?></span>
              </div>
              <div style="font-size: 0.85rem; line-height: 1.3;">
                <strong><?= e($item['name']) ?></strong>
              </div>
              <strong style="font-size: 0.88rem; color: var(--olive);"><?= price($item['price'] * $item['qty']) ?></strong>
            </div>
          <?php endforeach; ?>
        </div>

        <div class="summary-row"><span>Sous-total</span><span><?= price($subtotal) ?></span></div>
        <div class="summary-row"><span>Livraison</span><span><?= $shipping > 0 ? price($shipping) : 'Gratuite' ?></span></div>
        <?php if ($discount > 0): ?>
          <div class="summary-row" style="color: var(--terracotta);"><span>Réduction</span><span>−<?= price($discount) ?></span></div>
        <?php endif; ?>
        <div class="summary-row total"><span>Total</span><span><?= price($total) ?></span></div>

        <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top: 20px;">
          Confirmer la commande
        </button>
        <div class="cart-secure">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
          Paiement sécurisé · vos données sont chiffrées
        </div>
      </aside>
    </form>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
