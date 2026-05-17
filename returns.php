<?php
require_once __DIR__ . '/includes/helpers.php';
$page_title = 'Politique de retour & remboursement';
$page_desc  = 'Comment retourner un produit GreenAmal et obtenir un remboursement. Délais, conditions, démarche.';
$body_class = 'gd-2026';
$extra_css  = ['/assets/css/home.css'];
require __DIR__ . '/includes/header.php';
?>

<section class="container legal-page" style="padding:48px 16px;max-width:820px;">
  <h1>Retours & remboursements</h1>
  <p class="legal-meta">Dernière mise à jour : <?= date('j F Y') ?></p>

  <p>Nous voulons que vous soyez pleinement satisfait(e) de vos achats. En cas de problème, voici comment procéder.</p>

  <h2>1. Droit de rétractation (7 jours)</h2>
  <p>Conformément à l'article 36 de la loi 31-08, vous disposez d'un délai de <strong>7 jours</strong> à compter de la réception de votre commande pour exercer votre droit de rétractation, sans avoir à justifier de motifs.</p>

  <h2>2. Produits éligibles au retour</h2>
  <p>Sont éligibles au retour les produits :</p>
  <ul>
    <li>Non ouverts, non utilisés, dans leur emballage d'origine intact ;</li>
    <li>Avec étiquettes et scellés non endommagés ;</li>
    <li>Retournés dans le délai de 7 jours.</li>
  </ul>

  <h2>3. Produits non retournables</h2>
  <p>Pour des raisons d'hygiène et de sécurité alimentaire :</p>
  <ul>
    <li>Produits alimentaires (couscous, farines, plantes…) une fois ouverts.</li>
    <li>Produits cosmétiques (eaux florales, huiles, savons, gommages) une fois descellés.</li>
    <li>Produits soldés ou en promotion (sauf défaut avéré).</li>
  </ul>

  <h2>4. Procédure</h2>
  <ol>
    <li>Contactez-nous à <a href="mailto:<?= e(CONTACT_EMAIL) ?>"><?= e(CONTACT_EMAIL) ?></a> ou au <?= e(CONTACT_PHONE) ?> dans les 7 jours suivant la réception, en indiquant votre numéro de commande et le motif.</li>
    <li>Nous vous envoyons sous 24 h les instructions de retour.</li>
    <li>Vous renvoyez le colis (frais de retour à votre charge sauf produit défectueux).</li>
    <li>Une fois le colis reçu et inspecté, le remboursement est traité sous 7 jours ouvrables.</li>
  </ol>

  <h2>5. Produits défectueux ou non conformes</h2>
  <p>Si vous recevez un produit endommagé, périmé ou non conforme à votre commande :</p>
  <ul>
    <li>Photographiez le produit dès la réception.</li>
    <li>Contactez-nous dans les <strong>48 heures</strong> avec les photos et votre numéro de commande.</li>
    <li>Nous vous proposons un remplacement gratuit ou un remboursement intégral, frais de retour à notre charge.</li>
  </ul>

  <h2>6. Remboursement</h2>
  <p>Le remboursement est effectué selon le mode de paiement initial :</p>
  <ul>
    <li><strong>Paiement à la livraison</strong> : virement bancaire (RIB requis).</li>
    <li><strong>Carte bancaire (CMI)</strong> : recrédit sur la carte (5–10 jours ouvrables).</li>
    <li><strong>Virement</strong> : virement de retour (3–5 jours ouvrables).</li>
  </ul>

  <h2>7. Frais de livraison</h2>
  <p>Les frais de livraison initiaux sont remboursés uniquement en cas de produit défectueux ou d'erreur de notre part. Pour une rétractation classique, ils restent à la charge du Client.</p>

  <h2>8. Adresse de retour</h2>
  <p>Coopérative Al Amal · Tigrigra, Province d'Ifrane, Maroc.<br>
  <em>Merci de toujours nous contacter avant tout retour</em>, afin de coordonner la réception et de vous éviter un envoi non traité.</p>

  <h2>Nous contacter</h2>
  <p>Une question ? Nous sommes là pour vous aider :<br>
  <svg class="icon-inline" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/></svg><?= e(CONTACT_PHONE) ?> · <svg class="icon-inline" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg><a href="mailto:<?= e(CONTACT_EMAIL) ?>"><?= e(CONTACT_EMAIL) ?></a></p>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
