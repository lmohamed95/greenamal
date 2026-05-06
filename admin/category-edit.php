<?php
require_once __DIR__ . '/../includes/auth.php';
admin_require_login();

$id = (int) ($_GET['id'] ?? 0);
$is_new = $id === 0;
$category = $is_new
    ? ['id' => 0, 'name' => '', 'slug' => '', 'description' => '', 'image_url' => '', 'display_order' => 0]
    : db_one('SELECT * FROM categories WHERE id = ?', [$id]);

if (!$category) redirect('categories.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $data = [
        'name'          => trim($_POST['name'] ?? ''),
        'slug'          => trim($_POST['slug'] ?? '') ?: slugify($_POST['name'] ?? ''),
        'description'   => trim($_POST['description'] ?? ''),
        'image_url'     => trim($_POST['image_url'] ?? ''),
        'display_order' => (int) ($_POST['display_order'] ?? 0),
    ];

    if (empty($data['name'])) {
        $error = 'Le nom est requis.';
    } else {
        if ($is_new) {
            $new_id = db_insert('categories', $data);
            redirect('category-edit.php?id=' . $new_id . '&saved=1');
        } else {
            $set = [];
            $params = [];
            foreach ($data as $k => $v) { $set[] = "$k = ?"; $params[] = $v; }
            $params[] = $id;
            db_query('UPDATE categories SET ' . implode(', ', $set) . ' WHERE id = ?', $params);
            redirect('category-edit.php?id=' . $id . '&saved=1');
        }
    }
}

$saved = !empty($_GET['saved']);
$products_in_cat = $is_new ? [] : db_all('SELECT id, name, image_main, sales_count FROM products WHERE category_id = ? ORDER BY sales_count DESC LIMIT 10', [$id]);
$products_count = $is_new ? 0 : (int) db_value('SELECT COUNT(*) FROM products WHERE category_id = ?', [$id]);

$page_title = $is_new ? 'Nouvelle catégorie' : $category['name'];
$current = 'categories';

require __DIR__ . '/_includes/header.php';
?>

<div class="page">
  <div class="breadcrumb-admin">
    <a href="index.php">Tableau de bord</a><span>/</span>
    <a href="categories.php">Catégories</a><span>/</span>
    <span><?= $is_new ? 'Nouvelle' : e($category['name']) ?></span>
  </div>

  <div class="page-head">
    <div>
      <h1><?= $is_new ? 'Nouvelle catégorie' : e($category['name']) ?></h1>
      <?php if (!$is_new): ?>
        <p>Slug : <code style="background: var(--surface-2); padding: 2px 8px; border-radius: 4px;"><?= e($category['slug']) ?></code> · <?= $products_count ?> produit<?= $products_count > 1 ? 's' : '' ?> dans cette catégorie</p>
      <?php endif; ?>
    </div>
    <div class="page-actions">
      <a href="categories.php" class="btn btn-ghost">← Retour</a>
    </div>
  </div>

  <?php if ($saved): ?>
    <div style="background: var(--success-bg); color: var(--success); padding: 12px 18px; border-radius: var(--radius-sm); margin-bottom: 18px; font-size: 0.88rem;">✓ Catégorie enregistrée.</div>
  <?php endif; ?>
  <?php if (!empty($error)): ?>
    <div style="background: var(--danger-bg); color: var(--danger); padding: 12px 18px; border-radius: var(--radius-sm); margin-bottom: 18px; font-size: 0.88rem;"><?= e($error) ?></div>
  <?php endif; ?>

  <form method="post" class="detail-grid">
    <?= csrf_field() ?>
    <div style="display: flex; flex-direction: column; gap: 16px;">

      <div class="card">
        <div class="card-head"><h3>Informations</h3></div>
        <div class="card-body">
          <div class="form-grid">
            <div class="field full">
              <label>Nom de la catégorie <span class="required">*</span></label>
              <input type="text" name="name" required value="<?= e($category['name']) ?>" placeholder="Ex : Huiles essentielles">
            </div>
            <div class="field full">
              <label>Slug URL</label>
              <input type="text" name="slug" value="<?= e($category['slug']) ?>" placeholder="auto-généré si vide">
              <span class="help">URL : /shop.php?cat=<strong><?= e($category['slug'] ?: 'mon-slug') ?></strong></span>
            </div>
            <div class="field full">
              <label>Description</label>
              <textarea name="description" rows="3" placeholder="Phrase courte qui décrit la catégorie (visible sur la page Catégories)..."><?= e($category['description']) ?></textarea>
              <span class="help">~100-160 caractères pour un bon affichage.</span>
            </div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-head">
          <h3>Image bannière</h3>
          <span class="head-meta">utilisée sur la page Catégories</span>
        </div>
        <div class="card-body">
          <div class="upload-widget"
               data-target="categories"
               data-name="image_url"
               data-current="<?= e($category['image_url']) ?>"></div>
          <p class="help" style="margin-top: 10px;">Format conseillé : JPG ou WebP, ratio 16:9, au moins 1200 × 675 px. Maximum 5 Mo.</p>
        </div>
      </div>

      <?php if (!$is_new && !empty($products_in_cat)): ?>
        <div class="card">
          <div class="card-head">
            <h3>Produits dans cette catégorie</h3>
            <a href="products.php?cat=<?= e($category['slug']) ?>" style="font-size: 0.82rem; color: var(--olive); font-weight: 500;">Tout voir →</a>
          </div>
          <table class="data-table">
            <thead><tr><th>Produit</th><th>Ventes</th><th></th></tr></thead>
            <tbody>
              <?php foreach ($products_in_cat as $p): ?>
                <tr>
                  <td><div class="cell-product"><img src="<?= e($p['image_main']) ?>" alt=""><span><strong><?= e($p['name']) ?></strong></span></div></td>
                  <td class="cell-num"><?= (int) $p['sales_count'] ?></td>
                  <td><a href="product-edit.php?id=<?= (int) $p['id'] ?>" class="btn btn-ghost btn-sm">Modifier</a></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>

    <div style="display: flex; flex-direction: column; gap: 16px;">
      <div class="card">
        <div class="card-head"><h3>Affichage</h3></div>
        <div class="card-body">
          <div class="field">
            <label>Ordre d'affichage</label>
            <input type="number" name="display_order" value="<?= e((string) $category['display_order']) ?>">
            <span class="help">Plus le chiffre est petit, plus la catégorie apparaît en premier (1, 2, 3...).</span>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-body">
          <button type="submit" class="btn btn-primary btn-block btn-lg">
            <?= $is_new ? 'Créer la catégorie' : 'Enregistrer' ?>
          </button>
          <a href="categories.php" class="btn btn-ghost btn-block" style="margin-top: 8px;">Annuler</a>
        </div>
      </div>

      <?php if (!$is_new && $products_count === 0): ?>
        <div class="card" style="border-color: var(--danger-bg);">
          <div class="card-body">
            <p style="font-size: 0.85rem; color: var(--ink-soft); margin-bottom: 12px;">Cette catégorie est vide. Vous pouvez la supprimer en toute sécurité.</p>
            <a href="categories.php?delete=<?= (int) $id ?>" onclick="return confirm('Supprimer définitivement cette catégorie ?')" class="btn btn-ghost btn-block" style="color: var(--danger); border: 1px solid var(--danger-bg);">
              Supprimer la catégorie
            </a>
          </div>
        </div>
      <?php elseif (!$is_new): ?>
        <div class="card">
          <div class="card-body">
            <p style="font-size: 0.82rem; color: var(--ink-mute); margin: 0;">
              ⚠ Cette catégorie contient <?= $products_count ?> produit<?= $products_count > 1 ? 's' : '' ?>. Réassignez-les avant de pouvoir supprimer.
            </p>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </form>
</div>

<?php require __DIR__ . '/_includes/footer.php'; ?>
