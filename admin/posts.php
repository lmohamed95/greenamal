<?php
require_once __DIR__ . '/../includes/auth.php';
admin_require_login();

// =====================================================================
// Bulk actions (POST) — mirrors admin/products.php
// =====================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_action'])) {
    csrf_verify();
    $action = (string) $_POST['bulk_action'];
    $ids = array_values(array_filter(array_map('intval', (array) ($_POST['ids'] ?? []))));

    if ($ids && in_array($action, ['published', 'draft', 'archived', 'delete'], true)) {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        switch ($action) {
            case 'delete':
                db_query("DELETE FROM posts WHERE id IN ($placeholders)", $ids);
                $_SESSION['flash'] = ['type' => 'success', 'msg' => count($ids) . ' article(s) supprimé(s).'];
                break;
            case 'published':
                // Set published_at only if it was NULL — preserves the original
                // publication date when re-publishing an archived/draft post.
                $params = array_merge([date('Y-m-d H:i:s')], $ids);
                db_query("UPDATE posts SET status = 'published', published_at = COALESCE(published_at, ?) WHERE id IN ($placeholders)", $params);
                $_SESSION['flash'] = ['type' => 'success', 'msg' => count($ids) . ' article(s) publié(s).'];
                break;
            default: // draft | archived
                $params = array_merge([$action], $ids);
                db_query("UPDATE posts SET status = ? WHERE id IN ($placeholders)", $params);
                $labels = ['draft' => 'mis en brouillon', 'archived' => 'archivé(s)'];
                $_SESSION['flash'] = ['type' => 'success', 'msg' => count($ids) . ' article(s) ' . $labels[$action] . '.'];
        }
    } else {
        $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Aucun article sélectionné ou action invalide.'];
    }

    $qs = http_build_query(array_intersect_key($_POST, array_flip(['status'])));
    redirect('posts.php' . ($qs ? '?' . $qs : ''));
}

$page_title = 'Articles';
$current = 'posts';

$status_filter = $_GET['status'] ?? 'all';

$where = [];
$params = [];
if ($status_filter !== 'all') {
    $where[] = "status = ?";
    $params[] = $status_filter;
}
$whereSql = empty($where) ? '' : 'WHERE ' . implode(' AND ', $where);

$migration_pending = false;
try {
    $posts = db_all("
        SELECT id, slug, title, excerpt, cover_url, status, published_at, created_at
        FROM posts
        $whereSql
        ORDER BY COALESCE(published_at, created_at) DESC
        LIMIT 100
    ", $params);

    $counts = db_one("
        SELECT
          COUNT(*) AS total,
          SUM(status='published') AS published,
          SUM(status='draft') AS draft,
          SUM(status='archived') AS archived
        FROM posts
    ");
} catch (PDOException $e) {
    // Migration hasn't been run yet — show empty state + a notice instead of 500
    $posts = [];
    $counts = ['total' => 0, 'published' => 0, 'draft' => 0, 'archived' => 0];
    $migration_pending = true;
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

require __DIR__ . '/_includes/header.php';
?>

<div class="page">
  <div class="breadcrumb-admin"><a href="index.php">Tableau de bord</a><span>/</span><span>Articles</span></div>
  <div class="page-head">
    <div>
      <h1>Articles</h1>
      <p><?= (int) $counts['total'] ?> articles · <?= (int) $counts['published'] ?> publiés · <?= (int) $counts['draft'] ?> brouillons</p>
    </div>
    <div class="page-actions">
      <a href="post-edit.php" class="btn btn-primary">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Nouvel article
      </a>
    </div>
  </div>

  <?php if ($migration_pending): ?>
    <div class="flash flash-warning">
      La table <code>posts</code> n'existe pas encore en base de données. Importez
      <code>sql/migrations/2026-05-19-posts.sql</code> via phpMyAdmin pour activer la rédaction d'articles.
    </div>
  <?php endif; ?>

  <?php if ($flash): ?>
    <div class="flash flash-<?= e($flash['type']) ?>"><?= e($flash['msg']) ?></div>
  <?php endif; ?>

  <form method="get" class="toolbar">
    <div class="toolbar-tabs">
      <a href="?status=all" class="toolbar-tab<?= $status_filter === 'all' ? ' active' : '' ?>">Tous <span class="count"><?= (int) $counts['total'] ?></span></a>
      <a href="?status=published" class="toolbar-tab<?= $status_filter === 'published' ? ' active' : '' ?>">Publiés <span class="count"><?= (int) $counts['published'] ?></span></a>
      <a href="?status=draft" class="toolbar-tab<?= $status_filter === 'draft' ? ' active' : '' ?>">Brouillons <span class="count"><?= (int) $counts['draft'] ?></span></a>
      <a href="?status=archived" class="toolbar-tab<?= $status_filter === 'archived' ? ' active' : '' ?>">Archivés <span class="count"><?= (int) $counts['archived'] ?></span></a>
    </div>
  </form>

  <form method="post" id="bulkForm">
    <?= csrf_field() ?>
    <input type="hidden" name="status" value="<?= e($status_filter) ?>">

    <div class="bulk-bar" id="bulkBar" hidden>
      <span class="bulk-count"><strong id="bulkCount">0</strong> sélectionné(s)</span>
      <button type="submit" name="bulk_action" value="published" class="btn btn-sm btn-secondary">Publier</button>
      <button type="submit" name="bulk_action" value="draft" class="btn btn-sm btn-ghost">Brouillon</button>
      <button type="submit" name="bulk_action" value="archived" class="btn btn-sm btn-ghost">Archiver</button>
      <button type="submit" name="bulk_action" value="delete" class="btn btn-sm btn-danger" data-confirm="Supprimer définitivement les articles sélectionnés ?">Supprimer</button>
      <button type="button" class="btn btn-sm btn-ghost" id="bulkClear">Annuler</button>
    </div>

    <div class="card">
      <table class="data-table" id="postsTable">
        <thead>
          <tr>
            <th class="check-col"><input type="checkbox" id="checkAll" aria-label="Tout sélectionner"></th>
            <th>Article</th><th>Slug</th><th>Statut</th><th>Publié le</th><th class="actions-col"></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($posts as $p):
            $status_lbl_map = ['published' => 'Publié', 'draft' => 'Brouillon', 'archived' => 'Archivé'];
            $status_cls_map = ['published' => 'status-active', 'draft' => 'status-draft', 'archived' => 'status-archived'];
            $status_lbl = $status_lbl_map[$p['status']] ?? $p['status'];
            $status_cls = $status_cls_map[$p['status']] ?? 'status-neutral';
            $published_display = $p['published_at'] ? date('j M Y', strtotime($p['published_at'])) : '—';
          ?>
            <tr>
              <td><input type="checkbox" name="ids[]" value="<?= (int) $p['id'] ?>" class="row-check" aria-label="Sélectionner <?= e($p['title']) ?>"></td>
              <td>
                <div class="cell-product">
                  <?php if (!empty($p['cover_url'])): ?>
                    <img src="<?= e($p['cover_url']) ?>" alt="">
                  <?php else: ?>
                    <div style="width:36px;height:36px;border-radius:6px;background:var(--surface-2);display:grid;place-items:center;color:var(--ink-mute);">
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/></svg>
                    </div>
                  <?php endif; ?>
                  <span><strong><?= e($p['title']) ?></strong><?php if (!empty($p['excerpt'])): ?><br><small class="cell-mute"><?= e(mb_strimwidth($p['excerpt'], 0, 80, '…')) ?></small><?php endif; ?></span>
                </div>
              </td>
              <td class="cell-mono"><?= e($p['slug']) ?></td>
              <td><span class="badge-status <?= e($status_cls) ?>"><?= e($status_lbl) ?></span></td>
              <td><?= e($published_display) ?></td>
              <td>
                <div class="row-actions">
                  <a href="post-edit.php?id=<?= (int) $p['id'] ?>" class="topbar-btn" title="Modifier" aria-label="Modifier <?= e($p['title']) ?>">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                  </a>
                  <button type="button" class="topbar-btn row-delete" data-id="<?= (int) $p['id'] ?>" data-name="<?= e($p['title']) ?>" title="Supprimer" aria-label="Supprimer <?= e($p['title']) ?>">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-2 14a2 2 0 0 1-2 2H9a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/></svg>
                  </button>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$posts): ?>
            <tr><td colspan="6" style="text-align:center; padding: 32px; color: var(--ink-mute);">Aucun article<?= $migration_pending ? '' : ' — cliquez « Nouvel article » pour commencer' ?>.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </form>
</div>

<script>
(function () {
  const form     = document.getElementById('bulkForm');
  const bar      = document.getElementById('bulkBar');
  const countEl  = document.getElementById('bulkCount');
  const checkAll = document.getElementById('checkAll');
  const rows     = () => form.querySelectorAll('.row-check');
  const checked  = () => form.querySelectorAll('.row-check:checked');

  function refresh() {
    const n = checked().length;
    countEl.textContent = n;
    bar.hidden = n === 0;
    const total = rows().length;
    checkAll.checked = n > 0 && n === total;
    checkAll.indeterminate = n > 0 && n < total;
  }

  checkAll.addEventListener('change', () => {
    rows().forEach(cb => { cb.checked = checkAll.checked; });
    refresh();
  });

  rows().forEach(cb => cb.addEventListener('change', refresh));

  document.getElementById('bulkClear').addEventListener('click', () => {
    rows().forEach(cb => { cb.checked = false; });
    refresh();
  });

  bar.querySelectorAll('button[data-confirm]').forEach(btn => {
    btn.addEventListener('click', e => {
      if (!confirm(btn.getAttribute('data-confirm'))) e.preventDefault();
    });
  });

  // Per-row delete — reuses the bulk_action=delete handler with a single id.
  document.querySelectorAll('.row-delete').forEach(btn => {
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
  });

  refresh();
})();
</script>

<?php require __DIR__ . '/_includes/footer.php'; ?>
