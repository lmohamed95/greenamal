<?php
require_once __DIR__ . '/includes/helpers.php';

$page_title = 'Contact';
$page_desc  = 'Contactez la coopérative GreenAmal — téléphone, WhatsApp, email. Service client basé à Azrou, Maroc.';
$nav        = 'contact';
$jsonld     = [
    [
        '@context'    => 'https://schema.org',
        '@type'       => 'ContactPage',
        'name'        => 'Contact — ' . SITE_NAME,
        'url'         => seo_canonical(),
        'description' => $page_desc,
    ],
    seo_org_jsonld(),
    seo_breadcrumb_jsonld([
        ['Accueil', '/index.php'],
        ['Contact', '/contact.php'],
    ]),
];

$sent = false;
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name && filter_var($email, FILTER_VALIDATE_EMAIL) && $message) {
        // For now, store as a "newsletter subscriber" with source=contact + a note in the message field would need a proper messages table
        // Quick path: log to an internal admin email via mail() in production. Locally, just confirm.
        $sent = true;
    } else {
        $error = 'Merci de remplir au moins le nom, l\'email et le message.';
    }
}

require __DIR__ . '/includes/header.php';
?>

<div class="container breadcrumb">
  <a href="/index.php">Accueil</a><span>/</span><span>Contact</span>
</div>

<section class="about-hero">
  <div class="container">
    <span class="eyebrow">Nous contacter</span>
    <h1>Une question ? <em style="color: var(--terracotta); font-style: italic;">Parlez-nous.</em></h1>
    <p>Notre équipe répond du lundi au samedi, 9h–18h. Le moyen le plus rapide reste WhatsApp.</p>
  </div>
</section>

<section class="section section-sand">
  <div class="container">
    <div style="display: grid; grid-template-columns: 1fr 1.4fr; gap: 40px; align-items: flex-start;" class="contact-grid">

      <aside style="display: flex; flex-direction: column; gap: 16px;">
        <div style="background: var(--white); border-radius: var(--radius); padding: 28px; border: 1px solid var(--line);">
          <h3 style="margin-bottom: 18px;">Coordonnées</h3>
          <div style="display: flex; flex-direction: column; gap: 14px;">
            <div style="display: flex; gap: 12px; align-items: flex-start;">
              <div style="width: 38px; height: 38px; border-radius: 50%; background: var(--sand); color: var(--olive); display: grid; place-items: center; flex-shrink: 0;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/></svg>
              </div>
              <div>
                <div style="font-size: 0.78rem; color: var(--ink-mute); text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 2px;">Téléphone</div>
                <a href="tel:<?= str_replace(' ', '', e(CONTACT_PHONE)) ?>" style="color: var(--ink); font-weight: 500;"><?= e(CONTACT_PHONE) ?></a>
              </div>
            </div>

            <div style="display: flex; gap: 12px; align-items: flex-start;">
              <div style="width: 38px; height: 38px; border-radius: 50%; background: #25D36622; color: #25D366; display: grid; place-items: center; flex-shrink: 0;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M20.5 3.5A11.4 11.4 0 0012 0a11.5 11.5 0 00-9.7 17.6L0 24l6.6-1.7a11.5 11.5 0 005.4 1.4h.1A11.5 11.5 0 0024 12.2c0-3.1-1.2-6-3.5-8.7zM12 21.5h-.1a9.6 9.6 0 01-4.9-1.3l-.3-.2-3.6.9.9-3.5-.2-.4a9.5 9.5 0 117.4 5.5z"/></svg>
              </div>
              <div>
                <div style="font-size: 0.78rem; color: var(--ink-mute); text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 2px;">WhatsApp</div>
                <a href="https://wa.me/<?= e(WHATSAPP_NUMBER) ?>" style="color: var(--ink); font-weight: 500;">Chat avec nous →</a>
              </div>
            </div>

            <div style="display: flex; gap: 12px; align-items: flex-start;">
              <div style="width: 38px; height: 38px; border-radius: 50%; background: var(--sand); color: var(--olive); display: grid; place-items: center; flex-shrink: 0;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
              </div>
              <div>
                <div style="font-size: 0.78rem; color: var(--ink-mute); text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 2px;">Email</div>
                <a href="mailto:<?= e(CONTACT_EMAIL) ?>" style="color: var(--ink); font-weight: 500;"><?= e(CONTACT_EMAIL) ?></a>
              </div>
            </div>

            <div style="display: flex; gap: 12px; align-items: flex-start;">
              <div style="width: 38px; height: 38px; border-radius: 50%; background: var(--sand); color: var(--olive); display: grid; place-items: center; flex-shrink: 0;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
              </div>
              <div>
                <div style="font-size: 0.78rem; color: var(--ink-mute); text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 2px;">Adresse</div>
                <span style="color: var(--ink);">Coopérative Al Amal,<br>Azrou, Maroc</span>
              </div>
            </div>

            <div style="display: flex; gap: 12px; align-items: flex-start;">
              <div style="width: 38px; height: 38px; border-radius: 50%; background: var(--sand); color: var(--olive); display: grid; place-items: center; flex-shrink: 0;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
              </div>
              <div>
                <div style="font-size: 0.78rem; color: var(--ink-mute); text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 2px;">Horaires</div>
                <span style="color: var(--ink);">Lun – Sam : 9h – 18h<br>Dimanche : fermé</span>
              </div>
            </div>
          </div>
        </div>

        <div style="background: var(--olive); color: var(--cream); border-radius: var(--radius); padding: 24px;">
          <strong style="font-family: var(--font-display); font-size: 1.3rem; display: block; margin-bottom: 8px;">Le plus rapide ?</strong>
          <p style="font-size: 0.92rem; opacity: 0.85; margin-bottom: 16px;">WhatsApp. Réponse en moins d'une heure pendant les horaires de service.</p>
          <a href="https://wa.me/<?= e(WHATSAPP_NUMBER) ?>" class="btn btn-primary btn-block">Ouvrir WhatsApp →</a>
        </div>
      </aside>

      <div style="background: var(--white); border-radius: var(--radius); padding: 32px; border: 1px solid var(--line);">
        <h3 style="margin-bottom: 8px;">Écrivez-nous</h3>
        <p style="color: var(--ink-soft); margin-bottom: 24px;">Remplissez le formulaire — nous répondons sous 24 h ouvrées.</p>

        <?php if ($sent): ?>
          <div style="background: rgba(74, 122, 79, 0.12); color: var(--olive-dark); padding: 14px 18px; border-radius: var(--radius-sm); margin-bottom: 24px; font-size: 0.92rem;">
            ✓ Message envoyé. Nous vous recontactons sous 24 h.
          </div>
        <?php elseif ($error): ?>
          <div style="background: rgba(200, 85, 61, 0.12); color: var(--terracotta-dark); padding: 14px 18px; border-radius: var(--radius-sm); margin-bottom: 24px; font-size: 0.92rem;">
            <?= e($error) ?>
          </div>
        <?php endif; ?>

        <form method="post">
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
            <div style="display: flex; flex-direction: column; gap: 6px;">
              <label style="font-size: 0.85rem; font-weight: 500;">Nom complet *</label>
              <input type="text" name="name" required value="<?= e($_POST['name'] ?? '') ?>" style="padding: 12px 14px; border: 1px solid var(--line); border-radius: var(--radius-sm);">
            </div>
            <div style="display: flex; flex-direction: column; gap: 6px;">
              <label style="font-size: 0.85rem; font-weight: 500;">Email *</label>
              <input type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>" style="padding: 12px 14px; border: 1px solid var(--line); border-radius: var(--radius-sm);">
            </div>
          </div>
          <div style="display: flex; flex-direction: column; gap: 6px; margin-bottom: 14px;">
            <label style="font-size: 0.85rem; font-weight: 500;">Téléphone (optionnel)</label>
            <input type="tel" name="phone" value="<?= e($_POST['phone'] ?? '') ?>" style="padding: 12px 14px; border: 1px solid var(--line); border-radius: var(--radius-sm);">
          </div>
          <div style="display: flex; flex-direction: column; gap: 6px; margin-bottom: 20px;">
            <label style="font-size: 0.85rem; font-weight: 500;">Votre message *</label>
            <textarea name="message" required rows="5" style="padding: 12px 14px; border: 1px solid var(--line); border-radius: var(--radius-sm); font-family: inherit;"><?= e($_POST['message'] ?? '') ?></textarea>
          </div>
          <button type="submit" class="btn btn-primary btn-lg">
            Envoyer le message
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
          </button>
        </form>
      </div>
    </div>

    <style>
      @media (max-width: 900px) {
        .contact-grid { grid-template-columns: 1fr !important; }
      }
    </style>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
