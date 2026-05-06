<footer class="site-footer" id="contact">
  <div class="container">
    <div class="footer-grid">
      <div class="footer-brand">
        <div class="logo">
          <span class="logo-mark">G</span>
          <?= e(SITE_NAME) ?>
        </div>
        <p>Coopérative Al Amal — Produits naturels du Maroc, certifiés ONSSA, issus du cœur de l'Atlas.</p>
        <div class="footer-social">
          <a href="https://facebook.com" aria-label="Facebook"><svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg></a>
          <a href="https://instagram.com" aria-label="Instagram"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5"/><path d="M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg></a>
          <a href="https://wa.me/<?= e(WHATSAPP_NUMBER) ?>" aria-label="WhatsApp"><svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M20.5 3.5A11.4 11.4 0 0012 0a11.5 11.5 0 00-9.7 17.6L0 24l6.6-1.7a11.5 11.5 0 005.4 1.4h.1A11.5 11.5 0 0024 12.2c0-3.1-1.2-6-3.5-8.7zM12 21.5h-.1a9.6 9.6 0 01-4.9-1.3l-.3-.2-3.6.9.9-3.5-.2-.4a9.5 9.5 0 117.4 5.5z"/></svg></a>
          <a href="https://youtube.com" aria-label="YouTube"><svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M22.5 6.4a3 3 0 00-2-2C18.7 4 12 4 12 4s-6.7 0-8.5.4a3 3 0 00-2 2A31 31 0 001 12a31 31 0 00.5 5.6 3 3 0 002 2c1.8.4 8.5.4 8.5.4s6.7 0 8.5-.4a3 3 0 002-2 31 31 0 00.5-5.6 31 31 0 00-.5-5.6zM10 15.5v-7l6 3.5z"/></svg></a>
        </div>
      </div>

      <div class="footer-col">
        <h4>Boutique</h4>
        <ul>
          <li><a href="/categories.php">Toutes les catégories</a></li>
          <li><a href="/shop.php">Tous les produits</a></li>
          <li><a href="/shop.php?sort=sales">Best-sellers</a></li>
          <li><a href="/shop.php?sort=recent">Nouveautés</a></li>
        </ul>
      </div>

      <div class="footer-col">
        <h4>Aide</h4>
        <ul>
          <li><a href="/contact.php">Contact</a></li>
          <li><a href="/faq.php">FAQ</a></li>
          <li><a href="/about.php">Notre histoire</a></li>
          <li><a href="/blog.php">Blog</a></li>
          <li><a href="/returns.php">Retours & remboursements</a></li>
          <li><a href="https://wa.me/<?= e(WHATSAPP_NUMBER) ?>">Service client WhatsApp</a></li>
        </ul>
      </div>

      <div class="footer-col">
        <h4>Contact</h4>
        <div class="footer-contact-item">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
          <span>Coopérative Al Amal,<br>Azrou, Maroc</span>
        </div>
        <div class="footer-contact-item">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/></svg>
          <span><?= e(CONTACT_PHONE) ?></span>
        </div>
        <div class="footer-contact-item">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
          <span><?= e(CONTACT_EMAIL) ?></span>
        </div>
      </div>
    </div>

    <div class="footer-legal-links">
      <a href="/cgv.php">CGV</a>
      <a href="/privacy.php">Confidentialité</a>
      <a href="/cookies.php">Cookies</a>
      <a href="/mentions.php">Mentions légales</a>
      <a href="/returns.php">Retours</a>
      <button type="button" id="cookieReopen" class="footer-link-btn">Préférences cookies</button>
    </div>

    <div class="footer-bottom">
      <span>© <?= date('Y') ?> <?= e(SITE_NAME) ?> — Coopérative Al Amal. Tous droits réservés.</span>
      <div class="footer-payments">
        <span style="color: rgba(250,246,240,0.5); font-size: 0.78rem; margin-right: 6px;">Paiement :</span>
        <span class="payment-pill" style="background: rgba(224,164,88,0.18); color: var(--saffron);">À la livraison</span>
      </div>
    </div>
  </div>
</footer>

<!-- Cookie banner -->
<div class="cookie-banner" id="cookieBanner" role="dialog" aria-label="Préférences cookies" hidden>
  <div class="cookie-text">
    <strong>Cookies & vie privée</strong>
    <p>Nous utilisons des cookies essentiels au fonctionnement du site, et — avec votre accord — des cookies de mesure d'audience pour l'améliorer. <a href="/cookies.php">En savoir plus</a></p>
  </div>
  <div class="cookie-actions">
    <button type="button" class="btn btn-ghost btn-sm" data-cookie="reject">Refuser</button>
    <button type="button" class="btn btn-primary btn-sm" data-cookie="accept">Tout accepter</button>
  </div>
</div>

<script>
(function () {
  const KEY = 'ga_cookie_consent';
  const banner = document.getElementById('cookieBanner');
  function setConsent(value) {
    document.cookie = KEY + '=' + value + ';path=/;max-age=' + (60*60*24*365) + ';samesite=lax';
    banner.hidden = true;
  }
  function getConsent() {
    return (document.cookie.split('; ').find(r => r.startsWith(KEY + '=')) || '').split('=')[1] || '';
  }
  if (!getConsent()) {
    requestAnimationFrame(() => banner.hidden = false);
  }
  banner.querySelectorAll('[data-cookie]').forEach(b => b.addEventListener('click', () => setConsent(b.dataset.cookie)));
  document.getElementById('cookieReopen')?.addEventListener('click', () => banner.hidden = false);
})();
</script>

<a href="https://wa.me/<?= e(WHATSAPP_NUMBER) ?>" class="wa-float" aria-label="WhatsApp">
  <svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor"><path d="M20.5 3.5A11.4 11.4 0 0012 0a11.5 11.5 0 00-9.7 17.6L0 24l6.6-1.7a11.5 11.5 0 005.4 1.4h.1A11.5 11.5 0 0024 12.2c0-3.1-1.2-6-3.5-8.7zM12 21.5h-.1a9.6 9.6 0 01-4.9-1.3l-.3-.2-3.6.9.9-3.5-.2-.4a9.5 9.5 0 117.4 5.5z"/></svg>
</a>

<script src="assets/js/main.js"></script>
</body>
</html>
