<?php
require_once __DIR__ . '/includes/helpers.php';
$page_title = 'Politique de confidentialité';
$page_desc  = 'Comment GreenAmal collecte, utilise et protège vos données personnelles. Conforme à la loi marocaine 09-08.';
$body_class = 'gd-2026';
$extra_css  = ['/assets/css/home.css'];
require __DIR__ . '/includes/header.php';
?>

<section class="container legal-page" style="padding:48px 16px;max-width:820px;">
  <h1>Politique de confidentialité</h1>
  <p class="legal-meta">Dernière mise à jour : <?= date('j F Y') ?></p>

  <p>La Coopérative Al Amal (« GreenAmal », « <em>nous</em> »), édite et exploite le site greenamal.com. Nous attachons une importance particulière au respect de votre vie privée et à la protection de vos données personnelles, en conformité avec la <strong>loi 09-08</strong> relative à la protection des personnes physiques à l'égard du traitement des données à caractère personnel.</p>

  <h2>1. Responsable du traitement</h2>
  <p>Coopérative Al Amal, Tigrigra, Province d'Ifrane, Maroc.<br>Contact : <a href="mailto:<?= e(CONTACT_EMAIL) ?>"><?= e(CONTACT_EMAIL) ?></a></p>

  <h2>2. Données collectées</h2>
  <p>Lorsque vous utilisez notre site, nous collectons :</p>
  <ul>
    <li><strong>Données d'identification</strong> : nom, prénom, email, téléphone, adresse de livraison, ville, code postal.</li>
    <li><strong>Données de commande</strong> : produits achetés, montants, dates, mode de paiement.</li>
    <li><strong>Données techniques</strong> : adresse IP, type de navigateur, pages visitées (via cookies · cf. plus bas).</li>
    <li><strong>Données de compte</strong> : si vous créez un compte client, votre mot de passe est stocké de manière hachée (jamais en clair).</li>
  </ul>

  <h2>3. Finalités du traitement</h2>
  <p>Vos données sont utilisées pour :</p>
  <ul>
    <li>Traiter et expédier vos commandes.</li>
    <li>Vous envoyer des notifications relatives à votre commande (confirmation, expédition, livraison).</li>
    <li>Gérer votre compte client et vos demandes de service.</li>
    <li>Vous envoyer notre newsletter (uniquement si vous y avez consenti · désinscription possible à tout moment).</li>
    <li>Améliorer notre site et nos produits (statistiques agrégées et anonymes).</li>
    <li>Respecter nos obligations légales et comptables.</li>
  </ul>

  <h2>4. Base légale</h2>
  <p>Le traitement de vos données repose sur :</p>
  <ul>
    <li>L'<strong>exécution du contrat</strong> de vente (commandes).</li>
    <li>Votre <strong>consentement</strong> explicite (newsletter, cookies non essentiels).</li>
    <li>Nos <strong>obligations légales</strong> (facturation, comptabilité).</li>
    <li>Notre <strong>intérêt légitime</strong> à améliorer le service (mesures d'audience anonymes).</li>
  </ul>

  <h2>5. Destinataires</h2>
  <p>Vos données ne sont jamais vendues. Elles ne sont communiquées qu'aux destinataires nécessaires à la bonne exécution du service :</p>
  <ul>
    <li>Notre transporteur partenaire (uniquement les informations nécessaires à la livraison).</li>
    <li>Notre prestataire de paiement CMI (uniquement les données de transaction, jamais le numéro de carte qui ne transite jamais par nos serveurs).</li>
    <li>Les autorités, sur réquisition légale.</li>
  </ul>

  <h2>6. Durée de conservation</h2>
  <ul>
    <li><strong>Données de commande</strong> : 10 ans (obligation comptable).</li>
    <li><strong>Compte client inactif</strong> : 3 ans après la dernière connexion, puis suppression.</li>
    <li><strong>Newsletter</strong> : jusqu'à votre désinscription.</li>
    <li><strong>Cookies</strong> : 13 mois maximum.</li>
  </ul>

  <h2>7. Vos droits</h2>
  <p>Conformément à la loi 09-08, vous disposez des droits suivants :</p>
  <ul>
    <li><strong>Accès</strong> : obtenir confirmation que vos données sont traitées et en obtenir une copie.</li>
    <li><strong>Rectification</strong> : corriger les données inexactes vous concernant.</li>
    <li><strong>Opposition</strong> : vous opposer au traitement, notamment à des fins de prospection.</li>
    <li><strong>Suppression</strong> : demander l'effacement de vos données, sous réserve de nos obligations légales.</li>
  </ul>
  <p>Pour exercer ces droits : <a href="mailto:<?= e(CONTACT_EMAIL) ?>"><?= e(CONTACT_EMAIL) ?></a>. Vous pouvez également saisir la <a href="https://www.cndp.ma" target="_blank" rel="noopener">CNDP</a> en cas de litige.</p>

  <h2>8. Sécurité</h2>
  <p>Nous mettons en œuvre des mesures techniques et organisationnelles appropriées (HTTPS, mots de passe hachés, accès limités aux données, sauvegardes chiffrées) pour protéger vos données.</p>

  <h2>9. Cookies</h2>
  <p>Notre site utilise des cookies. Pour plus de détails, consultez notre <a href="cookies">politique cookies</a>.</p>

  <h2>10. Modifications</h2>
  <p>La présente politique peut être modifiée à tout moment. La version en vigueur est celle publiée sur le site à la date de votre visite.</p>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
