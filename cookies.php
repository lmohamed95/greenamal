<?php
require_once __DIR__ . '/includes/helpers.php';
$page_title = 'Politique cookies';
$page_desc  = 'Quels cookies utilisons-nous, à quoi servent-ils, comment les contrôler.';
require __DIR__ . '/includes/header.php';
?>

<section class="container legal-page" style="padding:48px 16px;max-width:820px;">
  <h1>Politique cookies</h1>
  <p class="legal-meta">Dernière mise à jour : <?= date('j F Y') ?></p>

  <h2>Qu'est-ce qu'un cookie ?</h2>
  <p>Un cookie est un petit fichier texte déposé sur votre appareil (ordinateur, smartphone, tablette) lors de votre visite sur notre site. Il permet de mémoriser vos préférences, de maintenir votre session, ou de mesurer l'audience.</p>

  <h2>Cookies que nous utilisons</h2>

  <h3>1. Cookies strictement nécessaires (toujours actifs)</h3>
  <p>Sans eux, le site ne peut pas fonctionner correctement. Ils ne nécessitent pas votre consentement.</p>
  <ul>
    <li><code>PHPSESSID</code> : maintien de votre session (panier, connexion). Durée : session.</li>
    <li><code>ga_cookie_consent</code> : mémorisation de votre choix sur ce bandeau. Durée : 12 mois.</li>
  </ul>

  <h3>2. Cookies de mesure d'audience (avec consentement)</h3>
  <p>Ils nous aident à améliorer le site en nous donnant des statistiques anonymisées de fréquentation.</p>
  <ul>
    <li>Google Analytics (ou Plausible) : pages visitées, durée, parcours. Durée : 13 mois maximum.</li>
  </ul>

  <h3>3. Cookies tiers (avec consentement)</h3>
  <p>Provenant de services tiers intégrés au site (vidéos, partages sociaux). Régis par les politiques cookies de chaque tiers.</p>

  <h2>Comment contrôler les cookies ?</h2>
  <p>Vous pouvez à tout moment :</p>
  <ul>
    <li>Modifier votre choix via le bouton <strong>« Cookies »</strong> en bas de page.</li>
    <li>Bloquer ou supprimer les cookies dans les paramètres de votre navigateur (Chrome, Firefox, Safari…).</li>
  </ul>
  <p>Bloquer tous les cookies peut affecter le fonctionnement du panier et de votre compte.</p>

  <h2>Durée de conservation</h2>
  <p>13 mois maximum. Au-delà, votre consentement vous sera de nouveau demandé.</p>

  <h2>Contact</h2>
  <p>Pour toute question : <a href="mailto:<?= e(CONTACT_EMAIL) ?>"><?= e(CONTACT_EMAIL) ?></a></p>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
