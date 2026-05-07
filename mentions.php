<?php
require_once __DIR__ . '/includes/helpers.php';
$page_title = 'Mentions légales';
$page_desc  = 'Mentions légales du site greenamal.com · éditeur, hébergeur, propriété intellectuelle.';
require __DIR__ . '/includes/header.php';
?>

<section class="container legal-page" style="padding:48px 16px;max-width:820px;">
  <h1>Mentions légales</h1>
  <p class="legal-meta">Dernière mise à jour : <?= date('j F Y') ?></p>

  <h2>Éditeur du site</h2>
  <p>
    <strong>Coopérative Al Amal</strong> (« GreenAmal »)<br>
    Coopérative féminine · Tigrigra, Province d'Ifrane, Maroc<br>
    Téléphone : <?= e(CONTACT_PHONE) ?><br>
    Email : <a href="mailto:<?= e(CONTACT_EMAIL) ?>"><?= e(CONTACT_EMAIL) ?></a><br>
    Autorisation sanitaire : ONSSA PAC.15.13.21<br>
    Identifiant fiscal : à compléter
  </p>

  <h2>Directeur de la publication</h2>
  <p>La présidente de la Coopérative Al Amal.</p>

  <h2>Hébergeur</h2>
  <p>
    Namecheap, Inc.<br>
    4600 East Washington Street, Suite 305<br>
    Phoenix, Arizona 85034, USA<br>
    Site : <a href="https://www.namecheap.com" target="_blank" rel="noopener">namecheap.com</a>
  </p>

  <h2>Propriété intellectuelle</h2>
  <p>L'ensemble du contenu de ce site (textes, images, photographies, logos, graphismes, code) est la propriété exclusive de la Coopérative Al Amal, sauf mention contraire. Toute reproduction, représentation, modification, publication ou adaptation, totale ou partielle, par quelque procédé que ce soit, est interdite sans l'autorisation écrite préalable de la Coopérative.</p>

  <h2>Responsabilité</h2>
  <p>La Coopérative Al Amal s'efforce de fournir des informations exactes et à jour, mais ne peut être tenue responsable d'éventuelles erreurs ou omissions. L'utilisation des informations et contenus disponibles sur l'ensemble du site se fait sous l'entière et seule responsabilité de l'utilisateur.</p>

  <h2>Liens externes</h2>
  <p>Le site peut contenir des liens vers d'autres sites Internet. Ces liens sont fournis à titre informatif. La Coopérative n'a aucun contrôle sur ces sites tiers et décline toute responsabilité quant à leur contenu.</p>

  <h2>Droit applicable</h2>
  <p>Les présentes mentions légales sont régies par le droit marocain. Tout litige sera de la compétence exclusive des tribunaux marocains.</p>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
