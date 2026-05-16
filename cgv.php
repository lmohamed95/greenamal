<?php
require_once __DIR__ . '/includes/helpers.php';
$page_title = 'Conditions générales de vente';
$page_desc  = 'Conditions générales de vente de GreenAmal · coopérative féminine d\'Azrou. Commande, livraison, paiement, retours, garanties.';
require __DIR__ . '/includes/header.php';
?>

<section class="container legal-page" style="padding:48px 16px;max-width:820px;">
  <h1>Conditions Générales de Vente</h1>
  <p class="legal-meta">Dernière mise à jour : <?= date('j F Y') ?></p>

  <h2>1. Identification du vendeur</h2>
  <p>Le présent site <strong>greenamal.com</strong> est édité par la <strong>Coopérative Al Amal</strong>, coopérative féminine immatriculée au Maroc, dont le siège est situé à Tigrigra, Province d'Ifrane.</p>
  <ul>
    <li>Téléphone : <?= e(CONTACT_PHONE) ?></li>
    <li>Email : <?= e(CONTACT_EMAIL) ?></li>
    <li>Autorisation sanitaire : ONSSA PAC.15.13.21</li>
  </ul>

  <h2>2. Objet</h2>
  <p>Les présentes Conditions Générales de Vente (CGV) régissent les relations contractuelles entre la Coopérative Al Amal (« <em>le Vendeur</em> ») et toute personne physique ou morale (« <em>le Client</em> ») souhaitant procéder à un achat sur le site greenamal.com. Toute commande implique l'acceptation pleine et entière des présentes CGV.</p>

  <h2>3. Produits</h2>
  <p>Les produits proposés sont ceux figurant sur le site, dans la limite des stocks disponibles. Les photographies illustrant les produits sont fournies à titre indicatif et n'engagent pas le Vendeur. Les produits alimentaires sont conformes à la réglementation marocaine en vigueur ; les produits cosmétiques sont à usage externe uniquement.</p>

  <h2>4. Prix</h2>
  <p>Les prix sont indiqués en dirhams marocains (MAD), toutes taxes comprises (TTC), hors frais de livraison. La Coopérative se réserve le droit de modifier ses prix à tout moment ; les produits seront facturés sur la base des tarifs en vigueur au moment de la validation de la commande.</p>

  <h2>5. Commande</h2>
  <p>Le Client passe commande en suivant le processus en ligne, avec confirmation finale sur la page de paiement. Toute commande vaut acceptation des prix et descriptions des produits. Le Vendeur se réserve le droit d'annuler ou de refuser toute commande en cas de litige avec un Client, de paiement défectueux, ou de problème d'approvisionnement.</p>

  <h2>6. Paiement</h2>
  <p>Le paiement s'effectue selon les modes proposés au moment de la commande :</p>
  <ul>
    <li><strong>Paiement à la livraison (COD)</strong> : en espèces auprès du livreur.</li>
    <li><strong>Carte bancaire</strong> via la plateforme sécurisée CMI (3-D Secure) · disponibilité progressive.</li>
    <li><strong>Virement bancaire</strong> sur demande pour les commandes importantes.</li>
  </ul>

  <h2>7. Livraison</h2>
  <p>Les livraisons sont effectuées partout au Maroc. Les frais de livraison standards s'élèvent à <?= price(SHIPPING_FEE) ?>. La livraison est offerte à partir de <?= price(FREE_SHIPPING_THRESHOLD) ?> d'achat.</p>
  <p>Les délais indicatifs sont de 2 à 5 jours ouvrables après confirmation de la commande, hors zones rurales reculées (jusqu'à 7 jours). Le Vendeur ne pourra être tenu responsable des retards imputables au transporteur.</p>

  <h2>8. Droit de rétractation</h2>
  <p>Conformément à l'article 36 de la loi 31-08 sur la protection du consommateur, le Client dispose d'un délai de 7 jours à compter de la réception du produit pour exercer son droit de rétractation, sans avoir à justifier de motifs. Les frais de retour restent à la charge du Client.</p>
  <p>Les produits alimentaires entamés, ainsi que les produits cosmétiques ouverts ou descellés, ne sont pas remboursables pour des raisons d'hygiène.</p>
  <p>Voir détails sur la <a href="retours">Politique de retour</a>.</p>

  <h2>9. Garanties</h2>
  <p>Tous les produits sont garantis conformes à leur description et à leur usage. En cas de produit défectueux ou non conforme, le Client peut demander un remplacement ou un remboursement dans un délai de 14 jours après réception, photos à l'appui.</p>

  <h2>10. Données personnelles</h2>
  <p>Les données personnelles collectées sont traitées conformément à la loi 09-08 et à notre <a href="confidentialite">politique de confidentialité</a>.</p>

  <h2>11. Propriété intellectuelle</h2>
  <p>Tous les contenus du site (textes, images, logo, graphismes) sont la propriété exclusive de la Coopérative Al Amal. Toute reproduction, même partielle, est interdite sans accord écrit préalable.</p>

  <h2>12. Litiges</h2>
  <p>Les présentes CGV sont soumises au droit marocain. Tout litige sera tranché par les tribunaux compétents d'Ifrane à défaut de règlement amiable.</p>

  <h2>13. Contact</h2>
  <p>Pour toute question relative aux présentes CGV : <a href="mailto:<?= e(CONTACT_EMAIL) ?>"><?= e(CONTACT_EMAIL) ?></a></p>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
