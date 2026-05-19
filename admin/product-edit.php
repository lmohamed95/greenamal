<?php
require_once __DIR__ . '/../includes/auth.php';
admin_require_login();

$id = (int) ($_GET['id'] ?? 0);
$is_new = $id === 0;
$product = $is_new ? [
    'id' => 0, 'name' => '', 'slug' => '', 'sku' => '', 'category_id' => null,
    'description_short' => '', 'description_long' => '', 'price' => 0, 'compare_at_price' => 0,
    'cost' => 0, 'stock' => 0, 'low_stock_threshold' => 5, 'image_main' => '',
    'status' => 'draft', 'is_featured' => 0, 'tags' => '',
    'meta_title' => '', 'meta_description' => '',
    'sales_count' => 0, 'rating_avg' => 0, 'rating_count' => 0,
] : db_one('SELECT * FROM products WHERE id = ?', [$id]);

if (!$product) redirect('products.php');

$saved = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $base_slug = trim($_POST['slug'] ?? '') ?: slugify($_POST['name'] ?? '');
    $slug = $base_slug;
    $n = 2;
    while (db_one('SELECT id FROM products WHERE slug = ? AND id <> ?', [$slug, $id])) {
        $slug = $base_slug . '-' . $n++;
    }
    $data = [
        'name'              => trim($_POST['name'] ?? ''),
        'slug'              => $slug,
        'sku'               => trim($_POST['sku'] ?? '') ?: null,
        'category_id'       => (int) ($_POST['category_id'] ?? 0) ?: null,
        'description_short' => trim($_POST['description_short'] ?? ''),
        'description_long'  => trim($_POST['description_long'] ?? ''),
        'price'             => (float) ($_POST['price'] ?? 0),
        'compare_at_price'  => (float) ($_POST['compare_at_price'] ?? 0) ?: null,
        'cost'              => (float) ($_POST['cost'] ?? 0) ?: null,
        'stock'             => (int) ($_POST['stock'] ?? 0),
        'low_stock_threshold' => (int) ($_POST['low_stock_threshold'] ?? 5),
        'image_main'        => trim($_POST['image_main'] ?? ''),
        'status'            => $_POST['status'] ?? 'draft',
        'is_featured'       => isset($_POST['is_featured']) ? 1 : 0,
        'tags'              => trim($_POST['tags'] ?? ''),
        'meta_title'        => trim($_POST['meta_title'] ?? ''),
        'meta_description'  => trim($_POST['meta_description'] ?? ''),
    ];
    if ($is_new) {
        $new_id = db_insert('products', $data);
        $pack_id = $new_id;
    } else {
        $set = [];
        $params = [];
        foreach ($data as $k => $v) {
            $set[] = "$k = ?";
            $params[] = $v;
        }
        $params[] = $id;
        db_query('UPDATE products SET ' . implode(', ', $set) . ' WHERE id = ?', $params);
        $pack_id = $id;
    }

    // Sync pack components · rebuild based on submitted lists
    $component_ids  = $_POST['component_id']  ?? [];
    $component_qtys = $_POST['component_qty'] ?? [];
    db_query('DELETE FROM product_components WHERE pack_id = ?', [$pack_id]);
    foreach ($component_ids as $i => $cid) {
        $cid = (int) $cid;
        if ($cid <= 0 || $cid === $pack_id) continue; // refuse 0 / self-ref
        $qty = max(1, (int) ($component_qtys[$i] ?? 1));
        db_query(
            'INSERT IGNORE INTO product_components (pack_id, component_id, quantity, display_order) VALUES (?, ?, ?, ?)',
            [$pack_id, $cid, $qty, $i]
        );
    }

    redirect('product-edit.php?id=' . $pack_id . '&saved=1');
}

$saved = !empty($_GET['saved']);
$categories = db_all("SELECT * FROM categories ORDER BY display_order");

// Pack composition: existing components + candidate products for the picker
$existing_components = $is_new ? [] : product_components((int) $id);
$candidate_products = db_all(
    "SELECT p.id, p.name, p.sku, p.price, p.image_main, c.name AS category_name
     FROM products p
     LEFT JOIN categories c ON c.id = p.category_id
     WHERE p.id <> ? AND p.status IN ('active','draft')
     ORDER BY p.name",
    [(int) $id]
);

$page_title = $is_new ? 'Nouveau produit' : $product['name'];
$current = 'products';

[$status_lbl, $status_cls] = product_status_label($product['status']);

require __DIR__ . '/_includes/header.php';
?>

<div class="page">
  <div class="breadcrumb-admin"><a href="index.php">Tableau de bord</a><span>/</span><a href="products.php">Produits</a><span>/</span><span><?= $is_new ? 'Nouveau' : e($product['name']) ?></span></div>

  <div class="page-head">
    <div>
      <h1 style="display: flex; align-items: center; gap: 14px; flex-wrap: wrap;">
        <?= $is_new ? 'Nouveau produit' : e($product['name']) ?>
        <?php if (!$is_new): ?><span class="badge-status <?= e($status_cls) ?>"><?= e($status_lbl) ?></span><?php endif; ?>
      </h1>
      <?php if (!$is_new): ?>
        <p>SKU : <?= e($product['sku']) ?> · <?= (int) $product['sales_count'] ?> ventes · ★ <?= number_format($product['rating_avg'], 1) ?> (<?= (int) $product['rating_count'] ?>)</p>
      <?php endif; ?>
    </div>
    <?php if (!$is_new): ?>
      <div class="page-actions">
        <button type="button" class="btn btn-ghost" id="deleteProduct" style="color: var(--danger);" data-id="<?= (int) $product['id'] ?>" data-name="<?= e($product['name']) ?>">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-2 14a2 2 0 0 1-2 2H9a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/></svg>
          Supprimer le produit
        </button>
      </div>
    <?php endif; ?>
  </div>

  <?php if ($saved): ?>
    <div style="background: var(--success-bg); color: var(--success); padding: 12px 18px; border-radius: var(--radius-sm); margin-bottom: 18px; font-size: 0.88rem;">
      ✓ Produit enregistré avec succès.
    </div>
  <?php endif; ?>

  <form method="post" class="detail-grid">
    <?= csrf_field() ?>
    <div style="display: flex; flex-direction: column; gap: 16px;">

      <div class="card">
        <div class="card-head"><h3>Informations générales</h3></div>
        <div class="card-body">
          <div class="form-grid">
            <div class="field full">
              <label>Nom du produit <span class="required">*</span></label>
              <input type="text" name="name" required value="<?= e($product['name']) ?>">
            </div>
            <div class="field">
              <label>Slug URL</label>
              <input type="text" name="slug" value="<?= e($product['slug']) ?>" placeholder="auto-généré si vide">
            </div>
            <div class="field">
              <label>SKU</label>
              <input type="text" name="sku" value="<?= e($product['sku']) ?>">
            </div>
            <div class="field full">
              <label>Description courte</label>
              <textarea name="description_short" rows="3"><?= e($product['description_short']) ?></textarea>
            </div>
            <div class="field full">
              <label>Description longue</label>
              <textarea name="description_long" rows="6"><?= e($product['description_long']) ?></textarea>
            </div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-head"><h3>Image principale</h3></div>
        <div class="card-body">
          <div class="upload-widget"
               data-target="products"
               data-name="image_main"
               data-current="<?= e($product['image_main']) ?>"></div>
          <p class="help" style="margin-top: 10px;">Format conseillé : JPG ou WebP, carré (1:1), au moins 800 × 800 px. Le recadrage s'ouvre automatiquement après sélection.</p>
        </div>
      </div>

      <div class="card">
        <div class="card-head">
          <h3>Galerie produit</h3>
          <span class="head-meta">images supplémentaires affichées sur la page produit</span>
        </div>
        <div class="card-body">
          <div class="gallery-widget" data-product-id="<?= $is_new ? 0 : (int) $id ?>"></div>
        </div>
      </div>

      <div class="card" id="componentsCard">
        <div class="card-head">
          <h3>Composition du pack</h3>
          <span class="head-meta">Si ce produit est un pack/coffret, listez les produits qu'il contient.</span>
        </div>
        <div class="card-body">
          <ul class="components-list" id="componentsList">
            <?php foreach ($existing_components as $i => $c): ?>
              <li class="component-row" data-id="<?= (int) $c['id'] ?>">
                <img src="<?= e($c['image_main']) ?>" alt="" class="component-thumb">
                <div class="component-meta">
                  <strong class="component-name"><?= e($c['name']) ?></strong>
                  <span class="component-price"><?= e(price((float) $c['price'])) ?> · l'unité</span>
                </div>
                <input type="hidden" name="component_id[]" value="<?= (int) $c['id'] ?>">
                <label class="component-qty">
                  <span>Qté</span>
                  <input type="number" name="component_qty[]" min="1" value="<?= (int) $c['quantity'] ?>">
                </label>
                <button type="button" class="component-remove" aria-label="Retirer" title="Retirer">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
              </li>
            <?php endforeach; ?>
          </ul>
          <p class="components-empty" id="componentsEmpty"<?= $existing_components ? ' hidden' : '' ?>>
            Aucun composant pour le moment. Ce produit sera traité comme un produit simple.
          </p>

          <div class="components-add">
            <button type="button" class="btn btn-secondary btn-sm" id="componentsAddBtn">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
              Ajouter un composant
            </button>
            <div class="components-picker" id="componentsPicker" hidden>
              <div class="chip-search-wrap">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="search" id="componentSearch" placeholder="Rechercher un produit…">
              </div>
              <ul class="chip-list" id="componentChoices">
                <?php foreach ($candidate_products as $cp): ?>
                  <li data-id="<?= (int) $cp['id'] ?>"
                      data-name="<?= e($cp['name']) ?>"
                      data-image="<?= e($cp['image_main']) ?>"
                      data-price="<?= e(price((float) $cp['price'])) ?>"
                      data-sku="<?= e($cp['sku']) ?>"
                      data-cat="<?= e($cp['category_name'] ?? '') ?>">
                    <img src="<?= e($cp['image_main']) ?>" alt="" class="component-thumb">
                    <div class="component-meta">
                      <strong><?= e($cp['name']) ?></strong>
                      <span><?= e($cp['sku']) ?> · <?= e($cp['category_name'] ?? '') ?></span>
                    </div>
                    <span class="component-add-cta">+ Ajouter</span>
                  </li>
                <?php endforeach; ?>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-head"><h3>Prix</h3></div>
        <div class="card-body">
          <div class="form-grid">
            <div class="field">
              <label>Prix de vente <span class="required">*</span></label>
              <input type="number" name="price" step="0.01" required value="<?= e((string) $product['price']) ?>"><span class="help">en DH (TTC)</span>
            </div>
            <div class="field">
              <label>Prix barré</label>
              <input type="number" name="compare_at_price" step="0.01" value="<?= e((string) $product['compare_at_price']) ?>"><span class="help">Affiché barré (promo)</span>
            </div>
            <div class="field">
              <label>Coût d'achat</label>
              <input type="number" name="cost" step="0.01" value="<?= e((string) $product['cost']) ?>"><span class="help">Privé · pour la marge</span>
            </div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-head"><h3>SEO</h3></div>
        <div class="card-body">
          <div class="form-grid">
            <div class="field full">
              <label>Titre SEO</label>
              <input type="text" name="meta_title" value="<?= e($product['meta_title']) ?>">
            </div>
            <div class="field full">
              <label>Description SEO</label>
              <textarea name="meta_description" rows="3"><?= e($product['meta_description']) ?></textarea>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div style="display: flex; flex-direction: column; gap: 16px;">
      <div class="card">
        <div class="card-head"><h3>Statut</h3></div>
        <div class="card-body">
          <div class="field">
            <select name="status" class="field-input">
              <option value="active" <?= $product['status'] === 'active' ? 'selected' : '' ?>>Actif (publié)</option>
              <option value="draft" <?= $product['status'] === 'draft' ? 'selected' : '' ?>>Brouillon</option>
              <option value="archived" <?= $product['status'] === 'archived' ? 'selected' : '' ?>>Archivé</option>
            </select>
          </div>
          <label class="toggle" style="margin-top: 16px;">
            <input type="checkbox" name="is_featured" <?= $product['is_featured'] ? 'checked' : '' ?>>
            <span class="toggle-track"></span>
            <span>Mettre en avant (best-sellers)</span>
          </label>
        </div>
      </div>

      <div class="card">
        <div class="card-head"><h3>Catégorie & tags</h3></div>
        <div class="card-body">
          <div class="field">
            <label>Catégorie</label>
            <select name="category_id" class="field-input">
              <option value="">- aucune -</option>
              <?php foreach ($categories as $c): ?>
                <option value="<?= (int) $c['id'] ?>" <?= $product['category_id'] == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="field" style="margin-top: 14px;">
            <label>Tags</label>
            <input type="text" name="tags" value="<?= e($product['tags']) ?>" placeholder="argan, bio, pressé à froid">
            <span class="help">Séparés par des virgules</span>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-head"><h3>Stock</h3></div>
        <div class="card-body">
          <div class="field">
            <label>Quantité en stock</label>
            <input type="number" name="stock" value="<?= e((string) $product['stock']) ?>">
          </div>
          <div class="field" style="margin-top: 12px;">
            <label>Seuil d'alerte</label>
            <input type="number" name="low_stock_threshold" value="<?= e((string) $product['low_stock_threshold']) ?>">
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-body">
          <button type="submit" class="btn btn-primary btn-block btn-lg">Enregistrer</button>
          <a href="products.php" class="btn btn-ghost btn-block" style="margin-top: 8px;">Annuler</a>
        </div>
      </div>
    </div>
  </form>
</div>

<script>
(function () {
  const list      = document.getElementById('componentsList');
  const empty     = document.getElementById('componentsEmpty');
  const addBtn    = document.getElementById('componentsAddBtn');
  const picker    = document.getElementById('componentsPicker');
  const search    = document.getElementById('componentSearch');
  const choices   = document.getElementById('componentChoices');
  if (!list) return;

  function refreshEmpty() {
    empty.hidden = list.children.length > 0;
  }
  refreshEmpty();

  // Toggle picker
  addBtn.addEventListener('click', () => {
    picker.hidden = !picker.hidden;
    if (!picker.hidden) {
      search.value = '';
      choices.querySelectorAll('li').forEach(li => li.hidden = false);
      search.focus();
    }
  });

  // Filter the choice list
  search.addEventListener('input', () => {
    const q = search.value.toLowerCase().trim();
    choices.querySelectorAll('li').forEach(li => {
      const hay = (li.dataset.name + ' ' + li.dataset.sku + ' ' + li.dataset.cat).toLowerCase();
      li.hidden = q !== '' && !hay.includes(q);
    });
  });

  // Add a chosen product as a new component row
  choices.addEventListener('click', (e) => {
    const li = e.target.closest('li[data-id]');
    if (!li) return;
    const id = li.dataset.id;
    if (list.querySelector('.component-row[data-id="' + id + '"]')) {
      // already added → just close picker
      picker.hidden = true;
      return;
    }
    const row = document.createElement('li');
    row.className = 'component-row';
    row.dataset.id = id;
    row.innerHTML = `
      <img src="${li.dataset.image}" alt="" class="component-thumb">
      <div class="component-meta">
        <strong class="component-name">${li.dataset.name}</strong>
        <span class="component-price">${li.dataset.price} · l'unité</span>
      </div>
      <input type="hidden" name="component_id[]" value="${id}">
      <label class="component-qty">
        <span>Qté</span>
        <input type="number" name="component_qty[]" min="1" value="1">
      </label>
      <button type="button" class="component-remove" aria-label="Retirer" title="Retirer">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>`;
    list.appendChild(row);
    picker.hidden = true;
    refreshEmpty();
  });

  // Remove a row
  list.addEventListener('click', (e) => {
    const btn = e.target.closest('.component-remove');
    if (!btn) return;
    btn.closest('.component-row').remove();
    refreshEmpty();
  });
})();

// Delete the whole product — posts to products.php's bulk_action=delete with
// a single id, then the handler redirects back to the list with a flash.
(function () {
  const btn = document.getElementById('deleteProduct');
  if (!btn) return;
  btn.addEventListener('click', () => {
    const id   = btn.dataset.id;
    const name = btn.dataset.name || 'ce produit';
    if (!confirm('Supprimer définitivement « ' + name + ' » ? Cette action est irréversible.')) return;
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const f = document.createElement('form');
    f.method = 'POST';
    f.action = 'products.php';
    const fields = { _csrf: csrf, bulk_action: 'delete', 'ids[]': id };
    Object.entries(fields).forEach(([k, v]) => {
      const i = document.createElement('input');
      i.type = 'hidden';
      i.name = k;
      i.value = v;
      f.appendChild(i);
    });
    document.body.appendChild(f);
    f.submit();
  });
})();
</script>

<?php require __DIR__ . '/_includes/footer.php'; ?>
