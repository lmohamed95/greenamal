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
    $data = [
        'name'              => trim($_POST['name'] ?? ''),
        'slug'              => trim($_POST['slug'] ?? '') ?: slugify($_POST['name'] ?? ''),
        'sku'               => trim($_POST['sku'] ?? ''),
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
        redirect('product-edit.php?id=' . $new_id . '&saved=1');
    } else {
        $set = [];
        $params = [];
        foreach ($data as $k => $v) {
            $set[] = "$k = ?";
            $params[] = $v;
        }
        $params[] = $id;
        db_query('UPDATE products SET ' . implode(', ', $set) . ' WHERE id = ?', $params);
        redirect('product-edit.php?id=' . $id . '&saved=1');
    }
}

$saved = !empty($_GET['saved']);
$categories = db_all("SELECT * FROM categories ORDER BY display_order");

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

      <div class="card">
        <div class="card-head"><h3>Prix</h3></div>
        <div class="card-body">
          <div class="form-grid">
            <div class="field">
              <label>Prix de vente <span class="required">*</span></label>
              <input type="number" name="price" step="0.01" required value="<?= e((string) $product['price']) ?>"><span class="help">en د.م. (TTC)</span>
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
              <option value="">— aucune —</option>
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

<?php require __DIR__ . '/_includes/footer.php'; ?>
