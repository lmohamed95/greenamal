<?php
require_once __DIR__ . '/includes/helpers.php';

try {
    $posts = db_all("SELECT slug, title, excerpt, cover_url, published_at FROM posts WHERE status = 'published' ORDER BY published_at DESC LIMIT 30");
} catch (PDOException $e) {
    // posts table not yet migrated on this environment — fall through to the
    // empty-state UI instead of 500ing.
    $posts = [];
}

$page_title = 'Blog GreenAmal';
$page_desc  = 'Conseils, traditions et coulisses de la coopérative Al Amal · produits naturels du Maroc.';
$body_class = 'gd-2026';
$extra_css  = ['/assets/css/home.css'];
require __DIR__ . '/includes/header.php';
?>

<section class="static-page">
  <div class="container">
    <div class="crumbs"><a href="/">Accueil</a><span class="sep">/</span><span>Blog</span></div>

    <div class="static-head">
      <span class="h-eyebrow">Le journal</span>
      <h1 class="h-serif">Récits &amp; <em>conseils</em>.</h1>
      <p>Conseils, recettes et histoires de la coopérative.</p>
    </div>

    <?php if (!$posts): ?>
      <div class="cart-empty">
        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/></svg>
        <h2 class="h-serif">Bientôt nos premiers articles</h2>
        <p>Revenez vite, ou inscrivez-vous à la newsletter pour être notifié·e.</p>
      </div>
    <?php else: ?>
      <div class="blog-grid">
        <?php foreach ($posts as $p): ?>
          <a href="/post/<?= e($p['slug']) ?>" class="blog-card">
            <div class="blog-card-img">
              <img src="<?= e($p['cover_url'] ?: '/assets/img/categories/divers.jpg') ?>" alt="<?= e($p['title']) ?>" loading="lazy">
            </div>
            <div class="blog-card-body">
              <div class="blog-card-meta"><?= date('j F Y', strtotime($p['published_at'])) ?></div>
              <h2 class="blog-card-title"><?= e($p['title']) ?></h2>
              <p class="blog-card-excerpt"><?= e($p['excerpt']) ?></p>
              <span class="blog-card-cta">Lire l'article
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
              </span>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
