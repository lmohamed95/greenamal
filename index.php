<?php
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/image.php';

$page_title = 'Produits naturels du Maroc';
$page_desc  = 'Huiles essentielles, plantes aromatiques, cosmétiques bio et couscous artisanal. 100% naturels, certifiés ONSSA, issus de la coopérative féminine d\'Azrou.';
$nav        = 'home';
$og_image   = '/assets/img/categories/huiles-essentielles.jpg';
$jsonld     = [seo_org_jsonld(), seo_website_jsonld()];
$body_class = 'gd-2026';
$extra_css  = ['/assets/css/home.css'];

$categories = db_all("SELECT * FROM categories ORDER BY display_order ASC");

$best = db_all("
  SELECT p.*, c.slug AS category_slug, c.name AS category_name
  FROM products p LEFT JOIN categories c ON c.id = p.category_id
  WHERE p.status='active' AND p.is_featured=1
  ORDER BY p.sales_count DESC, p.rating_avg DESC
  LIMIT 4
");
if (count($best) < 4) {
    $best = db_all("
      SELECT p.*, c.slug AS category_slug, c.name AS category_name
      FROM products p LEFT JOIN categories c ON c.id = p.category_id
      WHERE p.status='active'
      ORDER BY p.sales_count DESC, p.rating_avg DESC
      LIMIT 4
    ");
}

$newest = db_all("
  SELECT p.*, c.slug AS category_slug, c.name AS category_name
  FROM products p LEFT JOIN categories c ON c.id = p.category_id
  WHERE p.status='active'
  ORDER BY p.created_at DESC, p.id DESC
  LIMIT 4
");

$hero_image  = cust('hero_image_url',     '/assets/img/categories/huiles-essentielles.jpg');
$story_image = cust('home_story_image',   'https://images.unsplash.com/photo-1604908176997-125f25cc6f3d?w=800&q=80');

$CAT_THEME = [
    'huiles-essentielles' => ['c1' => '#EBE3D0', 'c2' => '#D9CDA8', 'icon' => '🌿', 'short' => 'Huiles essentielles'],
    'huiles-vegetales'    => ['c1' => '#F5E4C2', 'c2' => '#E5C779', 'icon' => '🫒', 'short' => 'Huiles végétales'],
    'eau-florale'         => ['c1' => '#F4D9CE', 'c2' => '#E8B89D', 'icon' => '🌸', 'short' => 'Hydrolats'],
    'pam'                 => ['c1' => '#E4D5BC', 'c2' => '#CFB58A', 'icon' => '🪴', 'short' => 'Plantes'],
    'couscous'            => ['c1' => '#F0E1B7', 'c2' => '#E0C383', 'icon' => '🌾', 'short' => 'Couscous'],
    'farine'              => ['c1' => '#EEDFB9', 'c2' => '#D8B976', 'icon' => '🌾', 'short' => 'Farines'],
    'poudres'             => ['c1' => '#E3D2B0', 'c2' => '#C9AC75', 'icon' => '🧂', 'short' => 'Poudres'],
    'savons'              => ['c1' => '#E6D9C3', 'c2' => '#C4A877', 'icon' => '🧼', 'short' => 'Savons'],
    'packs'               => ['c1' => '#F4D9CE', 'c2' => '#D9A98F', 'icon' => '🎁', 'short' => 'Coffrets'],
    'divers'              => ['c1' => '#E2D4B6', 'c2' => '#BBA374', 'icon' => '✨', 'short' => 'Divers'],
];

require_once __DIR__ . '/includes/product_card.php';

require __DIR__ . '/includes/header.php';
?>

<!-- ===== HERO ===== -->
<section class="hero">
  <div class="container">
    <div class="hero-grid">
      <div class="hero-text">
        <span class="h-eyebrow"><?= e(cust('home_hero_eyebrow', 'Coopérative féminine · Azrou, Maroc')) ?></span>
        <h1><?= cust_html('home_hero_title', 'Le Maroc {accent}authentique{/accent},{br}en bouteille.') ?></h1>
        <p class="hero-lede"><?= e(cust('home_hero_lede', "Des huiles essentielles distillées à la main, des plantes cueillies dans l'Atlas, du couscous roulé selon la tradition. 100 % naturels, certifiés ONSSA, livrés chez vous.")) ?></p>
        <div class="hero-cta">
          <a href="<?= e(cust('home_hero_cta_primary_url', '/boutique')) ?>" class="h-btn h-btn-primary h-btn-lg">
            <?= e(cust('home_hero_cta_primary_label', 'Découvrir la boutique')) ?>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          </a>
          <a href="<?= e(cust('home_hero_cta_secondary_url', '/notre-histoire')) ?>" class="h-btn h-btn-ghost h-btn-lg"><?= e(cust('home_hero_cta_secondary_label', 'Notre histoire')) ?></a>
        </div>
        <div class="hero-trust">
          <span class="hero-trust-item">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="m5 12 5 5L20 7"/></svg>
            <?= e(cust('home_hero_trust_1', 'Certifié ONSSA')) ?>
          </span>
          <span class="hero-trust-item">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
            <?= e(cust('home_hero_trust_2', 'Livraison 24-48h')) ?>
          </span>
          <span class="hero-trust-item">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="3" y="6" width="18" height="13" rx="2"/><path d="M3 10h18"/></svg>
            <?= e(cust('home_hero_trust_3', 'Paiement à la livraison')) ?>
          </span>
        </div>
      </div>

      <div class="hero-visual">
        <svg class="hero-leaves-bg" viewBox="0 0 240 180" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
          <path d="M118 168 C 118 100, 150 35, 230 12 C 238 78, 210 138, 152 162 C 138 168, 126 169, 118 168 Z" fill="#2F8C3A"/>
          <path d="M118 168 C 118 100, 86 35, 6 12 C -2 78, 26 138, 84 162 C 98 168, 110 169, 118 168 Z" fill="#2F8C3A"/>
        </svg>
        <div class="hero-image">
          <?= picture_tag($hero_image, 'Huiles essentielles GreenAmal · produits naturels du Maroc', [
              'lazy'          => false,
              'fetchpriority' => 'high',
              'sizes'         => '(max-width: 900px) 100vw, 540px',
              'width'         => 1600,
              'height'        => 2000,
          ]) ?>
        </div>
        <div class="hero-card hero-card-rating">
          <div class="hero-card-icon">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 21s-7-4.5-7-10a4 4 0 0 1 7-2.6A4 4 0 0 1 19 11c0 5.5-7 10-7 10Z"/></svg>
          </div>
          <div>
            <div class="rating-num"><?= e(cust('home_hero_card_rating_num', '4.9')) ?><span class="sub-frac"> <?= e(cust('home_hero_card_rating_frac', '/ 5')) ?></span></div>
            <div class="rating-sub"><?= e(cust('home_hero_card_rating_sub', '+1 200 AVIS CLIENTS')) ?></div>
          </div>
        </div>
        <div class="hero-card hero-card-years">
          <div class="hero-card-icon">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 11 12 4l9 7"/><path d="M5 10v9h14v-9"/></svg>
          </div>
          <div>
            <div class="years-num"><?= e(cust('home_hero_card_years_num', '+15 ans')) ?></div>
            <div class="years-sub"><?= e(cust('home_hero_card_years_sub', 'de savoir-faire')) ?></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ===== TRUST STRIP ===== -->
<section class="trust-strip">
  <div class="container">
    <div class="trust-strip-inner">
      <?php $trust_icons = [
        1 => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M12 22s7-7 7-12a7 7 0 1 0-14 0c0 5 7 12 7 12Z"/><circle cx="12" cy="10" r="2.5"/></svg>',
        2 => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><rect x="1" y="5" width="15" height="13" rx="2"/><path d="M16 9h4l3 4v5h-7"/><circle cx="6" cy="20" r="2"/><circle cx="19" cy="20" r="2"/></svg>',
        3 => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V8a5 5 0 0 1 10 0v3"/></svg>',
        4 => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M3 12a9 9 0 1 0 9-9"/><path d="M3 4v8h8"/></svg>',
      ];
      $trust_defaults = [
        1 => ['Origine Atlas', 'Cueillette artisanale'],
        2 => ['Livraison 24-48h', 'Partout au Maroc'],
        3 => ['COD disponible', 'Paiement à la livraison'],
        4 => ['Satisfait ou remboursé', "14 jours pour changer d'avis"],
      ];
      foreach ([1,2,3,4] as $i): ?>
        <div class="trust-item">
          <span class="trust-icon"><?= $trust_icons[$i] ?></span>
          <div class="trust-text">
            <div class="t1"><?= e(cust("home_trust_{$i}_title", $trust_defaults[$i][0])) ?></div>
            <div class="t2"><?= e(cust("home_trust_{$i}_subtitle", $trust_defaults[$i][1])) ?></div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ===== BEST-SELLERS ===== -->
<section class="h-bestsellers">
  <div class="container">
    <div class="h-section-head">
      <div>
        <span class="h-eyebrow"><?= e(cust('home_best_eyebrow', '★ Best-sellers')) ?></span>
        <h2 class="h-serif" style="margin-top:6px;"><?= cust_html('home_best_title', 'Les {accent}coups de cœur{/accent} de la tribu.') ?></h2>
        <p class="subtitle"><?= e(cust('home_best_subtitle', "Les produits que nos client·e·s commandent encore et encore.")) ?></p>
      </div>
      <a href="/boutique?sort=best" class="h-section-link"><?= e(cust('home_best_link_label', 'Voir tous les best-sellers')) ?>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
      </a>
    </div>
    <div class="product-row">
      <?php foreach ($best as $p) echo home_product_card($p); ?>
    </div>
  </div>
</section>

<!-- ===== CATEGORIES TILES ===== -->
<section class="h-cat-section">
  <div class="container">
    <div class="h-section-head">
      <div>
        <span class="h-eyebrow"><?= e(cust('home_cat_eyebrow', 'Nos univers')) ?></span>
        <h2 class="h-serif" style="margin-top:6px;"><?= cust_html('home_cat_title', 'Explorez nos {accent}catégories{/accent}.') ?></h2>
        <p class="subtitle"><?= e(cust('home_cat_subtitle', "Dix familles de produits, une seule promesse : la nature à l'état pur.")) ?></p>
      </div>
      <a href="/categories" class="h-section-link"><?= e(cust('home_cat_link_label', 'Tout voir')) ?>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
      </a>
    </div>
    <div class="cat-tiles">
      <?php foreach ($categories as $cat):
        $theme = $CAT_THEME[$cat['slug']] ?? ['c1' => '#F0E6D0', 'c2' => '#D8C9A4', 'icon' => '🌿'];
        $count = (int) db_value("SELECT COUNT(*) FROM products WHERE category_id = ? AND status='active'", [$cat['id']]);
      ?>
        <a href="/boutique?cat=<?= e($cat['slug']) ?>" class="cat-tile" style="--c1:<?= e($theme['c1']) ?>;--c2:<?= e($theme['c2']) ?>;">
          <?php if (!empty($cat['image_url'])): ?>
            <div class="cat-tile-img">
              <?= picture_tag($cat['image_url'], $cat['name'], [
                  'lazy'   => true,
                  'sizes'  => '(max-width: 600px) 50vw, (max-width: 900px) 33vw, 20vw',
                  'width'  => 600,
                  'height' => 600,
              ]) ?>
            </div>
          <?php endif; ?>
          <span class="cat-tile-icon"><?= $theme['icon'] ?></span>
          <div class="cat-tile-foot">
            <div>
              <div class="cat-tile-count"><?= $count ?> produits</div>
              <div class="cat-tile-name">
                <span class="cat-tile-name-long"><?= e($cat['name']) ?></span>
                <span class="cat-tile-name-short"><?= e($theme['short'] ?? $cat['name']) ?></span>
              </div>
            </div>
            <span class="cat-tile-arrow">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </span>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ===== NOUVEAUTÉS ===== -->
<section class="h-newrow">
  <div class="container">
    <div class="h-section-head">
      <div>
        <span class="h-eyebrow ochre"><?= e(cust('home_new_eyebrow', 'Nouveautés')) ?></span>
        <h2 class="h-serif" style="margin-top:6px;"><?= cust_html('home_new_title', 'Tout juste {ochre}arrivé{/ochre}.') ?></h2>
      </div>
      <a href="/boutique?sort=new" class="h-section-link"><?= e(cust('home_new_link_label', 'Tout voir')) ?>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
      </a>
    </div>
    <div class="product-row">
      <?php foreach ($newest as $p) echo home_product_card($p); ?>
    </div>
  </div>
</section>

<!-- ===== STORY ===== -->
<section class="h-story">
  <div class="container">
    <div class="story-grid">
      <div class="story-visual">
        <?= picture_tag($story_image, 'Coopérative féminine d\'Azrou · femmes amazighes au travail', [
            'lazy'   => true,
            'width'  => 800,
            'height' => 1000,
        ]) ?>
        <div class="story-stat">
          <div class="num"><?= e(cust('home_story_stat_num', '+45')) ?></div>
          <div class="lbl"><?= e(cust('home_story_stat_label', 'femmes accompagnées')) ?></div>
        </div>
      </div>
      <div>
        <span class="h-eyebrow"><?= e(cust('home_story_eyebrow', 'Notre histoire')) ?></span>
        <h2 class="h-serif" style="margin-top:8px;"><?= cust_html('home_story_title', 'Une coopérative, des mains, un {accent}héritage{/accent}.') ?></h2>
        <p style="color: var(--ink-2h); margin-top:14px; max-width:48ch;"><?= e(cust('home_story_p1', "À Azrou, au cœur du Moyen Atlas, des femmes amazighes perpétuent un savoir-faire transmis de mères en filles. Cueillette à l'aube, distillation au feu de bois, mouture à la pierre.")) ?></p>
        <p style="color: var(--ink-2h); margin-top:10px; max-width:48ch;"><?= e(cust('home_story_p2', "Chaque produit GreenAmal raconte cette histoire, celle d'un Maroc rural, debout, fier, et résolument tourné vers l'avenir.")) ?></p>
        <div class="h-values">
          <?php $value_icons = [
            1 => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="m5 12 5 5L20 7"/></svg>',
            2 => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 2 4 6v6c0 5 3.5 9 8 10 4.5-1 8-5 8-10V6l-8-4Z"/></svg>',
            3 => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="9"/><path d="M12 2v20M2 12h20"/></svg>',
            4 => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 8a4 4 0 0 0-4 4M12 8a4 4 0 0 1 4 4M12 8V3M5 21h14"/></svg>',
          ];
          $value_def = [
            1 => ['100% local', 'Cultivé et transformé au Maroc.'],
            2 => ['Certifié ONSSA', 'Qualité contrôlée à chaque lot.'],
            3 => ['Commerce équitable', 'Rémunération juste des productrices.'],
            4 => ['Sans intermédiaire', 'Du cultivateur à votre porte.'],
          ];
          foreach ([1,2,3,4] as $i): ?>
            <div class="h-value">
              <span class="h-value-icon"><?= $value_icons[$i] ?></span>
              <div>
                <h4><?= e(cust("home_story_value_{$i}_title", $value_def[$i][0])) ?></h4>
                <p><?= e(cust("home_story_value_{$i}_text", $value_def[$i][1])) ?></p>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
        <a href="<?= e(cust('home_story_cta_url', '/notre-histoire')) ?>" class="h-btn h-btn-ghost" style="margin-top: 24px;"><?= e(cust('home_story_cta_label', 'En savoir plus')) ?>
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </a>
      </div>
    </div>
  </div>
</section>

<!-- ===== REVIEWS ===== -->
<section class="h-reviews">
  <div class="container">
    <div class="h-section-head">
      <div>
        <span class="h-eyebrow"><?= e(cust('home_review_eyebrow', '★★★★★ · 1 247 avis')) ?></span>
        <h2 class="h-serif" style="margin-top:6px;"><?= cust_html('home_review_title', "Ce qu'on en pense.") ?></h2>
      </div>
      <a href="/boutique" class="h-section-link"><?= e(cust('home_review_link_label', 'Découvrir les produits')) ?>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
      </a>
    </div>
    <div class="review-grid">
      <?php $rev_def = [
        1 => ["L'huile d'argan est juste extraordinaire. Goût intense, parfum incomparable. On sent qu'elle a été faite avec soin. Je ne reviendrai jamais en arrière.", 'Salma K.', 'Casablanca · vérifié'],
        2 => ["Le couscous d'orge me rappelle celui de ma grand-mère. Texture parfaite, goût authentique. Et la livraison était au top, 36h pour Rabat.", 'Younes A.', 'Rabat · vérifié'],
        3 => ["Les huiles essentielles sont d'une pureté rare. Mon kiné en utilise et m'a recommandé GreenAmal. Service WhatsApp ultra-réactif aussi.", 'Fatima B.', 'Marrakech · vérifié'],
      ];
      foreach ([1,2,3] as $i):
        $name = cust("home_review_{$i}_name", $rev_def[$i][1]);
        $av_initials = strtoupper(substr(preg_replace('/[^A-Za-zÀ-ÿ]/u', '', $name), 0, 2));
      ?>
        <div class="h-review">
          <span class="stars-glyph">★★★★★</span>
          <p><?= e(cust("home_review_{$i}_text", $rev_def[$i][0])) ?></p>
          <div class="who"><div class="avatar"><?= e($av_initials) ?></div><div><div class="who-name"><?= e($name) ?></div><div class="who-meta"><?= e(cust("home_review_{$i}_meta", $rev_def[$i][2])) ?></div></div></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ===== NEWSLETTER ===== -->
<section class="h-newsletter">
  <div class="container">
    <div class="h-newsletter-grid">
      <div>
        <span class="h-eyebrow"><?= e(cust('home_nl_eyebrow', '−25% offerts')) ?></span>
        <h2 class="h-serif" style="margin-top:8px;"><?= cust_html('home_nl_title', 'Rejoignez la tribu {ochre}GreenAmal{/ochre}.') ?></h2>
        <p><?= cust_html('home_nl_text', 'Recevez le code {code}first25{/code}, des recettes amazighes inédites et nos offres en avant-première.') ?></p>
      </div>
      <form onsubmit="event.preventDefault(); this.querySelector('button').textContent='Merci ✓';">
        <input type="email" required placeholder="Votre adresse email…">
        <button type="submit" class="h-btn h-btn-primary"><?= e(cust('home_nl_btn', "S'inscrire")) ?></button>
      </form>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
