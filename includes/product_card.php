<?php
/**
 * Shared product-card renderer for the 2026 redesign (.gd-2026 body class).
 * Accepts an array with the joined product+category fields.
 */

if (!function_exists('home_product_card')) {

require_once __DIR__ . '/image.php';

function home_product_card(array $p): string {
    $slug      = $p['slug'];
    $name      = $p['name'];
    $catName   = $p['category_name'] ?? '';
    $price     = (float) $p['price'];
    $oldPrice  = !empty($p['compare_at_price']) && $p['compare_at_price'] > $p['price']
        ? (float) $p['compare_at_price'] : null;
    $rating    = (float) ($p['rating_avg'] ?? 0);
    $reviews   = (int)   ($p['rating_count'] ?? 0);
    $isFeat    = !empty($p['is_featured']);
    $isNew     = !empty($p['created_at']) && (time() - strtotime($p['created_at'])) < 60 * 24 * 3600;
    $outOfStock = isset($p['stock']) && (int) $p['stock'] <= 0;

    $badge = null; $badgeText = '';
    if ($oldPrice)   { $badge = 'promo'; $badgeText = '−' . (int) round((1 - $price / $oldPrice) * 100) . '%'; }
    elseif ($isFeat) { $badge = 'best';  $badgeText = 'Best-seller'; }
    elseif ($isNew)  { $badge = 'new';   $badgeText = 'Nouveau'; }

    $full  = $rating > 0 ? (int) round($rating) : 5;
    $stars = str_repeat('★', $full) . str_repeat('☆', 5 - $full);

    $img = picture_tag($p['image_main'] ?? '', $name, [
        'lazy'   => true,
        'sizes'  => '(max-width: 720px) 50vw, 25vw',
        'width'  => 800,
        'height' => 800,
    ]);

    ob_start(); ?>
    <article class="h-card<?= $outOfStock ? ' is-out' : '' ?>" data-product-id="<?= (int) ($p['id'] ?? 0) ?>">
      <a href="/product?slug=<?= e($slug) ?>" class="h-card-media">
        <?= $img ?>
        <?php if ($badge): ?>
          <div class="h-card-badges">
            <span class="h-card-badge <?= e($badge) ?>"><?= e($badgeText) ?></span>
          </div>
        <?php endif; ?>
        <button type="button" class="h-card-fav" aria-label="Ajouter aux favoris" onclick="event.preventDefault();this.classList.toggle('on')">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21s-7-4.5-7-10a4 4 0 0 1 7-2.6A4 4 0 0 1 19 11c0 5.5-7 10-7 10Z"/></svg>
        </button>
        <span class="h-card-quick">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          Voir le produit
        </span>
      </a>
      <div class="h-card-body">
        <?php if ($catName): ?><div class="h-card-cat"><?= e($catName) ?></div><?php endif; ?>
        <h3 class="h-card-name"><a href="/product?slug=<?= e($slug) ?>"><?= e($name) ?></a></h3>
        <?php if ($reviews > 0): ?>
          <div class="h-card-stars">
            <span class="stars-glyph"><?= $stars ?></span>
            <span><?= number_format($rating, 1) ?></span>
            <span>(<?= $reviews ?>)</span>
          </div>
        <?php endif; ?>
        <div class="h-card-price-row">
          <div>
            <span class="h-price"><?= number_format($price, 0, ',', ' ') ?> <span class="h-unit">DH</span></span>
            <?php if ($oldPrice): ?>
              <span class="h-price-old"><?= number_format($oldPrice, 0, ',', ' ') ?> DH</span>
            <?php endif; ?>
          </div>
          <button type="button" class="h-card-add" aria-label="Ajouter au panier" data-add-to-cart data-product-id="<?= (int) ($p['id'] ?? 0) ?>"<?= $outOfStock ? ' disabled' : '' ?>>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
          </button>
        </div>
      </div>
    </article>
    <?php
    return ob_get_clean();
}

} // !function_exists
