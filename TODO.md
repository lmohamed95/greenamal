# TODO — GreenAmal next steps

Running list of things to do after the production deploy stabilized.

## Up next (priority order)

### 1. Mobile experience overhaul ★ current focus

The site loads on phones but the experience isn't tuned — most traffic will
arrive on mobile (Moroccan e-commerce skews heavily to phones), so this is
the biggest single conversion lever right now.

**Audit first (don't fix blind):**
- [ ] Run Lighthouse on `/`, `/boutique`, `/p/<a-product>`, `/panier`, `/paiement` —
      record Performance / Accessibility / Best Practices / SEO scores at the
      "Mobile" preset. Re-run after each pass to measure delta.
- [ ] Test on a real iPhone and a real Android (not just Chrome DevTools) —
      DevTools lies about scroll inertia, sticky-position, and viewport units.
- [ ] Tap through the **full purchase flow** on a phone: home → category →
      product → add to cart → checkout → confirm. Write down every friction
      moment.

**Likely problem areas to fix (in roughly this order):**

*Header & navigation*
- [ ] Hamburger menu (`.menu-toggle` in `includes/header.php:101`) — verify
      it opens, closes, traps focus, locks body scroll, has a backdrop, and
      is dismissible by tapping outside or pressing back.
- [ ] Logo + cart icon stay visible when the menu is open (sticky header).
- [ ] Search icon: tapping it should show an inline search bar, not navigate
      away mid-scroll.

*Product cards / shop grid*
- [ ] `.product-grid` is 2-col at ≤720px (`assets/css/styles.css:583`) — feels
      tight on phones <360px wide. Consider 1-col at <380px or tighter card
      padding.
- [ ] Product card "Voir" button → minimum 44×44px tap target.
- [ ] Image aspect ratio fixed (no CLS as images load).

*Product page*
- [ ] "Ajouter au panier" CTA → sticky at the bottom of the viewport on
      mobile only, with quantity stepper. Right now the user has to scroll
      back up after reading the long description.
- [ ] Gallery: swipeable horizontally on mobile, not vertical stack.
- [ ] Price + variant sticky in the header on scroll, like Shopify-style.

*Cart & checkout*
- [ ] Checkout form inputs (`checkout.php`) — verify `inputmode`,
      `autocomplete`, and `type` attributes are set correctly so phone
      keyboards show the right layout (numeric for postcode/phone,
      email-optimised for email).
- [ ] "Nom complet" field: consider splitting Prénom + Nom (also covered in
      "Later") — single fields invite typos like email-in-name.
- [ ] Phone number: prefill `+212` and accept Moroccan format.
- [ ] Quantity stepper in cart — +/- buttons should be 44×44px touch
      targets, not the tiny default browser inputs.
- [ ] Total + "Passer la commande" CTA sticky at the bottom of the cart
      page, always visible.

*Typography & spacing*
- [ ] Body text ≥16px on mobile (iOS Safari zooms otherwise on input focus).
- [ ] Tap targets minimum 44×44px (links, buttons, social icons).
- [ ] Hero h1 — reduce on mobile so it doesn't take the full screen above
      the fold.

*Performance*
- [ ] Image lazy-loading: already on via `picture_tag()`. Verify LCP image
      (hero / first product card) is NOT lazy-loaded — `loading="eager"`
      and `fetchpriority="high"`.
- [ ] Defer non-critical CSS / JS. Inline critical CSS for the above-the-
      fold render if Lighthouse complains about FCP.
- [ ] Audit the WhatsApp chat bubble (visible bottom-right) — is it loading
      a third-party script that delays interactivity?

*Polish*
- [ ] Test landscape orientation on phones (sometimes broken).
- [ ] iOS safe-area-inset for the bottom sticky CTA (notched phones).
- [ ] Pull-to-refresh: not broken anywhere.
- [ ] Cookie banner doesn't cover the entire screen on small phones.

**Acceptance:** Lighthouse mobile scores Perf ≥85, A11y ≥95, SEO ≥95.
Manual: purchase flow completable in one hand without rage-tapping.

### 2. Reliable transactional emails

PHP `mail()` works on shared hosting but lands in spam half the time
until SPF/DKIM are warm. Plan:

- [ ] Verify SPF + DKIM are configured in cPanel → **Email Deliverability**.
      Fix any "Repair" warnings.
- [ ] Confirm `MAIL_FROM` (`noreply@greenamal.com`) exists as a real
      mailbox in cPanel → Email Accounts (some hosts reject mail from
      non-existent local addresses).
- [ ] Test the full flow: register a new customer → confirm welcome email
      arrives (not spam) → place a test order → confirm order-confirmation
      email arrives → admin notification email arrives.
- [ ] If deliverability is still flaky after SPF/DKIM are clean,
      switch to **Resend** (or Postmark). Set `RESEND_API_KEY` in
      `config.local.php`, then patch `send_mail()` in `includes/mail.php`
      to hit the Resend API when the key is present.

### 3. Coupon delivery flow (FIRST25 onboarding)

The site already supports the FIRST25 coupon and the welcome email
mentions it, but the discount logic at checkout should be verified
end-to-end:

- [ ] Verify the `FIRST25` coupon row exists in the `coupons` table on
      production (slug, type=`percent`, value=`25`, active, no expiry).
- [ ] Test as a brand-new customer: register → receive welcome email
      → place an order → apply `FIRST25` → confirm 25% is deducted.
- [ ] Decide whether to enforce "one-use-per-customer" (already supported
      by the schema via `coupon_uses` linkage — verify it actually blocks
      a second use).
- [ ] Optional: switch the welcome email's static `FIRST25` reference to
      a personalised single-use token (mail_welcome currently hardcodes
      the public code).

### 4. Currency display root cause

Defensive `currency_symbol()` masks the `262145` bug at runtime, but the
underlying config issue is unresolved.

- [ ] Open `public_html/includes/config.local.php` on the server and find
      the line setting `CURRENCY_SYMBOL` (or the constant it derives from).
- [ ] Replace with the correct quoted string: `define('CURRENCY_SYMBOL', 'DH');`
- [ ] Remove the redundancy if a stale duplicate define snuck in.

### 5. Production cleanup

Deferred during debugging — finish before going public.

- [ ] Delete debug files from `public_html/`: `check.php`, `diag.php`,
      `db-test.php`, `test.php`, `vals.php`.
- [ ] Set `APP_DEBUG` to `false` in `config.local.php`.
- [ ] Replace `APP_SECRET` placeholder with a real 64-hex value
      (`php -r "echo bin2hex(random_bytes(32));"`).
- [ ] Change admin password from default `admin123` via
      `/admin/parametres` → Sécurité.

### 6. Product images upload

- [ ] Upload `~/Desktop/greenamal-product-images.zip` to
      `public_html/assets/img/uploads/` and extract.
- [ ] Verify products on `/boutique` display real photos (not placeholders).

## Later / nice to have

- [ ] Force HTTPS in `.htaccess` (uncomment the redirect block) once SSL
      is verified working everywhere.
- [ ] Submit refreshed sitemap to Google Search Console after URL
      translation deploy: `https://greenamal.com/sitemap`.
- [ ] Add `/produit/<slug>` and `/categorie/<slug>` pretty URLs alongside
      the existing `/p/<slug>` and `/c/<slug>` shorthand.
- [ ] Split checkout's "Nom complet" field into Prénom + Nom to prevent
      bad data (customers typing their email in the name field).
- [ ] Set up daily DB backup via cPanel cron + email/Dropbox export.
- [ ] Email Deliverability monitoring (e.g. mail-tester.com after each
      template change).
- [ ] Add an admin-side "send myself a test email" button to verify
      deliverability without placing fake orders.
