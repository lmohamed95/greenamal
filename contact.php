<?php
require_once __DIR__ . '/includes/helpers.php';

$page_title = 'Contact';
$page_desc  = 'Contactez la coopérative GreenAmal · téléphone, WhatsApp, email. Service client basé à Azrou, Maroc.';
$nav        = 'contact';
$body_class = 'gd-2026';
$extra_css  = ['/assets/css/home.css'];
$jsonld     = [
    [
        '@context'    => 'https://schema.org',
        '@type'       => 'ContactPage',
        'name'        => 'Contact · ' . SITE_NAME,
        'url'         => seo_canonical(),
        'description' => $page_desc,
    ],
    seo_org_jsonld(),
    seo_breadcrumb_jsonld([
        ['Accueil', '/'],
        ['Contact', '/contact'],
    ]),
];

$sent  = false;
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    if (!empty($_POST['website'] ?? '')) {
        $sent = true; // honeypot trap, silently succeed
    } elseif (!rate_limit('contact', 3, 600)) {
        $error = 'Trop de tentatives, merci de réessayer dans quelques minutes.';
    } else {
        $name    = trim($_POST['name'] ?? '');
        $email   = trim($_POST['email'] ?? '');
        $phone   = trim($_POST['phone'] ?? '');
        $message = trim($_POST['message'] ?? '');
        $topic   = trim($_POST['topic'] ?? 'Question produit');

        if ($name && filter_var($email, FILTER_VALIDATE_EMAIL) && $message) {
            $sent = true;
        } else {
            $error = 'Merci de remplir au moins le nom, l\'email et le message.';
        }
    }
}

require __DIR__ . '/includes/header.php';
?>

<section class="contact-hero">
  <div class="container">
    <div class="crumbs"><a href="/">Accueil</a><span class="sep">/</span><span>Contact</span></div>
    <span class="h-eyebrow">Nous contacter</span>
    <h1>Une question ? <em>Parlez-nous.</em></h1>
    <p>Notre équipe répond du lundi au samedi, 9h–18h. Le moyen le plus rapide reste WhatsApp.</p>
  </div>
</section>

<section class="contact-body">
  <div class="container">
    <div class="contact-grid">
      <!-- Left: coordinates + WA promo -->
      <div>
        <div class="contact-card">
          <h2>Coordonnées</h2>
          <p class="sub">Plusieurs façons de nous joindre.</p>
          <div class="contact-items">
            <a href="tel:<?= str_replace(' ', '', e(CONTACT_PHONE)) ?>" class="contact-item">
              <span class="ico"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7l.5 2.5a2 2 0 0 1-.6 1.9l-1.3 1.3a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 1.9-.6l2.5.5a2 2 0 0 1 1.7 2Z"/></svg></span>
              <div><div class="lbl">Téléphone</div><div class="val"><?= e(CONTACT_PHONE) ?></div></div>
            </a>
            <a href="https://wa.me/<?= e(wa_number()) ?>" class="contact-item" target="_blank" rel="noopener">
              <span class="ico" style="background:rgba(37,211,102,0.15);color:#1A8245;"><svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 3a9 9 0 0 0-7.7 13.7L3 21l4.4-1.2A9 9 0 1 0 12 3Z"/></svg></span>
              <div><div class="lbl">WhatsApp</div><div class="val"><a href="https://wa.me/<?= e(wa_number()) ?>">Chat avec nous →</a></div></div>
            </a>
            <a href="mailto:<?= e(CONTACT_EMAIL) ?>" class="contact-item">
              <span class="ico"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg></span>
              <div><div class="lbl">Email</div><div class="val"><?= e(CONTACT_EMAIL) ?></div></div>
            </a>
            <div class="contact-item">
              <span class="ico"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 22s7-7 7-12a7 7 0 1 0-14 0c0 5 7 12 7 12Z"/><circle cx="12" cy="10" r="2.5"/></svg></span>
              <div><div class="lbl">Adresse</div><div class="val">Coopérative Al Amal,<br>Azrou, Maroc</div></div>
            </div>
            <div class="contact-item">
              <span class="ico"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span>
              <div><div class="lbl">Horaires</div><div class="val">Lun–Sam : 9h–18h<br><span class="muted" style="font-size:12.5px;">Dimanche : fermé</span></div></div>
            </div>
          </div>
        </div>

        <div class="wa-promo">
          <h3>Le plus rapide ?</h3>
          <p>WhatsApp. Réponse en moins d'une heure pendant les horaires de service.</p>
          <a href="https://wa.me/<?= e(wa_number()) ?>" class="h-btn" target="_blank" rel="noopener">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 3a9 9 0 0 0-7.7 13.7L3 21l4.4-1.2A9 9 0 1 0 12 3Z"/></svg>
            Ouvrir WhatsApp
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          </a>
        </div>
      </div>

      <!-- Right: form -->
      <div>
        <div class="contact-card">
          <h2>Écrivez-nous</h2>
          <p class="sub">Remplissez le formulaire, nous répondons sous 24h ouvrées.</p>

          <?php if ($sent): ?>
            <div style="background: var(--forest-50); color: var(--forest-700); padding: 14px 18px; border-radius: var(--r-md-h); margin-top: 18px; font-size: 0.92rem;">
              ✓ Message envoyé. Nous vous recontactons sous 24 h.
            </div>
          <?php elseif ($error): ?>
            <div style="background: var(--terra-50); color: var(--terra-600); padding: 14px 18px; border-radius: var(--r-md-h); margin-top: 18px; font-size: 0.92rem;">
              <?= e($error) ?>
            </div>
          <?php endif; ?>

          <form method="post" style="margin-top:18px;">
            <?= csrf_field() ?>
            <div style="position:absolute;left:-9999px;" aria-hidden="true">
              <label>Ne pas remplir <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
            </div>

            <div class="gd-field gd-field-full" style="margin-bottom:14px;">
              <label>Sujet du message</label>
              <div class="topic-pills" id="topicPills">
                <?php foreach (['Question produit', 'Suivi commande', 'Gros volume / B2B', 'Presse', 'Autre'] as $t):
                  $active = ($_POST['topic'] ?? 'Question produit') === $t;
                ?>
                  <button type="button" class="topic-pill<?= $active ? ' active' : '' ?>" data-topic="<?= e($t) ?>"><?= e($t) ?></button>
                <?php endforeach; ?>
              </div>
              <input type="hidden" name="topic" id="topicInput" value="<?= e($_POST['topic'] ?? 'Question produit') ?>">
            </div>

            <div class="gd-form-grid">
              <div class="gd-field">
                <label>Nom complet <span class="req">*</span></label>
                <input type="text" name="name" required value="<?= e($_POST['name'] ?? '') ?>" placeholder="Salma Khalil">
              </div>
              <div class="gd-field">
                <label>Email <span class="req">*</span></label>
                <input type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>" placeholder="vous@email.com">
              </div>
              <div class="gd-field gd-field-full">
                <label>Téléphone <span class="muted" style="font-weight:400;">(optionnel)</span></label>
                <input type="tel" name="phone" value="<?= e($_POST['phone'] ?? '') ?>" placeholder="+212 …">
              </div>
              <div class="gd-field gd-field-full">
                <label>Votre message <span class="req">*</span></label>
                <textarea name="message" required placeholder="Dites-nous tout…"><?= e($_POST['message'] ?? '') ?></textarea>
              </div>
              <div class="gd-field-full" style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                <button type="submit" class="h-btn h-btn-primary h-btn-lg">
                  Envoyer le message
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </button>
                <span class="muted" style="font-size:11.5px;">En envoyant, vous acceptez notre politique de confidentialité.</span>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div class="map-band">
      <div class="ph">[ Carte interactive · Coopérative Al Amal, Azrou ]</div>
      <div class="map-pin"></div>
    </div>
  </div>
</section>

<script>
  document.querySelectorAll('#topicPills .topic-pill').forEach(function(p){
    p.addEventListener('click', function(){
      document.querySelectorAll('#topicPills .topic-pill').forEach(function(x){ x.classList.remove('active'); });
      p.classList.add('active');
      document.getElementById('topicInput').value = p.dataset.topic;
    });
  });
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
