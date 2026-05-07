<?php
require_once __DIR__ . '/../includes/auth.php';
admin_require_login();

// =====================================================================
// POST handler · create / update / delete
// =====================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $op = $_POST['op'] ?? '';

    if ($op === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id) {
            db_query('DELETE FROM coupons WHERE id = ?', [$id]); // cascades to coupon_products / coupon_categories
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Coupon supprimé.'];
        }
        redirect('coupons.php');
    }

    if ($op === 'save') {
        $id          = (int) ($_POST['id'] ?? 0);
        $code        = strtoupper(trim((string) ($_POST['code'] ?? '')));
        $description = trim((string) ($_POST['description'] ?? ''));
        $type        = in_array($_POST['type'] ?? '', ['percent','fixed','free_shipping'], true) ? $_POST['type'] : 'percent';
        $value       = (float) ($_POST['value'] ?? 0);
        $applies_to  = in_array($_POST['applies_to'] ?? '', ['all','products','categories'], true) ? $_POST['applies_to'] : 'all';
        $min_order   = (float) ($_POST['min_order'] ?? 0);
        $max_uses    = $_POST['max_uses'] !== '' ? (int) $_POST['max_uses'] : null;
        $per_cust    = $_POST['max_uses_per_customer'] !== '' ? (int) $_POST['max_uses_per_customer'] : null;
        $starts_at   = !empty($_POST['starts_at']) ? $_POST['starts_at'] : null;
        $expires_at  = !empty($_POST['expires_at']) ? $_POST['expires_at'] : null;
        $status      = in_array($_POST['status'] ?? '', ['active','scheduled','expired','disabled'], true) ? $_POST['status'] : 'active';
        $product_ids = array_map('intval', (array) ($_POST['product_ids'] ?? []));
        $cat_ids     = array_map('intval', (array) ($_POST['category_ids'] ?? []));

        $errors = [];
        if ($code === '') $errors[] = 'Le code est requis.';
        if ($type !== 'free_shipping' && $value <= 0) $errors[] = 'La valeur doit être supérieure à 0.';
        if ($type === 'percent' && $value > 100) $errors[] = 'Le pourcentage ne peut pas dépasser 100.';

        if (!$errors) {
            $data = [
                'code' => $code,
                'description' => $description,
                'type' => $type,
                'value' => $value,
                'applies_to' => $applies_to,
                'min_order' => $min_order,
                'max_uses' => $max_uses,
                'max_uses_per_customer' => $per_cust,
                'starts_at' => $starts_at ? date('Y-m-d H:i:s', strtotime($starts_at)) : null,
                'expires_at' => $expires_at ? date('Y-m-d H:i:s', strtotime($expires_at)) : null,
                'status' => $status,
            ];

            try {
                if ($id > 0) {
                    $set = implode(', ', array_map(fn($k) => "$k = :$k", array_keys($data)));
                    $data['id'] = $id;
                    db_query("UPDATE coupons SET $set WHERE id = :id", $data);
                    db_query('DELETE FROM coupon_products WHERE coupon_id = ?', [$id]);
                    db_query('DELETE FROM coupon_categories WHERE coupon_id = ?', [$id]);
                } else {
                    $id = db_insert('coupons', $data);
                }

                if ($applies_to === 'products' && $product_ids) {
                    foreach ($product_ids as $pid) {
                        db_query('INSERT IGNORE INTO coupon_products (coupon_id, product_id) VALUES (?, ?)', [$id, $pid]);
                    }
                } elseif ($applies_to === 'categories' && $cat_ids) {
                    foreach ($cat_ids as $cid) {
                        db_query('INSERT IGNORE INTO coupon_categories (coupon_id, category_id) VALUES (?, ?)', [$id, $cid]);
                    }
                }

                $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Coupon enregistré.'];
                redirect('coupons.php');
            } catch (PDOException $e) {
                $errors[] = (str_contains($e->getMessage(), 'Duplicate'))
                    ? 'Ce code existe déjà.'
                    : 'Erreur base de données : ' . $e->getMessage();
            }
        }

        $_SESSION['flash'] = ['type' => 'error', 'msg' => implode(' ', $errors)];
        $_SESSION['form_repop'] = $_POST;
        redirect('coupons.php?edit=' . $id);
    }
}

$page_title = 'Coupons';
$current = 'coupons';

$coupons = db_all("
    SELECT c.*,
      (SELECT COUNT(*) FROM coupon_products WHERE coupon_id = c.id) AS product_count,
      (SELECT COUNT(*) FROM coupon_categories WHERE coupon_id = c.id) AS category_count
    FROM coupons c
    ORDER BY c.created_at DESC
");

$counts = db_one("SELECT
    COUNT(*) AS total,
    SUM(status='active') AS active,
    SUM(status='scheduled') AS scheduled,
    SUM(status='expired') AS expired,
    COALESCE(SUM(uses_count),0) AS uses
  FROM coupons
");

$categories = db_all("SELECT id, name FROM categories ORDER BY display_order");
$products   = db_all("SELECT id, name, sku FROM products ORDER BY name");

// Editing payload (if ?edit=N)
$edit_id     = (int) ($_GET['edit'] ?? 0);
$edit        = null;
$edit_pids   = [];
$edit_cids   = [];
if ($edit_id > 0) {
    $edit = db_one('SELECT * FROM coupons WHERE id = ?', [$edit_id]);
    if ($edit) {
        $edit_pids = array_column(db_all('SELECT product_id FROM coupon_products WHERE coupon_id = ?', [$edit_id]), 'product_id');
        $edit_cids = array_column(db_all('SELECT category_id FROM coupon_categories WHERE coupon_id = ?', [$edit_id]), 'category_id');
    }
}
$repop = $_SESSION['form_repop'] ?? null;
unset($_SESSION['form_repop']);

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$open_panel = isset($_GET['new']) || $edit_id > 0 || $repop;

require __DIR__ . '/_includes/header.php';

function _val(?array $repop, ?array $edit, string $key, $default = '') {
    if ($repop !== null && isset($repop[$key])) return $repop[$key];
    if ($edit !== null && isset($edit[$key]))   return $edit[$key];
    return $default;
}
$f = fn(string $k, $d='') => _val($repop, $edit, $k, $d);
$selected_pids = $repop['product_ids']  ?? $edit_pids;
$selected_cids = $repop['category_ids'] ?? $edit_cids;
?>

<div class="page">
  <div class="breadcrumb-admin"><a href="index.php">Tableau de bord</a><span>/</span><span>Coupons</span></div>
  <div class="page-head">
    <div>
      <h1>Coupons & promotions</h1>
      <p>Codes promo, réductions, livraisons offertes</p>
    </div>
    <div class="page-actions">
      <a href="?new=1" class="btn btn-primary" id="openNew">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Nouveau coupon
      </a>
    </div>
  </div>

  <?php if ($flash): ?>
    <div class="flash flash-<?= e($flash['type']) ?>"><?= e($flash['msg']) ?></div>
  <?php endif; ?>

  <div class="kpi-grid">
    <div class="kpi"><div class="kpi-label">Coupons actifs</div><div class="kpi-value"><?= (int) $counts['active'] ?></div></div>
    <div class="kpi"><div class="kpi-label">Programmés</div><div class="kpi-value"><?= (int) $counts['scheduled'] ?></div></div>
    <div class="kpi"><div class="kpi-label">Utilisations totales</div><div class="kpi-value"><?= (int) $counts['uses'] ?></div></div>
    <div class="kpi"><div class="kpi-label">Expirés</div><div class="kpi-value"><?= (int) $counts['expired'] ?></div></div>
  </div>

  <div class="card">
    <table class="data-table">
      <thead><tr>
        <th>Code</th><th>Description</th><th>Type</th><th>Valeur</th><th>Cible</th><th>Utilisations</th><th>Expire</th><th>Statut</th><th class="actions-col"></th>
      </tr></thead>
      <tbody>
        <?php foreach ($coupons as $c):
          $type_lbl = match($c['type']) {
              'percent' => 'Pourcentage',
              'fixed' => 'Montant fixe',
              'free_shipping' => 'Livraison',
              default => $c['type'],
          };
          $value_lbl = $c['type'] === 'percent' ? '−' . (int) $c['value'] . '%' : ($c['type'] === 'fixed' ? '−' . price($c['value']) : 'Gratuite');
          $status_cls = match($c['status']) {
              'active' => 'status-success',
              'scheduled' => 'status-warning',
              'expired' => 'status-danger',
              default => 'status-neutral',
          };
          $status_lbl = match($c['status']) {
              'active' => 'Actif',
              'scheduled' => 'Programmé',
              'expired' => 'Expiré',
              default => 'Désactivé',
          };
          $target_lbl = match($c['applies_to']) {
              'products'   => $c['product_count'] . ' produit(s)',
              'categories' => $c['category_count'] . ' catégorie(s)',
              default      => 'Toute la boutique',
          };
        ?>
          <tr>
            <td><span class="cell-mono" style="background: var(--sand); padding: 4px 10px; border-radius: 4px; color: var(--olive-dark); font-weight: 600;"><?= e($c['code']) ?></span></td>
            <td><strong style="font-size: 0.88rem;"><?= e($c['description']) ?></strong></td>
            <td><span class="badge-status status-info"><?= e($type_lbl) ?></span></td>
            <td class="cell-num"><strong><?= e($value_lbl) ?></strong></td>
            <td class="cell-mute"><?= e($target_lbl) ?></td>
            <td><strong><?= (int) $c['uses_count'] ?></strong> / <span class="cell-mute"><?= $c['max_uses'] ? (int) $c['max_uses'] : '∞' ?></span></td>
            <td class="cell-mute"><?= $c['expires_at'] ? date('j M Y', strtotime($c['expires_at'])) : 'Pas d\'expiration' ?></td>
            <td><span class="badge-status <?= e($status_cls) ?>"><?= e($status_lbl) ?></span></td>
            <td>
              <div class="row-actions">
                <a href="?edit=<?= (int) $c['id'] ?>" class="topbar-btn" title="Modifier">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                </a>
                <form method="post" style="display:inline" onsubmit="return confirm('Supprimer le coupon <?= e($c['code']) ?> ?');">
                  <?= csrf_field() ?>
                  <input type="hidden" name="op" value="delete">
                  <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
                  <button type="submit" class="topbar-btn" title="Supprimer">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-2 14a2 2 0 0 1-2 2H9a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
                  </button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$coupons): ?>
          <tr><td colspan="9" style="text-align:center; padding: 32px; color: var(--ink-mute);">Aucun coupon. Cliquez sur « Nouveau coupon » pour commencer.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- =====================================================================
     Side panel · create / edit coupon
     ===================================================================== -->
<div class="side-overlay<?= $open_panel ? ' is-open' : '' ?>" id="sideOverlay"></div>
<aside class="side-panel<?= $open_panel ? ' is-open' : '' ?>" id="couponPanel" aria-labelledby="panelTitle">
  <div class="side-head">
    <div>
      <div class="side-eyebrow"><?= $edit ? 'Édition' : 'Nouveau' ?></div>
      <h2 id="panelTitle"><?= $edit ? e($edit['code']) : 'Nouveau coupon' ?></h2>
    </div>
    <button type="button" class="side-close" id="panelClose" aria-label="Fermer">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>
  </div>

  <form method="post" class="side-body coupon-form" id="couponForm" autocomplete="off">
    <?= csrf_field() ?>
    <input type="hidden" name="op" value="save">
    <input type="hidden" name="id" value="<?= (int) ($edit['id'] ?? 0) ?>">
    <input type="hidden" name="type"       id="typeInput"       value="<?= e($f('type', 'percent')) ?>">
    <input type="hidden" name="applies_to" id="appliesToInput"  value="<?= e($f('applies_to', 'all')) ?>">

    <!-- Live preview -->
    <div class="cp-preview" id="couponPreview" aria-hidden="true">
      <div class="cp-stamp" id="cpStamp">%</div>
      <div class="cp-meta">
        <div class="cp-code" id="cpCode">CODE</div>
        <div class="cp-line"><span id="cpAmount">−15%</span> · <span id="cpScope">Toute la boutique</span></div>
      </div>
    </div>

    <!-- ============ Identité ============ -->
    <section class="form-section">
      <h3 class="section-label">Identité</h3>
      <div class="field">
        <label for="couponCode">Code <span class="req">*</span></label>
        <div class="input-with-action">
          <input id="couponCode" type="text" name="code" value="<?= e($f('code')) ?>" required
                 pattern="[A-Za-z0-9_\-]+" placeholder="ETE2026" style="text-transform:uppercase">
          <button type="button" class="btn-inline" id="genCode" title="Générer un code aléatoire">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
            Générer
          </button>
        </div>
        <small class="hint">Lettres, chiffres et tirets uniquement.</small>
      </div>
      <div class="field">
        <label>Description</label>
        <input type="text" name="description" value="<?= e($f('description')) ?>" placeholder="Promo d'été −15%">
      </div>
    </section>

    <!-- ============ Remise ============ -->
    <section class="form-section">
      <h3 class="section-label">Type de remise</h3>
      <div class="seg-group" role="radiogroup" aria-label="Type" id="typeSeg">
        <button type="button" class="seg" data-value="percent" role="radio">
          <span class="seg-icon">%</span><span class="seg-label">Pourcentage</span>
        </button>
        <button type="button" class="seg" data-value="fixed" role="radio">
          <span class="seg-icon">DH</span><span class="seg-label">Montant fixe</span>
        </button>
        <button type="button" class="seg" data-value="free_shipping" role="radio">
          <span class="seg-icon">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
          </span>
          <span class="seg-label">Livraison</span>
        </button>
      </div>
      <div class="field" id="valueField">
        <label for="valueInput">Valeur <span class="req">*</span></label>
        <div class="input-suffix">
          <input id="valueInput" type="number" name="value" value="<?= e($f('value', '15')) ?>" step="0.01" min="0">
          <span class="suffix" id="valueSuffix">%</span>
        </div>
      </div>
    </section>

    <!-- ============ Cible ============ -->
    <section class="form-section">
      <h3 class="section-label">Cible</h3>
      <div class="seg-group" role="radiogroup" aria-label="S'applique à" id="appliesToSeg">
        <button type="button" class="seg" data-value="all" role="radio">Toute la boutique</button>
        <button type="button" class="seg" data-value="products" role="radio">Produits</button>
        <button type="button" class="seg" data-value="categories" role="radio">Catégories</button>
      </div>

      <!-- Product chip-picker -->
      <div class="chip-picker" id="productPicker" hidden>
        <div class="chip-bar" id="productChips" data-empty="Aucun produit sélectionné"></div>
        <div class="chip-search-wrap">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input type="search" class="chip-search" id="productSearch" placeholder="Rechercher un produit…">
        </div>
        <ul class="chip-list" id="productList">
          <?php foreach ($products as $p): ?>
            <li>
              <label data-label="<?= e($p['name']) ?>">
                <input type="checkbox" name="product_ids[]" value="<?= (int) $p['id'] ?>"
                  <?= in_array((int) $p['id'], array_map('intval', $selected_pids), true) ? 'checked' : '' ?>>
                <span class="item-main"><?= e($p['name']) ?></span>
                <span class="item-sub"><?= e($p['sku']) ?></span>
              </label>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>

      <!-- Category chip-picker -->
      <div class="chip-picker" id="categoryPicker" hidden>
        <div class="chip-bar" id="categoryChips" data-empty="Aucune catégorie sélectionnée"></div>
        <div class="chip-search-wrap">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input type="search" class="chip-search" id="categorySearch" placeholder="Rechercher une catégorie…">
        </div>
        <ul class="chip-list" id="categoryList">
          <?php foreach ($categories as $c): ?>
            <li>
              <label data-label="<?= e($c['name']) ?>">
                <input type="checkbox" name="category_ids[]" value="<?= (int) $c['id'] ?>"
                  <?= in_array((int) $c['id'], array_map('intval', $selected_cids), true) ? 'checked' : '' ?>>
                <span class="item-main"><?= e($c['name']) ?></span>
              </label>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </section>

    <!-- ============ Conditions ============ -->
    <section class="form-section">
      <h3 class="section-label">Conditions</h3>
      <div class="field-grid">
        <div class="field">
          <label>Commande minimum</label>
          <div class="input-suffix">
            <input type="number" name="min_order" value="<?= e($f('min_order', '0')) ?>" step="0.01" min="0">
            <span class="suffix">DH</span>
          </div>
        </div>
        <div class="field">
          <label>Limite totale</label>
          <input type="number" name="max_uses" value="<?= e($f('max_uses', '')) ?>" min="0" placeholder="Illimitée">
        </div>
      </div>
      <div class="field">
        <label>Limite par client</label>
        <input type="number" name="max_uses_per_customer" value="<?= e($f('max_uses_per_customer', '')) ?>" min="0" placeholder="Illimitée">
        <small class="hint">Combien de fois un même client peut utiliser ce code.</small>
      </div>
    </section>

    <!-- ============ Programmation ============ -->
    <section class="form-section">
      <h3 class="section-label">Programmation</h3>
      <div class="field-grid">
        <div class="field">
          <label>Début</label>
          <?php $sd = $f('starts_at'); $sd_v = $sd ? date('Y-m-d\TH:i', strtotime($sd)) : ''; ?>
          <input type="datetime-local" name="starts_at" value="<?= e($sd_v) ?>">
        </div>
        <div class="field">
          <label>Expiration</label>
          <?php $ed = $f('expires_at'); $ed_v = $ed ? date('Y-m-d\TH:i', strtotime($ed)) : ''; ?>
          <input type="datetime-local" name="expires_at" value="<?= e($ed_v) ?>">
        </div>
      </div>
      <div class="field">
        <label>Statut</label>
        <select name="status">
          <?php $st = $f('status', 'active'); ?>
          <option value="active"    <?= $st === 'active' ? 'selected' : '' ?>>Actif</option>
          <option value="scheduled" <?= $st === 'scheduled' ? 'selected' : '' ?>>Programmé</option>
          <option value="disabled"  <?= $st === 'disabled' ? 'selected' : '' ?>>Désactivé</option>
          <?php if ($st === 'expired'): ?>
            <option value="expired" selected>Expiré</option>
          <?php endif; ?>
        </select>
      </div>
    </section>
  </form>

  <!-- Sticky footer -->
  <div class="side-foot">
    <a href="coupons.php" class="btn btn-ghost">Annuler</a>
    <button type="submit" form="couponForm" class="btn btn-primary">
      <?= $edit ? 'Enregistrer' : 'Créer le coupon' ?>
    </button>
  </div>
</aside>

<script>
(function () {
  const overlay = document.getElementById('sideOverlay');
  const panel   = document.getElementById('couponPanel');
  if (!panel) return;

  // ---- Open / close --------------------------------------------------
  function close() {
    overlay.classList.remove('is-open');
    panel.classList.remove('is-open');
    history.replaceState(null, '', 'coupons.php');
  }
  document.getElementById('panelClose').addEventListener('click', close);
  overlay.addEventListener('click', close);
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape' && panel.classList.contains('is-open')) close();
  });

  // ---- Segmented control ---------------------------------------------
  function bindSegmented(segId, hiddenId, onChange) {
    const seg = document.getElementById(segId);
    const hidden = document.getElementById(hiddenId);
    function setValue(v, fire = true) {
      hidden.value = v;
      seg.querySelectorAll('.seg').forEach(b => {
        const on = b.dataset.value === v;
        b.classList.toggle('is-active', on);
        b.setAttribute('aria-checked', on ? 'true' : 'false');
      });
      if (fire) onChange?.(v);
    }
    seg.querySelectorAll('.seg').forEach(btn =>
      btn.addEventListener('click', () => setValue(btn.dataset.value))
    );
    setValue(hidden.value, false); // initial
    return setValue;
  }

  // ---- Type segmented + value field ----------------------------------
  const valueField  = document.getElementById('valueField');
  const valueInput  = document.getElementById('valueInput');
  const valueSuffix = document.getElementById('valueSuffix');
  const setType = bindSegmented('typeSeg', 'typeInput', v => {
    if (v === 'percent') {
      valueField.hidden = false;
      valueSuffix.textContent = '%';
      valueInput.max = 100;
    } else if (v === 'fixed') {
      valueField.hidden = false;
      valueSuffix.textContent = 'DH';
      valueInput.removeAttribute('max');
    } else { // free_shipping
      valueField.hidden = true;
      valueInput.value = 0;
    }
    updatePreview();
  });

  // ---- Applies-to segmented ------------------------------------------
  const productPicker  = document.getElementById('productPicker');
  const categoryPicker = document.getElementById('categoryPicker');
  bindSegmented('appliesToSeg', 'appliesToInput', v => {
    productPicker.hidden  = v !== 'products';
    categoryPicker.hidden = v !== 'categories';
    updatePreview();
  });

  // ---- Chip pickers (search + chip rendering) ------------------------
  function bindPicker(listId, searchId, chipsId) {
    const list   = document.getElementById(listId);
    const search = document.getElementById(searchId);
    const chips  = document.getElementById(chipsId);

    function renderChips() {
      const checked = list.querySelectorAll('input[type="checkbox"]:checked');
      chips.innerHTML = '';
      if (checked.length === 0) {
        chips.dataset.state = 'empty';
        const empty = document.createElement('span');
        empty.className = 'chips-empty';
        empty.textContent = chips.dataset.empty;
        chips.appendChild(empty);
        return;
      }
      chips.dataset.state = 'filled';
      checked.forEach(cb => {
        const label = cb.closest('label').dataset.label;
        const chip = document.createElement('span');
        chip.className = 'chip';
        chip.innerHTML = `<span>${label}</span><button type="button" aria-label="Retirer">×</button>`;
        chip.querySelector('button').addEventListener('click', () => {
          cb.checked = false;
          renderChips();
          updatePreview();
        });
        chips.appendChild(chip);
      });
    }

    list.addEventListener('change', () => {
      renderChips();
      updatePreview();
    });

    search.addEventListener('input', () => {
      const q = search.value.toLowerCase().trim();
      list.querySelectorAll('li').forEach(li => {
        const label = li.querySelector('label').dataset.label.toLowerCase();
        const sub   = li.querySelector('.item-sub')?.textContent.toLowerCase() || '';
        li.hidden = q && !label.includes(q) && !sub.includes(q);
      });
    });

    renderChips();
  }
  bindPicker('productList',  'productSearch',  'productChips');
  bindPicker('categoryList', 'categorySearch', 'categoryChips');

  // ---- Code generator -------------------------------------------------
  document.getElementById('genCode').addEventListener('click', () => {
    const alphabet = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';
    let code = '';
    for (let i = 0; i < 8; i++) code += alphabet[Math.floor(Math.random() * alphabet.length)];
    const input = document.getElementById('couponCode');
    input.value = code;
    input.dispatchEvent(new Event('input'));
  });

  // ---- Live preview ---------------------------------------------------
  const cpStamp  = document.getElementById('cpStamp');
  const cpCode   = document.getElementById('cpCode');
  const cpAmount = document.getElementById('cpAmount');
  const cpScope  = document.getElementById('cpScope');
  const codeInput = document.getElementById('couponCode');
  const descInput = document.querySelector('input[name="description"]');

  function updatePreview() {
    const code  = (codeInput.value || 'CODE').toUpperCase();
    const type  = document.getElementById('typeInput').value;
    const value = parseFloat(valueInput.value || 0);
    const apply = document.getElementById('appliesToInput').value;

    cpCode.textContent = code;

    if (type === 'percent') {
      cpStamp.textContent = '%';
      cpAmount.textContent = '−' + (Number.isInteger(value) ? value : value.toFixed(0)) + '%';
    } else if (type === 'fixed') {
      cpStamp.textContent = 'DH';
      cpAmount.textContent = '−' + (Number.isInteger(value) ? value : value.toFixed(0)) + ' DH';
    } else {
      cpStamp.innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>';
      cpAmount.textContent = 'Livraison offerte';
    }

    if (apply === 'products') {
      const n = document.querySelectorAll('#productList input:checked').length;
      cpScope.textContent = n ? `${n} produit${n > 1 ? 's' : ''}` : 'Sélectionner des produits';
    } else if (apply === 'categories') {
      const n = document.querySelectorAll('#categoryList input:checked').length;
      cpScope.textContent = n ? `${n} catégorie${n > 1 ? 's' : ''}` : 'Sélectionner des catégories';
    } else {
      cpScope.textContent = 'Toute la boutique';
    }
  }
  codeInput.addEventListener('input', updatePreview);
  valueInput.addEventListener('input', updatePreview);
  descInput.addEventListener('input', updatePreview);
  updatePreview();
})();
</script>

<?php require __DIR__ . '/_includes/footer.php'; ?>
