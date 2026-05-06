<?php
require_once __DIR__ . '/includes/helpers.php';

$posts = db_all("SELECT slug, title, excerpt, cover_url, published_at FROM posts WHERE status = 'published' ORDER BY published_at DESC LIMIT 30");

$page_title = 'Blog GreenAmal';
$page_desc  = 'Conseils, traditions et coulisses de la coopérative Al Amal — produits naturels du Maroc.';
require __DIR__ . '/includes/header.php';
?>

<section class="container" style="padding:32px 16px;max-width:1080px;">
  <div class="breadcrumb"><a href="/">Accueil</a><span>/</span><span>Blog</span></div>
  <h1 style="margin:24px 0 8px;">Le journal GreenAmal</h1>
  <p style="color:var(--ink-soft);margin-bottom:32px;">Conseils, recettes, et histoires de la coopérative.</p>

  <?php if (!$posts): ?>
    <div class="empty-state"><h2>Bientôt nos premiers articles</h2></div>
  <?php else: ?>
    <div class="blog-grid">
      <?php foreach ($posts as $p): ?>
        <a href="post/<?= e($p['slug']) ?>" class="blog-card">
          <div class="blog-card-img">
            <img src="<?= e($p['cover_url'] ?: '/assets/img/categories/divers.jpg') ?>" alt="<?= e($p['title']) ?>" loading="lazy">
          </div>
          <div class="blog-card-body">
            <div class="blog-card-meta"><?= date('j F Y', strtotime($p['published_at'])) ?></div>
            <h2 class="blog-card-title"><?= e($p['title']) ?></h2>
            <p class="blog-card-excerpt"><?= e($p['excerpt']) ?></p>
            <span class="blog-card-cta">Lire l'article →</span>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
