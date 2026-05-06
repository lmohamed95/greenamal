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

require __DIR__ . '/includes/header.php';
?>

<article class="container" style="padding:32px 16px;max-width:760px;">
  <div class="breadcrumb"><a href="/">Accueil</a><span>/</span><a href="/blog.php">Blog</a><span>/</span><span><?= e($post['title']) ?></span></div>
  <header style="margin:24px 0;">
    <div class="blog-card-meta"><?= date('j F Y', strtotime($post['published_at'])) ?></div>
    <h1 style="font-size:2.2rem;line-height:1.15;margin:8px 0 12px;"><?= e($post['title']) ?></h1>
    <p style="color:var(--ink-soft);font-size:1.05rem;"><?= e($post['excerpt']) ?></p>
  </header>
  <?php if ($post['cover_url']): ?>
    <img src="<?= e($post['cover_url']) ?>" alt="<?= e($post['title']) ?>" style="width:100%;border-radius:14px;margin-bottom:28px;">
  <?php endif; ?>
  <div class="blog-body"><?= $post['body'] /* trusted: written by admin */ ?></div>
</article>

<?php if ($other): ?>
  <section class="container" style="padding:48px 16px;max-width:1080px;">
    <h2 style="font-size:1.4rem;margin-bottom:18px;">À lire aussi</h2>
    <div class="blog-grid">
      <?php foreach ($other as $p): ?>
        <a href="post/<?= e($p['slug']) ?>" class="blog-card">
          <div class="blog-card-img"><img src="<?= e($p['cover_url'] ?: '/assets/img/categories/divers.jpg') ?>" alt="<?= e($p['title']) ?>" loading="lazy"></div>
          <div class="blog-card-body"><h3 class="blog-card-title"><?= e($p['title']) ?></h3></div>
        </a>
      <?php endforeach; ?>
    </div>
  </section>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
