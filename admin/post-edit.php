<?php
require_once __DIR__ . '/../includes/auth.php';
admin_require_login();

$id = (int) ($_GET['id'] ?? 0);
$is_new = $id === 0;

$migration_pending = false;
$load_error = null;

if ($is_new) {
    $post = [
        'id' => 0, 'slug' => '', 'title' => '',
        'excerpt' => '', 'body' => '', 'cover_url' => '',
        'status' => 'draft', 'meta_title' => '', 'meta_description' => '',
        'published_at' => null,
    ];
} else {
    try {
        $post = db_one('SELECT * FROM posts WHERE id = ?', [$id]);
    } catch (PDOException $e) {
        // Table missing — bounce back to the list, which renders a notice.
        redirect('posts.php');
    }
    if (!$post) redirect('posts.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $title = trim($_POST['title'] ?? '');
    if ($title === '') {
        $load_error = 'Le titre est requis.';
    }

    if (!$load_error) {
        $base_slug = trim($_POST['slug'] ?? '') ?: slugify($title);
        if ($base_slug === '') $base_slug = 'article-' . date('YmdHis');

        // Uniquify slug — mirrors product-edit.php
        $slug = $base_slug;
        $n = 2;
        try {
            while (db_one('SELECT id FROM posts WHERE slug = ? AND id <> ?', [$slug, $id])) {
                $slug = $base_slug . '-' . $n++;
            }
        } catch (PDOException $e) {
            $migration_pending = true;
            $load_error = 'La table posts n\'existe pas encore. Importez sql/migrations/2026-05-19-posts.sql.';
        }
    }

    if (!$load_error) {
        $status = in_array($_POST['status'] ?? '', ['draft', 'published', 'archived'], true)
            ? $_POST['status']
            : 'draft';

        // published_at: respect the form value when given, otherwise stamp NOW
        // on first publish, leave alone on subsequent saves.
        $published_at_raw = trim($_POST['published_at'] ?? '');
        if ($published_at_raw !== '') {
            $ts = strtotime($published_at_raw);
            $published_at = $ts ? date('Y-m-d H:i:s', $ts) : null;
        } else {
            $published_at = $post['published_at'] ?? null;
            if ($status === 'published' && !$published_at) {
                $published_at = date('Y-m-d H:i:s');
            }
        }

        $data = [
            'slug'             => $slug,
            'title'            => $title,
            'excerpt'          => trim($_POST['excerpt'] ?? ''),
            'body'             => $_POST['body'] ?? '',
            'cover_url'        => trim($_POST['cover_url'] ?? ''),
            'status'           => $status,
            'meta_title'       => trim($_POST['meta_title'] ?? '') ?: null,
            'meta_description' => trim($_POST['meta_description'] ?? '') ?: null,
            'published_at'     => $published_at,
        ];

        try {
            if ($is_new) {
                $new_id = db_insert('posts', $data);
                redirect('post-edit.php?id=' . $new_id . '&saved=1');
            } else {
                $set = [];
                $params = [];
                foreach ($data as $k => $v) {
                    $set[] = "$k = ?";
                    $params[] = $v;
                }
                $params[] = $id;
                db_query('UPDATE posts SET ' . implode(', ', $set) . ' WHERE id = ?', $params);
                redirect('post-edit.php?id=' . $id . '&saved=1');
            }
        } catch (PDOException $e) {
            $migration_pending = true;
            $load_error = 'La table posts n\'existe pas encore. Importez sql/migrations/2026-05-19-posts.sql.';
        }
    }
}

$saved = !empty($_GET['saved']);

$page_title = $is_new ? 'Nouvel article' : $post['title'];
$current = 'posts';

$status_lbl_map = ['published' => 'Publié', 'draft' => 'Brouillon', 'archived' => 'Archivé'];
$status_cls_map = ['published' => 'status-active', 'draft' => 'status-draft', 'archived' => 'status-archived'];
$status_lbl = $status_lbl_map[$post['status']] ?? $post['status'];
$status_cls = $status_cls_map[$post['status']] ?? 'status-neutral';

// Pre-fill datetime-local input format: YYYY-MM-DDTHH:MM
$published_at_input = '';
if (!empty($post['published_at'])) {
    $published_at_input = date('Y-m-d\TH:i', strtotime($post['published_at']));
}

require __DIR__ . '/_includes/header.php';
?>

<div class="page">
  <div class="breadcrumb-admin"><a href="index.php">Tableau de bord</a><span>/</span><a href="posts.php">Articles</a><span>/</span><span><?= $is_new ? 'Nouveau' : e($post['title']) ?></span></div>

  <div class="page-head">
    <div>
      <h1 style="display: flex; align-items: center; gap: 14px; flex-wrap: wrap;">
        <?= $is_new ? 'Nouvel article' : e($post['title']) ?>
        <?php if (!$is_new): ?><span class="badge-status <?= e($status_cls) ?>"><?= e($status_lbl) ?></span><?php endif; ?>
      </h1>
      <?php if (!$is_new && !empty($post['published_at'])): ?>
        <p>Publié le <?= e(date('j F Y · H:i', strtotime($post['published_at']))) ?></p>
      <?php endif; ?>
    </div>
    <?php if (!$is_new): ?>
      <div class="page-actions">
        <a href="/post/<?= e($post['slug']) ?>" target="_blank" class="btn btn-ghost">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
          Voir
        </a>
        <button type="button" class="btn btn-ghost" id="deletePost" style="color: var(--danger);" data-id="<?= (int) $post['id'] ?>" data-name="<?= e($post['title']) ?>">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-2 14a2 2 0 0 1-2 2H9a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/></svg>
          Supprimer l'article
        </button>
      </div>
    <?php endif; ?>
  </div>

  <?php if ($migration_pending): ?>
    <div class="flash flash-warning">
      La table <code>posts</code> n'existe pas encore en base de données. Importez
      <code>sql/migrations/2026-05-19-posts.sql</code> via phpMyAdmin avant d'enregistrer.
    </div>
  <?php elseif ($load_error): ?>
    <div class="flash flash-error"><?= e($load_error) ?></div>
  <?php endif; ?>

  <?php if ($saved): ?>
    <div style="background: var(--success-bg); color: var(--success); padding: 12px 18px; border-radius: var(--radius-sm); margin-bottom: 18px; font-size: 0.88rem;">
      ✓ Article enregistré avec succès.
    </div>
  <?php endif; ?>

  <form method="post" class="detail-grid">
    <?= csrf_field() ?>
    <div style="display: flex; flex-direction: column; gap: 16px;">

      <div class="card">
        <div class="card-head"><h3>Contenu</h3></div>
        <div class="card-body">
          <div class="form-grid">
            <div class="field full">
              <label>Titre <span class="required">*</span></label>
              <input type="text" name="title" required value="<?= e($post['title']) ?>" placeholder="Le titre de votre article">
            </div>
            <div class="field full">
              <label>Slug URL</label>
              <input type="text" name="slug" value="<?= e($post['slug']) ?>" placeholder="auto-généré depuis le titre">
              <small class="help">L'URL publique sera <code>/post/<?= e($post['slug'] ?: 'votre-slug') ?></code></small>
            </div>
            <div class="field full">
              <label>Extrait</label>
              <textarea name="excerpt" rows="3" placeholder="Court résumé affiché dans la liste du blog et les méta-données."><?= e($post['excerpt']) ?></textarea>
            </div>
            <div class="field full">
              <label>Corps de l'article</label>
              <textarea name="body" rows="18" style="font-family: ui-monospace, 'SF Mono', Menlo, monospace; font-size: 0.88rem; line-height: 1.6;"><?= e($post['body']) ?></textarea>
              <small class="help">HTML autorisé (<code>&lt;p&gt;</code>, <code>&lt;h2&gt;</code>, <code>&lt;ul&gt;</code>, <code>&lt;a&gt;</code>, <code>&lt;img&gt;</code>, etc.). Le contenu est rendu tel quel sur la page publique.</small>
            </div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-head"><h3>Image de couverture</h3></div>
        <div class="card-body">
          <div class="upload-widget"
               data-target="posts"
               data-name="cover_url"
               data-current="<?= e($post['cover_url']) ?>"></div>
          <p class="help" style="margin-top: 10px;">Format conseillé : JPG ou WebP, paysage (16:9 ou 4:3), au moins 1200 px de large.</p>
        </div>
      </div>

      <div class="card">
        <div class="card-head"><h3>SEO</h3></div>
        <div class="card-body">
          <div class="form-grid">
            <div class="field full">
              <label>Titre méta</label>
              <input type="text" name="meta_title" value="<?= e($post['meta_title'] ?? '') ?>" placeholder="Laisser vide pour utiliser le titre de l'article">
            </div>
            <div class="field full">
              <label>Description méta</label>
              <textarea name="meta_description" rows="2" placeholder="Laisser vide pour utiliser l'extrait"><?= e($post['meta_description'] ?? '') ?></textarea>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div style="display: flex; flex-direction: column; gap: 16px;">
      <div class="card">
        <div class="card-head"><h3>Publication</h3></div>
        <div class="card-body">
          <div class="form-grid">
            <div class="field full">
              <label>Statut</label>
              <select name="status" class="field-select">
                <option value="draft"     <?= $post['status'] === 'draft' ? 'selected' : '' ?>>Brouillon</option>
                <option value="published" <?= $post['status'] === 'published' ? 'selected' : '' ?>>Publié</option>
                <option value="archived"  <?= $post['status'] === 'archived' ? 'selected' : '' ?>>Archivé</option>
              </select>
            </div>
            <div class="field full">
              <label>Date de publication</label>
              <input type="datetime-local" name="published_at" value="<?= e($published_at_input) ?>">
              <small class="help">Laisser vide pour utiliser l'instant du premier passage en « Publié ».</small>
            </div>
          </div>
        </div>
      </div>

      <button type="submit" class="btn btn-primary btn-lg">
        <?= $is_new ? 'Créer l\'article' : 'Enregistrer' ?>
      </button>
    </div>
  </form>
</div>

<script>
// Delete the whole post — reuses posts.php's bulk_action=delete handler.
(function () {
  const btn = document.getElementById('deletePost');
  if (!btn) return;
  btn.addEventListener('click', () => {
    const id   = btn.dataset.id;
    const name = btn.dataset.name || 'cet article';
    if (!confirm('Supprimer définitivement « ' + name + ' » ? Cette action est irréversible.')) return;
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const f = document.createElement('form');
    f.method = 'POST';
    f.action = 'posts.php';
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
