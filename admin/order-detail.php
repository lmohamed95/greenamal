<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/mail.php';
admin_require_login();

$id = (int) ($_GET['id'] ?? 0);
$order = db_one("
    SELECT o.*, c.first_name, c.last_name, c.email, c.phone, c.total_orders, c.lifetime_value
    FROM orders o
    LEFT JOIN customers c ON c.id = o.customer_id
    WHERE o.id = ?
", [$id]);

if (!$order) {
    redirect('orders.php');
}

// Status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_status'])) {
    csrf_verify();
    $new_status = $_POST['new_status'];
    $allowed = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
    if (in_array($new_status, $allowed, true) && $new_status !== $order['status']) {
        db_query('UPDATE orders SET status = ? WHERE id = ?', [$new_status, $id]);
        db_insert('order_events', [
            'order_id'    => $id,
            'event_type'  => 'status_change',
            'description' => "Statut changé en : " . order_status_label($new_status)[0],
            'created_by'  => 'admin',
        ]);
        // Notify the customer of the new status
        $fresh = db_one('SELECT * FROM orders WHERE id = ?', [$id]);
        if ($fresh) @mail_order_status($fresh, $new_status);
    }
    redirect('order-detail.php?id=' . $id);
}

$items = db_all("SELECT * FROM order_items WHERE order_id = ?", [$id]);
$events = db_all("SELECT * FROM order_events WHERE order_id = ? ORDER BY created_at DESC", [$id]);

$page_title = 'Commande #' . $order['order_number'];
$current = 'orders';
$customer_name = trim(($order['first_name'] ?? '') . ' ' . ($order['last_name'] ?? '')) ?: $order['shipping_name'];

[$status_lbl, $status_cls] = order_status_label($order['status']);
[$pay_lbl, $pay_cls] = payment_method_label($order['payment_method']);

require __DIR__ . '/_includes/header.php';
?>

<div class="page">
  <div class="breadcrumb-admin"><a href="index.php">Tableau de bord</a><span>/</span><a href="orders.php">Commandes</a><span>/</span><span>#<?= e($order['order_number']) ?></span></div>

  <div class="page-head">
    <div>
      <h1 style="display: flex; align-items: center; gap: 14px; flex-wrap: wrap;">
        Commande #<?= e($order['order_number']) ?>
        <span class="badge-status <?= e($status_cls) ?>"><?= e($status_lbl) ?></span>
      </h1>
      <p>Passée <?= time_ago($order['created_at']) ?> · Paiement <?= e($pay_lbl) ?> · <?= count($items) ?> articles · <?= price($order['total']) ?></p>
    </div>
    <form method="post" class="page-actions" style="gap: 8px;">
      <?= csrf_field() ?>
      <select name="new_status" class="field-select">
        <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>En attente</option>
        <option value="processing" <?= $order['status'] === 'processing' ? 'selected' : '' ?>>Préparation</option>
        <option value="shipped" <?= $order['status'] === 'shipped' ? 'selected' : '' ?>>Expédié</option>
        <option value="delivered" <?= $order['status'] === 'delivered' ? 'selected' : '' ?>>Livré</option>
        <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Annulé</option>
      </select>
      <button type="submit" class="btn btn-primary">Mettre à jour</button>
    </form>
  </div>

  <div class="detail-grid">
    <div style="display: flex; flex-direction: column; gap: 16px;">
      <div class="card">
        <div class="card-head"><h3>Articles commandés</h3><span class="head-meta"><?= count($items) ?> articles</span></div>
        <table class="data-table">
          <thead><tr><th>Produit</th><th>SKU</th><th>Qté</th><th>Prix</th><th>Total</th></tr></thead>
          <tbody>
            <?php foreach ($items as $it): ?>
              <tr>
                <td><div class="cell-product"><img src="<?= e($it['product_image']) ?>"><span><strong><?= e($it['product_name']) ?></strong></span></div></td>
                <td class="cell-mono"><?= e($it['product_sku']) ?></td>
                <td class="cell-num"><?= (int) $it['quantity'] ?></td>
                <td class="cell-num"><?= price($it['unit_price']) ?></td>
                <td class="cell-num"><strong><?= price($it['total']) ?></strong></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <div class="card-foot" style="display: block;">
          <div style="margin-left: auto; max-width: 280px;">
            <div class="summary-line"><span>Sous-total</span><strong><?= price($order['subtotal']) ?></strong></div>
            <div class="summary-line"><span>Livraison</span><strong><?= price($order['shipping']) ?></strong></div>
            <?php if ($order['discount'] > 0): ?>
              <div class="summary-line" style="color: var(--terracotta);"><span>Réduction (<?= e($order['coupon_code']) ?>)</span><strong>−<?= price($order['discount']) ?></strong></div>
            <?php endif; ?>
            <div class="summary-line total"><span>Total</span><span><?= price($order['total']) ?></span></div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-head"><h3>Historique</h3></div>
        <div class="card-body">
          <ul class="timeline">
            <?php foreach ($events as $i => $ev): ?>
              <li>
                <div class="timeline-dot<?= $i === 0 ? '' : ' done' ?>"></div>
                <div class="timeline-content">
                  <strong><?= e($ev['description']) ?></strong>
                  <span><?= e(date('j M Y, H:i', strtotime($ev['created_at']))) ?> · par <?= e($ev['created_by']) ?></span>
                </div>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>
    </div>

    <div style="display: flex; flex-direction: column; gap: 16px;">
      <div class="card">
        <div class="card-head"><h3>Client</h3></div>
        <div class="card-body">
          <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 14px;">
            <span class="cell-customer-avatar" style="width: 44px; height: 44px; font-size: 1rem;"><?= e(initials($customer_name)) ?></span>
            <div>
              <strong style="display: block;"><?= e($customer_name) ?></strong>
              <a href="customers.php" style="color: var(--olive); font-size: 0.82rem;">Voir le profil</a>
            </div>
          </div>
          <div style="font-size: 0.85rem; color: var(--ink-soft); display: flex; flex-direction: column; gap: 6px;">
            <div>📧 <?= e($order['shipping_email']) ?></div>
            <div>📱 <?= e($order['shipping_phone']) ?></div>
            <?php if ($order['customer_id']): ?>
              <div style="margin-top: 6px; padding-top: 10px; border-top: 1px solid var(--line-soft);">
                <strong style="color: var(--ink);"><?= (int) $order['total_orders'] ?></strong> commandes · LTV <strong style="color: var(--ink);"><?= price($order['lifetime_value']) ?></strong>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-head"><h3>Adresse de livraison</h3></div>
        <div class="card-body" style="font-size: 0.88rem; color: var(--ink-soft);">
          <strong style="color: var(--ink); display: block; margin-bottom: 4px;"><?= e($order['shipping_name']) ?></strong>
          <?= nl2br(e($order['shipping_address'])) ?><br>
          <?= e($order['shipping_postcode']) ?> <?= e($order['shipping_city']) ?><br>
          <span style="color: var(--ink-mute);"><?= e($order['shipping_phone']) ?></span>
        </div>
      </div>

      <div class="card">
        <div class="card-head"><h3>Paiement</h3></div>
        <div class="card-body" style="font-size: 0.88rem;">
          <div style="display: flex; justify-content: space-between; padding: 4px 0;"><span style="color: var(--ink-soft);">Méthode</span><span class="badge-status <?= e($pay_cls) ?>"><?= e($pay_lbl) ?></span></div>
          <div style="display: flex; justify-content: space-between; padding: 4px 0;"><span style="color: var(--ink-soft);">Statut</span><span class="badge-status <?= $order['payment_status'] === 'paid' ? 'status-success' : ($order['payment_status'] === 'pending' ? 'status-warning' : 'status-danger') ?>"><?= e(ucfirst($order['payment_status'])) ?></span></div>
        </div>
      </div>

      <?php if ($order['notes']): ?>
        <div class="card">
          <div class="card-head"><h3>Notes du client</h3></div>
          <div class="card-body" style="font-size: 0.88rem; color: var(--ink-soft);"><?= nl2br(e($order['notes'])) ?></div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require __DIR__ . '/_includes/footer.php'; ?>
