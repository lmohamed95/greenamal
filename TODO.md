# TODO — GreenAmal next steps

Running list of things to do after the production deploy stabilized.

## Up next (priority order)

### 1. Reliable transactional emails

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

### 2. Coupon delivery flow (FIRST25 onboarding)

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

### 3. Currency display root cause

Defensive `currency_symbol()` masks the `262145` bug at runtime, but the
underlying config issue is unresolved.

- [ ] Open `public_html/includes/config.local.php` on the server and find
      the line setting `CURRENCY_SYMBOL` (or the constant it derives from).
- [ ] Replace with the correct quoted string: `define('CURRENCY_SYMBOL', 'DH');`
- [ ] Remove the redundancy if a stale duplicate define snuck in.

### 4. Production cleanup

Deferred during debugging — finish before going public.

- [ ] Delete debug files from `public_html/`: `check.php`, `diag.php`,
      `db-test.php`, `test.php`, `vals.php`.
- [ ] Set `APP_DEBUG` to `false` in `config.local.php`.
- [ ] Replace `APP_SECRET` placeholder with a real 64-hex value
      (`php -r "echo bin2hex(random_bytes(32));"`).
- [ ] Change admin password from default `admin123` via
      `/admin/parametres` → Sécurité.

### 5. Product images upload

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
