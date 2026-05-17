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
$body_class = 'gd-2026';
$extra_css  = ['/assets/css/home.css'];

require __DIR__ . '/includes/header.php';
?>

<section class="confirmation-page">
  <div class="container" style="max-width: 760px;">
    <div class="confirmation-hero">
      <div class="confirmation-check">
        <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 13l4 4L19 7"/></svg>
      </div>
      <span class="h-eyebrow">Commande confirmée</span>
      <?php $first = customer_first_name($order['shipping_name'] ?? ''); ?>
      <h1 class="h-serif">Merci<?= $first ? ' <em>' . e($first) . '</em>' : '' ?>&nbsp;!</h1>
      <p>
        Votre commande <strong>#<?= e($order['order_number']) ?></strong> a bien été enregistrée.
        Vous recevrez un email à <strong><?= e($order['shipping_email']) ?></strong>.
      </p>
    </div>

    <div class="confirmation-card">
      <div class="confirmation-head">
        <div>
          <strong>Récapitulatif</strong>
          <div class="muted" style="font-size:12.5px;">
            <?= count($items) ?> article<?= count($items) > 1 ? 's' : '' ?> ·
            <?= e(payment_method_label($order['payment_method'])[0]) ?>
          </div>
        </div>
        <?php [$lbl, $cls] = order_status_label($order['status']); ?>
        <span class="status-pill status-<?= e($cls) ?>"><?= e($lbl) ?></span>
      </div>

      <div class="confirmation-items">
        <?php foreach ($items as $item): ?>
          <div class="confirmation-line">
            <img src="<?= e($item['product_image']) ?>" alt="<?= e($item['product_name']) ?>" loading="lazy">
            <div>
              <strong><?= e($item['product_name']) ?></strong>
              <div class="muted" style="font-size:12.5px;">Quantité : <?= (int) $item['quantity'] ?> · <?= price($item['unit_price']) ?> / unité</div>
            </div>
            <strong class="confirmation-line-total"><?= price($item['total']) ?></strong>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="confirmation-totals">
        <div class="summary-row"><span>Sous-total</span><span><?= price($order['subtotal']) ?></span></div>
        <div class="summary-row"><span>Livraison</span><span><?= $order['shipping'] > 0 ? price($order['shipping']) : '<b style="color:var(--forest-700);">Gratuite</b>' ?></span></div>
        <?php if ($order['discount'] > 0): ?>
          <div class="summary-row is-discount"><span>Réduction (<?= e($order['coupon_code']) ?>)</span><span>−<?= price($order['discount']) ?></span></div>
        <?php endif; ?>
        <div class="summary-row total">
          <span>Total payé</span>
          <span class="total-amount"><?= price($order['total']) ?></span>
        </div>
      </div>
    </div>

    <div class="confirmation-info-grid">
      <div class="confirmation-info-card">
        <strong>Livraison à</strong>
        <p>
          <?= e($order['shipping_name']) ?><br>
          <?= e($order['shipping_address']) ?><br>
          <?= e($order['shipping_postcode']) ?> <?= e($order['shipping_city']) ?><br>
          <?= e($order['shipping_phone']) ?>
        </p>
      </div>
      <div class="confirmation-info-card">
        <strong>Et maintenant ?</strong>
        <ul class="confirmation-next">
          <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="9 12 12 15 16 10"/></svg>Email de confirmation envoyé</li>
          <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>Préparation sous 24h</li>
          <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="5" width="15" height="13" rx="2"/><path d="M16 9h4l3 4v5h-7"/><circle cx="6" cy="20" r="2"/><circle cx="19" cy="20" r="2"/></svg>Livraison 24-48h</li>
          <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.9v3a2 2 0 01-2.2 2 19.8 19.8 0 01-8.6-3.1 19.5 19.5 0 01-6-6A19.8 19.8 0 012.1 4.2 2 2 0 014.1 2h3a2 2 0 012 1.7l.5 2.5a2 2 0 01-.6 1.9l-1.3 1.3a16 16 0 006 6l1.3-1.3a2 2 0 011.9-.6l2.5.5a2 2 0 011.7 2Z"/></svg>Questions ? <?= e(CONTACT_PHONE) ?></li>
        </ul>
      </div>
    </div>

    <div style="text-align: center; margin-top: 32px;">
      <a href="/boutique" class="h-btn h-btn-primary h-btn-lg">
        Continuer mes achats
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
      </a>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
