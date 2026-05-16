<?php
require_once __DIR__ . '/includes/helpers.php';

$order_number = $_GET['order'] ?? $_GET['n'] ?? '';
$token        = (string) ($_GET['t'] ?? '');
$order = $order_number !== ''
    ? db_one("SELECT * FROM orders WHERE order_number = ?", [$order_number])
    : null;

if (!$order || !can_view_order($order, $token)) {
    redirect('/');
}

$items = db_all("SELECT * FROM order_items WHERE order_id = ?", [$order['id']]);

$page_title = 'Commande confirmée';
$page_desc  = 'Votre commande GreenAmal a bien été enregistrée.';
$nav        = '';
$noindex    = true;

require __DIR__ . '/includes/header.php';
?>

<section style="padding: 60px 0;">
  <div class="container" style="max-width: 720px;">
    <div style="text-align: center; margin-bottom: 40px;">
      <div style="width: 84px; height: 84px; background: var(--success-bg, #E8F1E9); color: var(--success, #4A7A4F); border-radius: 50%; display: grid; place-items: center; margin: 0 auto 24px;">
        <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 13l4 4L19 7"/></svg>
      </div>
      <span class="eyebrow">Commande confirmée</span>
      <h1 style="margin: 12px 0;">Merci <?= e($order['shipping_name']) ?> !</h1>
      <p style="color: var(--ink-soft); font-size: 1.05rem;">
        Votre commande <strong style="color: var(--olive);">#<?= e($order['order_number']) ?></strong> a bien été enregistrée.
        Vous recevrez un email de confirmation à <strong><?= e($order['shipping_email']) ?></strong>.
      </p>
    </div>

    <div style="background: var(--white); border: 1px solid var(--line); border-radius: var(--radius); overflow: hidden;">
      <div style="padding: 22px 26px; border-bottom: 1px solid var(--line); display: flex; justify-content: space-between; align-items: center;">
        <div>
          <strong>Récapitulatif</strong>
          <div style="font-size: 0.82rem; color: var(--ink-mute); margin-top: 2px;">
            <?= count($items) ?> article<?= count($items) > 1 ? 's' : '' ?> ·
            <?= e(payment_method_label($order['payment_method'])[0]) ?>
          </div>
        </div>
        <?php [$lbl, $cls] = order_status_label($order['status']); ?>
        <span class="badge-status <?= e($cls) ?>" style="display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; border-radius: 999px; font-size: 0.78rem; font-weight: 600; background: var(--warning-bg, #FAF1DD); color: var(--warning, #C68A2E);"><?= e($lbl) ?></span>
      </div>

      <div style="padding: 8px 0;">
        <?php foreach ($items as $item): ?>
          <div style="display: grid; grid-template-columns: 64px 1fr auto; gap: 14px; padding: 16px 26px; align-items: center; border-bottom: 1px solid var(--line);">
            <img src="<?= e($item['product_image']) ?>" style="width: 64px; height: 64px; border-radius: var(--radius-sm); object-fit: cover;">
            <div>
              <strong><?= e($item['product_name']) ?></strong>
              <div style="font-size: 0.82rem; color: var(--ink-mute);">Quantité : <?= (int) $item['quantity'] ?> · <?= price($item['unit_price']) ?> / unité</div>
            </div>
            <strong><?= price($item['total']) ?></strong>
          </div>
        <?php endforeach; ?>
      </div>

      <div style="padding: 22px 26px; background: var(--surface-2, #FAFAF8);">
        <div style="display: flex; justify-content: space-between; padding: 4px 0; font-size: 0.92rem; color: var(--ink-soft);"><span>Sous-total</span><span><?= price($order['subtotal']) ?></span></div>
        <div style="display: flex; justify-content: space-between; padding: 4px 0; font-size: 0.92rem; color: var(--ink-soft);"><span>Livraison</span><span><?= $order['shipping'] > 0 ? price($order['shipping']) : 'Gratuite' ?></span></div>
        <?php if ($order['discount'] > 0): ?>
          <div style="display: flex; justify-content: space-between; padding: 4px 0; font-size: 0.92rem; color: var(--terracotta);"><span>Réduction (<?= e($order['coupon_code']) ?>)</span><span>−<?= price($order['discount']) ?></span></div>
        <?php endif; ?>
        <div style="display: flex; justify-content: space-between; padding: 12px 0 0; margin-top: 8px; border-top: 1px solid var(--line); font-size: 1.05rem; font-weight: 600; color: var(--ink);"><span>Total payé</span><span style="font-family: var(--font-display); font-size: 1.5rem; color: var(--olive);"><?= price($order['total']) ?></span></div>
      </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-top: 24px;">
      <div style="background: var(--white); border: 1px solid var(--line); border-radius: var(--radius); padding: 22px;">
        <strong style="display: block; margin-bottom: 10px;">Livraison à</strong>
        <div style="font-size: 0.9rem; color: var(--ink-soft); line-height: 1.6;">
          <?= e($order['shipping_name']) ?><br>
          <?= e($order['shipping_address']) ?><br>
          <?= e($order['shipping_postcode']) ?> <?= e($order['shipping_city']) ?><br>
          <?= e($order['shipping_phone']) ?>
        </div>
      </div>
      <div style="background: var(--white); border: 1px solid var(--line); border-radius: var(--radius); padding: 22px;">
        <strong style="display: block; margin-bottom: 10px;">Et maintenant ?</strong>
        <div style="font-size: 0.9rem; color: var(--ink-soft); line-height: 1.6;">
          <svg class="icon-inline" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="9 12 12 15 16 10"/></svg>Email de confirmation envoyé<br>
          <svg class="icon-inline" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>Préparation sous 24h<br>
          <svg class="icon-inline" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>Livraison sous 24-48h<br>
          <svg class="icon-inline" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/></svg>Questions ? +212 627-634472
        </div>
      </div>
    </div>

    <div style="text-align: center; margin-top: 40px;">
      <a href="shop" class="btn btn-primary btn-lg">Continuer mes achats</a>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
