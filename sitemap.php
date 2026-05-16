<?php
/**
 * Dynamic sitemap.
 *
 * Output: application/xml
 * On Apache, you can rewrite /sitemap.xml to /sitemap.php in .htaccess.
 * Cached for 1 hour to reduce DB load on crawler hits.
 */

require_once __DIR__ . '/includes/helpers.php';

header('Content-Type: application/xml; charset=utf-8');
header('Cache-Control: public, max-age=3600');

$base = seo_base();
$now  = date('c');

$urls = [];

// Static pages
$urls[] = ['loc' => $base . '/',                'lastmod' => $now, 'changefreq' => 'weekly',  'priority' => '1.0'];
$urls[] = ['loc' => $base . '/categories',      'lastmod' => $now, 'changefreq' => 'weekly',  'priority' => '0.9'];
$urls[] = ['loc' => $base . '/boutique',        'lastmod' => $now, 'changefreq' => 'daily',   'priority' => '0.9'];
$urls[] = ['loc' => $base . '/notre-histoire',  'lastmod' => $now, 'changefreq' => 'monthly', 'priority' => '0.5'];
$urls[] = ['loc' => $base . '/contact',         'lastmod' => $now, 'changefreq' => 'monthly', 'priority' => '0.4'];

// Categories — use the pretty /c/<slug> URL for cleaner SEO
foreach (db_all("SELECT slug, created_at FROM categories ORDER BY display_order") as $cat) {
    $urls[] = [
        'loc'        => $base . '/c/' . rawurlencode($cat['slug']),
        'lastmod'    => date('c', strtotime($cat['created_at'])),
        'changefreq' => 'weekly',
        'priority'   => '0.8',
    ];
}

// Active products — use the pretty /p/<slug> URL
foreach (db_all("SELECT slug, updated_at, image_main FROM products WHERE status='active' ORDER BY updated_at DESC") as $p) {
    $urls[] = [
        'loc'        => $base . '/p/' . rawurlencode($p['slug']),
        'lastmod'    => date('c', strtotime($p['updated_at'])),
        'changefreq' => 'weekly',
        'priority'   => '0.7',
        'image'      => $p['image_main'] ? seo_abs($p['image_main']) : null,
    ];
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
<?php foreach ($urls as $u): ?>
  <url>
    <loc><?= htmlspecialchars($u['loc'], ENT_XML1) ?></loc>
    <lastmod><?= $u['lastmod'] ?></lastmod>
    <changefreq><?= $u['changefreq'] ?></changefreq>
    <priority><?= $u['priority'] ?></priority>
    <?php if (!empty($u['image'])): ?>
    <image:image>
      <image:loc><?= htmlspecialchars($u['image'], ENT_XML1) ?></image:loc>
    </image:image>
    <?php endif; ?>
  </url>
<?php endforeach; ?>
</urlset>
