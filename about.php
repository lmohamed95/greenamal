<?php
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/image.php';

$page_title = 'Notre histoire';
$page_desc  = 'GreenAmal est une coopérative féminine amazighe fondée en 2008 à Azrou. Découvrez notre histoire, nos valeurs et les femmes qui produisent nos produits naturels.';
$nav        = 'about';
$body_class = 'gd-2026';
$extra_css  = ['/assets/css/home.css'];
$jsonld     = [seo_org_jsonld()];

require __DIR__ . '/includes/header.php';
?>

<section class="ab-hero">
  <div class="container">
    <span class="h-eyebrow">Notre histoire</span>
    <h1>Quand la tradition <em>rencontre</em> l'audace.</h1>
    <p>Au cœur du Moyen Atlas, à Azrou, une coopérative féminine amazighe cultive un héritage millénaire. Plus qu'une marque, GreenAmal est un projet de société.</p>
  </div>
  <svg class="ab-leaves-pattern" viewBox="0 0 240 180" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <path d="M118 168 C 118 100, 150 35, 230 12 C 238 78, 210 138, 152 162 C 138 168, 126 169, 118 168 Z" fill="#2F8C3A"/>
    <path d="M118 168 C 118 100, 86 35, 6 12 C -2 78, 26 138, 84 162 C 98 168, 110 169, 118 168 Z" fill="#2F8C3A"/>
  </svg>
</section>

<section class="ab-story">
  <div class="container">
    <div class="ab-story-grid">
      <div class="ab-img">
        <?= picture_tag('https://images.unsplash.com/photo-1604908176997-125f25cc6f3d?w=900&q=80', 'Coopérative Al Amal · Azrou', [
            'lazy'   => true,
            'width'  => 900,
            'height' => 1125,
        ]) ?>
        <div class="stat"><div class="n">2008</div><div class="l">année de fondation</div></div>
      </div>
      <div>
        <span class="h-eyebrow">Notre genèse</span>
        <h2 class="h-serif" style="margin-top:8px;">Un rêve né dans les <em>montagnes</em>.</h2>
        <p style="color:var(--ink-2h); margin-top:14px;">En 2008, douze femmes amazighes se réunissent autour d'une idée simple : transformer le savoir-faire ancestral de leurs aïeules en une activité économique viable, qui leur donne autonomie et dignité.</p>
        <p style="color:var(--ink-2h); margin-top:12px;">De la cueillette des plantes médicinales à la distillation des huiles essentielles, en passant par le roulage manuel du couscous, chaque geste perpétue une tradition tout en construisant un avenir.</p>
        <p style="color:var(--ink-2h); margin-top:12px;">Aujourd'hui, la coopérative Al Amal, qui signifie <i>« l'espoir »</i> en arabe, emploie plus de 45 femmes et exporte vers le monde entier.</p>
      </div>
    </div>
  </div>
</section>

<section class="ab-values">
  <div class="container">
    <div class="center">
      <span class="h-eyebrow">Nos valeurs</span>
      <h2 class="h-serif" style="margin-top:8px;">Trois engagements, <em>mille promesses</em>.</h2>
    </div>
    <div class="ab-values-grid">
      <div class="ab-value">
        <span class="ico"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M3 11 12 4l9 7"/><path d="M5 10v9h14v-9"/></svg></span>
        <h3>Authenticité</h3>
        <p>Pas de raccourci, pas de compromis. Chaque produit suit le rythme de la nature et le geste des artisanes.</p>
      </div>
      <div class="ab-value">
        <span class="ico"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="12" r="9"/><path d="M9 10s1 1 3 1 3-1 3-1M9 15s1 1 3 1 3-1 3-1"/></svg></span>
        <h3>Empowerment féminin</h3>
        <p>Rémunération juste, formation continue, garde d'enfants sur place. Soutenir des familles entières.</p>
      </div>
      <div class="ab-value">
        <span class="ico"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M3 12c4-7 14-7 18 0M3 12c4 7 14 7 18 0"/><circle cx="12" cy="12" r="2"/></svg></span>
        <h3>Écologie &amp; terroir</h3>
        <p>Cueillette raisonnée, agriculture sans pesticides, packaging recyclable. Préserver l'Atlas pour demain.</p>
      </div>
    </div>
  </div>
</section>

<section class="ab-timeline">
  <div class="container">
    <div class="center">
      <span class="h-eyebrow">Notre parcours</span>
      <h2 class="h-serif" style="margin-top:8px;">Quinze ans, <em>étape par étape</em>.</h2>
    </div>
    <div class="timeline">
      <div class="tl-item"><div class="tl-year">2008</div><div class="tl-content"><h4>Naissance de la coopérative</h4><p>12 femmes amazighes fondent Al Amal à Azrou avec le soutien d'une ONG locale.</p></div></div>
      <div class="tl-item"><div class="tl-year">2012</div><div class="tl-content"><h4>Certification ONSSA</h4><p>Premier audit qualité réussi, l'ensemble du process est validé par les autorités sanitaires.</p></div></div>
      <div class="tl-item"><div class="tl-year">2016</div><div class="tl-content"><h4>Première unité de distillation</h4><p>Construction d'un atelier de distillation à la vapeur, financé par les bénéfices de la coopérative.</p></div></div>
      <div class="tl-item"><div class="tl-year">2020</div><div class="tl-content"><h4>Le digital, à notre tour</h4><p>Lancement de greenamal.com pour vendre en direct, sans intermédiaire. 45 femmes employées.</p></div></div>
      <div class="tl-item"><div class="tl-year">2026</div><div class="tl-content"><h4>Une nouvelle ère</h4><p>Refonte complète de notre image et de notre site, pour porter encore plus loin la voix des productrices.</p></div></div>
    </div>
  </div>
</section>

<section class="ab-cta">
  <div class="container">
    <span class="h-eyebrow">Découvrez la suite</span>
    <h2 class="h-serif">Goûtez à un Maroc <em>authentique</em>.</h2>
    <p>Des produits qui racontent une histoire. Une histoire que vous pouvez écrire avec nous.</p>
    <a href="/boutique" class="h-btn h-btn-primary h-btn-lg">
      Explorer la boutique
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
    </a>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
