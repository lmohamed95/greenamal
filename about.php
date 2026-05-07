<?php
require_once __DIR__ . '/includes/helpers.php';
$page_title = 'Notre histoire';
$page_desc  = 'GreenAmal est une coopérative féminine berbère fondée en 2008 à Azrou. Découvrez notre histoire, nos valeurs et les femmes qui produisent nos produits naturels.';
$nav        = 'about';
$jsonld     = [seo_org_jsonld()];

require __DIR__ . '/includes/header.php';
?>

<section class="about-hero">
  <div class="container">
    <span class="eyebrow">Notre histoire</span>
    <h1>Quand la tradition <em style="color: var(--terracotta); font-style: italic;">rencontre</em> l'audace.</h1>
    <p>Au cœur du Moyen Atlas, à Azrou, une coopérative féminine berbère cultive un héritage millénaire. Plus qu'une marque, GreenAmal est un projet de société.</p>
  </div>
</section>

<section class="section section-sand">
  <div class="container">
    <div class="story">
      <div class="story-image">
        <img src="https://images.unsplash.com/photo-1604908176997-125f25cc6f3d?w=900&q=80" alt="Coopérative Al Amal">
        <div class="story-stat"><strong>2008</strong><span>année de fondation</span></div>
      </div>
      <div class="story-text">
        <span class="eyebrow">Notre genèse</span>
        <h2>Un rêve né dans les montagnes.</h2>
        <p>En 2008, douze femmes berbères se réunissent autour d'une idée simple : transformer le savoir-faire ancestral de leurs aïeules en une activité économique viable, qui leur donne autonomie et dignité.</p>
        <p>De la cueillette des plantes médicinales à la distillation des huiles essentielles, en passant par le roulage manuel du couscous, chaque geste perpétue une tradition tout en construisant un avenir.</p>
        <p>Aujourd'hui, la coopérative Al Amal · qui signifie <em>"l'espoir"</em> en arabe · emploie plus de 45 femmes et exporte vers le monde entier.</p>
      </div>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow">Nos valeurs</span>
      <h2>Trois engagements, mille promesses.</h2>
    </div>
    <div class="values-grid">
      <div class="value-card">
        <div class="value-icon"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 2L4 9v11a1 1 0 001 1h6v-7h2v7h6a1 1 0 001-1V9z"/></svg></div>
        <h3>Authenticité</h3>
        <p>Pas de raccourci, pas de compromis. Chaque produit suit le rythme de la nature et le geste des artisanes.</p>
      </div>
      <div class="value-card">
        <div class="value-icon"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><path d="M16 8a4 4 0 01-8 0"/><circle cx="9" cy="10" r="1"/><circle cx="15" cy="10" r="1"/></svg></div>
        <h3>Empowerment féminin</h3>
        <p>Rémunération juste, formation continue, garde d'enfants sur place. Soutenir des familles entières.</p>
      </div>
      <div class="value-card">
        <div class="value-icon"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 12c0 5-4 9-9 9s-9-4-9-9 4-9 9-9 9 4 9 9z"/></svg></div>
        <h3>Écologie & terroir</h3>
        <p>Cueillette raisonnée, agriculture sans pesticides, packaging recyclable. Préserver l'Atlas pour demain.</p>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
