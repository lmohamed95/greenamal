<?php
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/image.php';

$page_title = 'Mon panier';
$page_desc  = 'Votre panier GreenAmal - finalisez votre commande de produits naturels du Maroc.';
$nav        = '';
$noindex    = true;

$cart = cart_get();
$subtotal = cart_subtotal();
$shipping = $subtotal >= FREE_SHIPPING_THRESHOLD || $subtotal === 0.0 ? 0 : SHIPPING_FEE;

// Apply coupon if in session
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

// Handle coupon submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['coupon_code'])) {
    csrf_verify();
    if (!rate_limit('coupon_apply', 8, 60)) {
        $_SESSION['coupon_error'] = 'Trop de tentatives. Réessayez dans une minute.';
        redirect('cart');
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
    redirect('cart');
}

require __DIR__ . '/includes/header.php';
?>

<div class="container breadcrumb">
  <a href="/">Accueil</a><span>/</span><span>Mon panier</span>
</div>

<section style="padding-top: 20px;">
  <div class="container">
    <div style="margin-bottom: 32px;">
      <h1 style="font-size: 2.2rem;">Mon panier</h1>
      <p style="color: var(--ink-soft); margin-top: 8px;"><?= cart_count() ?> article<?= cart_count() > 1 ? 's' : '' ?></p>
    </div>

    <?php if (empty($cart)): ?>
      <div style="text-align: center; padding: 80px 20px; background: var(--white); border-radius: var(--radius); border: 1px solid var(--line);">
        <h2 style="font-size: 1.6rem;">Votre panier est vide</h2>
        <p style="color: var(--ink-soft); margin: 12px 0 24px;">Découvrez nos produits naturels du Maroc.</p>
        <a href="shop" class="btn btn-primary btn-lg">Voir la boutique</a>
      </div>
    <?php else: ?>

      <?php if ($subtotal < FREE_SHIPPING_THRESHOLD): ?>
        <div class="shipping-progress">
          <p>Plus que <strong><?= price(FREE_SHIPPING_THRESHOLD - $subtotal) ?></strong> pour la livraison gratuite</p>
          <div class="progress-track">
            <div class="progress-fill" style="width: <?= min(100, ($subtotal / FREE_SHIPPING_THRESHOLD) * 100) ?>%;"></div>
          </div>
        </div>
      <?php else: ?>
        <div class="shipping-progress">
          <p><svg class="icon-inline" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="9 12 12 15 16 10"/></svg><strong>Livraison gratuite débloquée !</strong></p>
          <div class="progress-track"><div class="progress-fill" style="width: 100%;"></div></div>
        </div>
      <?php endif; ?>

      <div class="cart-layout">
        <div class="cart-items">
          <?php foreach ($cart as $item): ?>
            <div class="cart-item" data-product-id="<?= (int) $item['id'] ?>">
              <div class="cart-item-image"><?= picture_tag($item['image'], $item['name'], ['lazy' => true, 'width' => 100, 'height' => 100]) ?></div>
              <div class="cart-item-info">
                <h4><?= e($item['name']) ?></h4>
                <div class="meta"><?= price($item['price']) ?> / unité</div>
                <div class="qty-mini">
                  <button>−</button>
                  <input type="text" value="<?= (int) $item['qty'] ?>">
                  <button>+</button>
                </div>
              </div>
              <div class="cart-item-actions">
                <span class="price" style="font-family: var(--font-display); font-size: 1.15rem; color: var(--olive); font-weight: 600;"><?= price($item['price'] * $item['qty']) ?></span>
                <button class="cart-remove" data-cart-remove="<?= (int) $item['id'] ?>">Supprimer</button>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <aside class="cart-summary">
          <h3>Récapitulatif</h3>
          <div class="summary-row"><span>Sous-total (<?= cart_count() ?> articles)</span><span><?= price($subtotal) ?></span></div>
          <div class="summary-row"><span>Livraison</span><span><?= $shipping > 0 ? price($shipping) : 'Gratuite' ?></span></div>
          <?php if ($discount > 0): ?>
            <div class="summary-row" style="color: var(--terracotta);">
              <span>Réduction (<?= e($coupon['code']) ?>)</span>
              <span>−<?= price($discount) ?></span>
            </div>
          <?php endif; ?>

          <form method="post" class="cart-coupon">
            <?= csrf_field() ?>
            <input type="text" name="coupon_code" placeholder="Code promo (ex: first25)" value="<?= e($coupon['code'] ?? '') ?>">
            <button type="submit">Appliquer</button>
          </form>
          <?php if (!empty($_SESSION['coupon_error'])): ?>
            <p style="color: var(--terracotta); font-size: 0.82rem; margin-top: -10px; margin-bottom: 14px;">
              <?= e($_SESSION['coupon_error']) ?>
            </p>
            <?php unset($_SESSION['coupon_error']); ?>
          <?php endif; ?>

          <div class="summary-row total"><span>Total</span><span><?= price($total) ?></span></div>

          <a href="checkout" class="btn btn-primary btn-block btn-lg" style="margin-top: 20px;">
            Passer la commande
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
          </a>

          <div class="cart-secure">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
            Paiement 100% sécurisé · Données chiffrées
          </div>

          <div style="margin-top: 16px; padding-top: 16px; border-top: 1px solid var(--line); text-align: center;">
            <span style="background: var(--olive); color: var(--cream); padding: 8px 16px; border-radius: 999px; font-size: 0.78rem; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="3"/></svg>
              Paiement à la livraison
            </span>
          </div>
        </aside>
      </div>

    <?php endif; ?>
  </div>
</section>

<div style="height: 60px;"></div>

<?php require __DIR__ . '/includes/footer.php'; ?>
