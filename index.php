<?php
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/image.php';

$page_title = 'Produits naturels du Maroc';
$page_desc  = 'Huiles essentielles, plantes aromatiques, cosmétiques bio et couscous artisanal. 100% naturels, certifiés ONSSA, issus de la coopérative féminine d\'Azrou.';
$nav        = 'home';
$og_image   = '/assets/img/categories/huiles-essentielles.jpg';
$jsonld     = [seo_org_jsonld(), seo_website_jsonld()];

$categories = db_all("SELECT * FROM categories ORDER BY display_order ASC");
$featured   = db_all("SELECT p.*, c.slug AS category_slug, c.name AS category_name FROM products p LEFT JOIN categories c ON c.id = p.category_id WHERE p.status='active' AND p.is_featured=1 ORDER BY p.sales_count DESC LIMIT 4");

$hero_image = db_value("SELECT setting_value FROM settings WHERE setting_key='hero_image_url'")
           ?: '/assets/img/categories/huiles-essentielles.jpg';

require __DIR__ . '/includes/header.php';
?>

<!-- Hero -->
<section class="hero">
  <div class="container">
    <div class="hero-grid">
      <div class="hero-text">
        <span class="eyebrow">Coopérative féminine · Azrou, Maroc</span>
        <h1>Le Maroc <em>authentique</em>, en bouteille.</h1>
        <p>Des huiles essentielles distillées à la main, des plantes cueillies dans l'Atlas, du couscous roulé selon la tradition. 100 % naturels, certifiés ONSSA, livrés chez vous.</p>
        <div class="hero-cta">
          <a href="shop.php" class="btn btn-primary btn-lg">
            Découvrir la boutique
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
          </a>
          <a href="about.php" class="btn btn-outline btn-lg">Notre histoire</a>
        </div>
        <div class="hero-trust">
          <div class="hero-trust-item">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4"/><path d="M21 12c0 4.97-4.03 9-9 9s-9-4.03-9-9 4.03-9 9-9 9 4.03 9 9z"/></svg>
            Certifié ONSSA
          </div>
          <div class="hero-trust-item">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
            Paiement à la livraison
          </div>
          <div class="hero-trust-item">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M16 8a4 4 0 01-8 0"/><circle cx="9" cy="10" r="1"/><circle cx="15" cy="10" r="1"/></svg>
            Coopérative féminine
          </div>
        </div>
      </div>
      <div class="hero-visual">
        <div class="hero-image">
          <?= picture_tag($hero_image, 'Huiles essentielles GreenAmal · produits naturels du Maroc', [
              'lazy'          => false,
              'fetchpriority' => 'high',
              'sizes'         => '(max-width: 900px) 100vw, 480px',
              'width'         => 1600,
              'height'        => 2000,
          ]) ?>
        </div>
        <div class="hero-badge hero-badge-1">
          <div class="hero-badge-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L4 9v11a1 1 0 001 1h6v-7h2v7h6a1 1 0 001-1V9z"/></svg></div>
          <div class="hero-badge-text"><strong>+15 ans</strong><span>de savoir-faire</span></div>
        </div>
        <div class="hero-badge hero-badge-2">
          <div class="hero-badge-icon terracotta"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg></div>
          <div class="hero-badge-text"><strong>4.9 / 5</strong><span>+1 200 avis clients</span></div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- USP strip -->
<section class="usp-strip">
  <div class="container">
    <div class="usp-grid">
      <div class="usp-item">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 2L4 9v11a1 1 0 001 1h6v-7h2v7h6a1 1 0 001-1V9z"/></svg>
        <div><strong>Origine Atlas</strong><span>Cueillette artisanale</span></div>
      </div>
      <div class="usp-item">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 7l9-4 9 4M3 7v10l9 4 9-4V7M3 7l9 4 9-4M12 11v10"/></svg>
        <div><strong>Livraison 24/48h</strong><span>Partout au Maroc</span></div>
      </div>
      <div class="usp-item">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
        <div><strong>Paiement sécurisé</strong><span>COD disponible</span></div>
      </div>
      <div class="usp-item">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 12a9 9 0 1018 0 9 9 0 00-18 0z"/><path d="M9 12l2 2 4-4"/></svg>
        <div><strong>Satisfait ou remboursé</strong><span>14 jours pour changer d'avis</span></div>
      </div>
    </div>
  </div>
</section>

<!-- Categories -->
<section class="section">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow">Nos univers</span>
      <h2>Explorez nos catégories</h2>
      <p>Sept familles de produits, une seule promesse : la nature à l'état pur.</p>
    </div>
    <div class="cat-grid">
      <?php foreach ($categories as $i => $cat): ?>
        <a href="shop.php?cat=<?= e($cat['slug']) ?>" class="cat-card<?= $i === 0 ? ' cat-large' : '' ?>">
          <?= picture_tag($cat['image_url'], $cat['name'] . ' · GreenAmal', [
              'lazy'   => true,
              'sizes'  => $i === 0 ? '(max-width: 720px) 100vw, 50vw' : '(max-width: 720px) 50vw, 25vw',
              'width'  => 1600,
              'height' => 1600,
          ]) ?>
          <div class="cat-card-info">
            <h3><?= e($cat['name']) ?></h3>
            <span><?= (int) db_value("SELECT COUNT(*) FROM products WHERE category_id = ? AND status='active'", [$cat['id']]) ?> produits</span>
            <span class="arrow">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </span>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Featured products -->
<section class="section section-sand">
  <div class="container">
    <div class="section-head left">
      <div class="head-text">
        <span class="eyebrow">Best-sellers</span>
        <h2>Les coups de cœur</h2>
      </div>
      <a href="shop.php" class="head-link">Voir tous les produits →</a>
    </div>

    <div class="product-grid">
      <?php foreach ($featured as $p): ?>
        <article class="product-card" data-product-id="<?= (int) $p['id'] ?>">
          <div class="product-image">
            <?php if ($p['compare_at_price'] && $p['compare_at_price'] > $p['price']): ?>
              <div class="product-tags">
                <span class="product-tag sale">−<?= (int) round((1 - $p['price'] / $p['compare_at_price']) * 100) ?>%</span>
              </div>
            <?php endif; ?>
            <div class="product-actions-quick">
              <button class="quick-btn" aria-label="Favoris"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg></button>
            </div>
            <?= picture_tag($p['image_main'], $p['name'], [
                'lazy'   => true,
                'sizes'  => '(max-width: 720px) 50vw, 25vw',
                'width'  => 800,
                'height' => 800,
            ]) ?>
            <a href="product.php?slug=<?= e($p['slug']) ?>" class="add-to-cart-overlay" data-add-to-cart>
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/></svg>
              Ajouter au panier
            </a>
          </div>
          <div class="product-info">
            <span class="product-cat"><?= e($p['category_name']) ?></span>
            <a href="product.php?slug=<?= e($p['slug']) ?>"><h4 class="product-name"><?= e($p['name']) ?></h4></a>
            <div class="product-rating">
              <span class="stars">★★★★★</span>
              <span>(<?= (int) $p['rating_count'] ?>)</span>
            </div>
            <div class="product-price">
              <span class="price-now"><?= price($p['price']) ?></span>
              <?php if ($p['compare_at_price'] && $p['compare_at_price'] > $p['price']): ?>
                <span class="price-was"><?= price($p['compare_at_price']) ?></span>
              <?php endif; ?>
            </div>
            <a href="product.php?slug=<?= e($p['slug']) ?>" class="btn-view">
              Voir
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </a>
          </div>
        </article>
      <?php endforeach; ?>
    </div>

    <div class="text-center mt-4">
      <a href="shop.php" class="btn btn-dark">Voir toute la boutique</a>
    </div>
  </div>
</section>

<!-- Story -->
<section class="section">
  <div class="container">
    <div class="story">
      <div class="story-image">
        <?= picture_tag('https://images.unsplash.com/photo-1604908176997-125f25cc6f3d?w=800&q=80', 'Coopérative féminine d\'Azrou · femmes berbères au travail', [
            'lazy'   => true,
            'width'  => 800,
            'height' => 1000,
        ]) ?>
        <div class="story-stat"><strong>+45</strong><span>femmes accompagnées</span></div>
      </div>
      <div class="story-text">
        <span class="eyebrow">Notre histoire</span>
        <h2>Une coopérative, des mains, un héritage.</h2>
        <p>À Azrou, au cœur du Moyen Atlas, des femmes berbères perpétuent un savoir-faire transmis de mères en filles. Cueillette à l'aube, distillation au feu de bois, mouture à la pierre.</p>
        <p>Chaque produit GreenAmal raconte cette histoire · celle d'un Maroc rural, debout, fier, et résolument tourné vers l'avenir.</p>
        <div class="story-features">
          <div class="story-feature">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L4 9v11a1 1 0 001 1h6v-7h2v7h6a1 1 0 001-1V9z"/></svg>
            <div><strong>100% local</strong><span>Cultivé et transformé au Maroc</span></div>
          </div>
          <div class="story-feature">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9 12l2 2 4-4"/></svg>
            <div><strong>Certifié ONSSA</strong><span>Qualité contrôlée à chaque lot</span></div>
          </div>
          <div class="story-feature">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M16 8a4 4 0 01-8 0"/><circle cx="9" cy="10" r="1"/><circle cx="15" cy="10" r="1"/></svg>
            <div><strong>Commerce équitable</strong><span>Rémunération juste des productrices</span></div>
          </div>
          <div class="story-feature">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12c0-5 4-9 9-9s9 4 9 9-4 9-9 9-9-4-9-9z"/><path d="M3 12h18M12 3a15 15 0 010 18M12 3a15 15 0 000 18"/></svg>
            <div><strong>Sans intermédiaire</strong><span>Du cultivateur à votre porte</span></div>
          </div>
        </div>
        <a href="about.php" class="btn btn-outline">En savoir plus</a>
      </div>
    </div>
  </div>
</section>

<!-- Newsletter -->
<section class="newsletter">
  <div class="container">
    <div class="newsletter-inner">
      <div>
        <span class="eyebrow" style="color: var(--saffron);">−25% offerts</span>
        <h2>Rejoignez la tribu GreenAmal</h2>
        <p>Recevez le code <strong style="color: var(--saffron);">first25</strong>, des recettes berbères inédites et nos offres en avant-première.</p>
      </div>
      <form class="newsletter-form" onsubmit="return false;">
        <input type="email" placeholder="Votre adresse email" required>
        <button type="submit" class="btn btn-primary">S'inscrire</button>
      </form>
    </div>
  </div>
</section>

<!-- Exit-intent modal -->
<div class="modal-backdrop" id="exit-modal">
  <div class="modal">
    <button class="modal-close" aria-label="Fermer">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>
    <span class="eyebrow">Offre de bienvenue</span>
    <h3>Avant de partir, prenez <em>−25%</em></h3>
    <p>Sur votre première commande. Inscrivez-vous et recevez le code par email immédiatement.</p>
    <form class="modal-form">
      <input type="email" placeholder="Votre adresse email" required>
      <button type="submit" class="btn btn-primary btn-block btn-lg">Recevoir mon code</button>
    </form>
    <p style="font-size: 0.75rem; margin-top: 16px; margin-bottom: 0;">Pas de spam. Désinscription en un clic.</p>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
