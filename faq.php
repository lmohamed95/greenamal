<?php
require_once __DIR__ . '/includes/helpers.php';

$page_title = 'Questions fréquentes';
$page_desc  = 'Réponses aux questions courantes sur GreenAmal : commande, livraison, paiement, retours, produits.';

$faqs = [
    'Commandes & livraison' => [
        ['Quels sont les délais de livraison ?',
         'Les colis sont expédiés sous 24-48 h ouvrables. Les délais de livraison sont de 2 à 5 jours ouvrables partout au Maroc. Les zones rurales reculées peuvent prendre jusqu\'à 7 jours.'],
        ['Combien coûte la livraison ?',
         'Les frais de livraison standards s\'élèvent à ' . price(SHIPPING_FEE) . '. La livraison est offerte à partir de ' . price(FREE_SHIPPING_THRESHOLD) . ' d\'achat.'],
        ['Livrez-vous à l\'international ?',
         'Pour le moment, nous livrons uniquement au Maroc. Les commandes internationales seront disponibles courant 2026 · inscrivez-vous à notre newsletter pour être informé(e).'],
        ['Puis-je suivre ma commande ?',
         'Vous recevrez un email de confirmation avec le numéro de suivi dès l\'expédition. Vous pouvez également retrouver toutes vos commandes dans votre <a href="account.php">espace client</a>.'],
    ],
    'Paiement' => [
        ['Quels modes de paiement acceptez-vous ?',
         'Actuellement, le <strong>paiement à la livraison (COD)</strong> en espèces est disponible pour toutes les commandes. Le paiement par carte bancaire via la plateforme sécurisée CMI sera disponible prochainement.'],
        ['Mes données de paiement sont-elles sécurisées ?',
         'Oui. Aucune information bancaire ne transite jamais par nos serveurs. Le paiement carte se fait sur la plateforme CMI sécurisée (3-D Secure).'],
    ],
    'Produits' => [
        ['Vos produits sont-ils certifiés ?',
         'Tous nos produits sont conformes à la réglementation marocaine. Notre coopérative dispose de l\'autorisation sanitaire ONSSA PAC.15.13.21. Nos cosmétiques sont fabriqués selon les bonnes pratiques de fabrication.'],
        ['Vos produits sont-ils bio ?',
         'La plupart de nos plantes et huiles sont issues de cueillettes sauvages dans le Moyen Atlas, sans pesticides ni engrais chimiques. Nous utilisons le terme « naturel » plutôt que « bio » car la certification bio formelle n\'est pas encore appliquée à toute notre gamme.'],
        ['Les produits ont-ils une date d\'expiration ?',
         'Oui. La date d\'expiration figure sur l\'étiquette de chaque produit. Les huiles essentielles ont une DLU de 36 mois, les hydrolats de 12 mois après ouverture.'],
        ['Comment conserver vos produits ?',
         'Les produits alimentaires se conservent dans un endroit sec et frais. Les huiles essentielles à l\'abri de la lumière. Les hydrolats au réfrigérateur après ouverture.'],
    ],
    'Retours & remboursements' => [
        ['Puis-je retourner un produit ?',
         'Oui, vous disposez de 7 jours pour exercer votre droit de rétractation, sous réserve que le produit soit non ouvert et dans son emballage d\'origine. Détails sur la <a href="returns.php">page retours</a>.'],
        ['Que faire si je reçois un produit endommagé ?',
         'Contactez-nous dans les 48 heures avec des photos à <a href="mailto:' . CONTACT_EMAIL . '">' . CONTACT_EMAIL . '</a>. Nous vous proposerons un remplacement ou un remboursement intégral, frais à notre charge.'],
    ],
    'Compte & sécurité' => [
        ['Dois-je créer un compte pour commander ?',
         'Non, vous pouvez commander en mode invité. Mais un compte permet de suivre vos commandes, sauvegarder vos adresses et profiter d\'offres exclusives.'],
        ['J\'ai oublié mon mot de passe',
         'Utilisez le formulaire <a href="forgot-password.php">mot de passe oublié</a>. Vous recevrez un lien de réinitialisation par email.'],
    ],
    'Coopérative' => [
        ['Qu\'est-ce que la Coopérative Al Amal ?',
         'La Coopérative féminine Al Amal est basée à Tigrigra, dans la province d\'Ifrane (Moyen Atlas). Elle regroupe une vingtaine de femmes amazighes qui transforment et commercialisent les ressources végétales locales.'],
        ['Comment soutenir la coopérative ?',
         'Acheter nos produits soutient directement les artisanes. Vous pouvez aussi nous suivre sur les réseaux sociaux ou parrainer un projet · contactez-nous pour les détails.'],
    ],
];

// Build FAQ JSON-LD
$qa_jsonld = ['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => []];
foreach ($faqs as $cat => $items) {
    foreach ($items as [$q, $a]) {
        $qa_jsonld['mainEntity'][] = [
            '@type' => 'Question',
            'name'  => $q,
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => strip_tags($a)],
        ];
    }
}
$jsonld = [$qa_jsonld];
require __DIR__ . '/includes/header.php';
?>

<section class="container" style="padding:48px 16px;max-width:880px;">
  <div class="breadcrumb"><a href="/">Accueil</a><span>/</span><span>FAQ</span></div>
  <h1 style="margin:24px 0 8px;">Questions fréquentes</h1>
  <p style="color:var(--ink-soft);margin-bottom:32px;">Tout ce qu'il faut savoir avant de commander chez GreenAmal.</p>

  <?php foreach ($faqs as $cat => $items): ?>
    <div class="faq-section">
      <h2 class="faq-cat"><?= e($cat) ?></h2>
      <?php foreach ($items as [$q, $a]): ?>
        <details class="faq-item">
          <summary><?= e($q) ?></summary>
          <div class="faq-answer"><?= $a /* trusted: hand-written */ ?></div>
        </details>
      <?php endforeach; ?>
    </div>
  <?php endforeach; ?>

  <div class="card" style="padding:24px;margin-top:36px;text-align:center;">
    <h3>Vous n'avez pas trouvé votre réponse ?</h3>
    <p style="color:var(--ink-soft);">Notre équipe est là pour vous aider.</p>
    <div style="display:inline-flex;gap:8px;margin-top:8px;">
      <a href="contact.php" class="btn btn-primary">Nous contacter</a>
      <a href="https://wa.me/<?= e(WHATSAPP_NUMBER) ?>" class="btn btn-ghost" target="_blank" rel="noopener">WhatsApp</a>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
