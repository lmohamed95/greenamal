<?php
require_once __DIR__ . '/includes/helpers.php';

$slug = trim((string) ($_GET['slug'] ?? ''));
$post = $slug ? db_one("SELECT * FROM posts WHERE slug = ? AND status = 'published'", [$slug]) : null;

if (!$post) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}

$page_title = $post['meta_title'] ?: $post['title'];
$page_desc  = $post['meta_description'] ?: $post['excerpt'];
$og_image   = $post['cover_url'];
$og_type    = 'article';

$jsonld = [[
    '@context' => 'https://schema.org',
    '@type' => 'Article',
    'headline' => $post['title'],
    'description' => $post['excerpt'],
    'image' => seo_abs($post['cover_url'] ?: ''),
    'datePublished' => date('c', strtotime($post['published_at'])),
    'author' => ['@type' => 'Organization', 'name' => SITE_NAME],
    'publisher' => ['@type' => 'Organization', 'name' => SITE_NAME, 'logo' => ['@type' => 'ImageObject', 'url' => seo_abs('/assets/img/logo.png')]],
]];

$other = db_all("SELECT slug, title, cover_url FROM posts WHERE status = 'published' AND slug <> ? ORDER BY published_at DESC LIMIT 3", [$slug]);

$body_class = 'gd-2026';
$extra_css  = ['/assets/css/home.css'];
require __DIR__ . '/includes/header.php';
?>

<article class="static-page">
  <div class="container" style="max-width:780px;">
    <div class="crumbs"><a href="/">Accueil</a><span class="sep">/</span><a href="/blog">Blog</a><span class="sep">/</span><span><?= e($post['title']) ?></span></div>

    <header style="margin: 24px 0 32px;">
      <div class="blog-card-meta"><?= date('j F Y', strtotime($post['published_at'])) ?></div>
      <h1 class="h-serif" style="font-family:var(--font-display-h); font-weight:400; letter-spacing:-0.025em; font-size: clamp(2rem, 5vw, 2.8rem); line-height: 1.1; margin: 8px 0 12px; color: var(--ink-h);"><?= e($post['title']) ?></h1>
      <p class="muted" style="font-size: 1.05rem; max-width: 56ch;"><?= e($post['excerpt']) ?></p>
    </header>

    <?php if ($post['cover_url']): ?>
      <img src="<?= e($post['cover_url']) ?>" alt="<?= e($post['title']) ?>" style="width:100%; border-radius: var(--r-xl-h); margin-bottom: 28px; box-shadow: var(--shadow-md-h);">
    <?php endif; ?>

    <div class="blog-body legal-page" style="padding: 0; max-width:none; background: transparent;">
      <?= $post['body'] /* trusted: written by admin */ ?>
    </div>
  </div>
</article>

<?php if ($other): ?>
  <section class="static-page" style="padding-top: 0;">
    <div class="container">
      <div class="h-section-head">
        <div>
          <span class="h-eyebrow">À lire aussi</span>
          <h2 class="h-serif" style="margin-top:6px;">Autres <em>articles</em>.</h2>
        </div>
        <a href="/blog" class="h-section-link">Tous les articles
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </a>
      </div>
      <div class="blog-grid">
        <?php foreach ($other as $p): ?>
          <a href="/post/<?= e($p['slug']) ?>" class="blog-card">
            <div class="blog-card-img"><img src="<?= e($p['cover_url'] ?: '/assets/img/categories/divers.jpg') ?>" alt="<?= e($p['title']) ?>" loading="lazy"></div>
            <div class="blog-card-body"><h3 class="blog-card-title"><?= e($p['title']) ?></h3></div>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
