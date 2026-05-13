# GreenAmal · Namecheap Stellar (cPanel) deploy guide

This is the step-by-step for getting the site live on a fresh Namecheap
Stellar shared-hosting plan, with auto-deploy from GitHub via cPanel's
Git Version Control.

Estimated time end-to-end: **45–60 minutes** the first time.

---

## Phase 0 · Gather these before you start

- Your **cPanel username** — for this site it's `greenamal` (already baked
  into `.cpanel.yml`; if it ever changes, update there too).
- Your **domain** (or the Namecheap-assigned `*.stellar.tld` placeholder
  if your custom domain isn't pointed yet).
- A long, random **APP_SECRET** — generate now and keep handy:
  ```bash
  php -r "echo bin2hex(random_bytes(32));"
  # → 64 hex characters; copy this output
  ```
- A bcrypt hash for the **first admin password**:
  ```bash
  php -r "echo password_hash('PUT-YOUR-PASSWORD-HERE', PASSWORD_BCRYPT);"
  # → $2y$10$… ; copy this output
  ```

---

## Phase 0.5 · Clear out any existing site in `public_html/` (≈3 min)

*Skip this phase only if `public_html/` is already empty.*

The deploy uses `rsync` **without** `--delete`, so files already in
`public_html/` are kept — that's a safety feature for our own
`config.local.php` and uploaded images, but it means leftovers from a
prior WordPress / Joomla / etc. install will collide with GreenAmal's
files and break the site.

Move them aside (don't delete — keeps a recovery path):

1. **Confirm you have a full account backup first.** cPanel →
   **JetBackup** (or **Backup Wizard** → *Download a Full Account
   Backup*). A WordPress DB export alone is not enough — themes,
   plugins, and `wp-content/uploads/` only exist in the files backup.
2. cPanel → **File Manager** → enter `public_html/`.
3. Select all files and folders. Right-click → **Move** → destination
   `/home6/greenamal/wordpress-backup/` (the path will be auto-created).
4. The `public_html/` folder should now be empty. Refresh to confirm.
5. The old WordPress database can stay in cPanel → MySQL Databases for
   now; it doesn't interfere with GreenAmal's separate database. Drop
   it later when you're sure you don't need it.

> **Heads-up on `.htaccess`** — moving everything from `public_html/`
> moves the WordPress `.htaccess` too, which is what you want. Our
> deploy ships its own `.htaccess` (HTTPS redirect, security headers,
> pretty URLs).

---

## Phase 1 · Create the database (≈5 min)

1. cPanel → **MySQL® Databases**.
2. **Create New Database**: name it `greenamal`. cPanel prefixes with your
   username, so the real name becomes e.g. `greenamal_greenamal`. **Write
   the full prefixed name down.**
3. **Add New User**: e.g. `greenamal_app`, generate a strong password.
   Real name will be e.g. `greenamal_greenamal_app`. **Write both down.**
4. **Add User to Database**: pick the user + database → **All Privileges**
   → Make Changes.
5. cPanel → **phpMyAdmin** → click your database in the sidebar so it's
   selected (its name appears in the breadcrumb at the top). Then top
   tab **Import** → **Choose File** → upload one file at a time, in
   this order, clicking **Go** between each:

   1. `sql/schema.sql`              ← tables only
   2. `sql/seed-prod.sql`           ← categories, admin user, settings
   3. `sql/products-shooting.sql`   ← 89 product rows (status=draft)
   4. `sql/products-content.sql`    ← real prices + FR descriptions

   > **Heads up** — the SQL files do **not** contain `CREATE DATABASE`
   > or `USE` statements. On shared hosting, your DB user can't run
   > those (you'd hit `#1044 Access denied`). The script imports into
   > whatever database phpMyAdmin has selected, which is why step 5
   > insists on clicking the DB in the sidebar first.

6. Verify: the sidebar should now list `products`, `categories`, `orders`,
   `customers`, `admin_users`, `coupons`, etc.

---

## Phase 2 · Set the PHP version (1 min)

cPanel → **MultiPHP Manager** → tick your domain → **Select PHP Version
8.1** (or 8.2) → **Apply**. The code uses PHP-8 features (`match`,
`str_starts_with`, named arguments) and will fail under 7.x.

---

## Phase 3 · Set up GitHub auto-deploy (≈10 min)

The repo already contains a [`.cpanel.yml`](.cpanel.yml) file that drives
the deployment.

### 3a · `.cpanel.yml` is already configured

The repo's `.cpanel.yml` already points at `/home6/greenamal/public_html/`.
If you ever move to a different cPanel account, update the `DEPLOYPATH`
line there and push.

### 3b · For a private GitHub repo: add a deploy key

(Skip this section if your repo is public.)

1. cPanel → **SSH Access** → **Manage SSH Keys** → **Generate a New Key**
   (default name is fine, no passphrase).
2. After it's created, click **View/Download** next to the **public** key
   → copy the whole contents.
3. On GitHub: your repo → **Settings** → **Deploy keys** → **Add deploy
   key** → title `cPanel`, paste the public key, leave "Allow write
   access" UNCHECKED → **Add key**.
4. Back in cPanel SSH Access, click **Manage Authorization** on the
   private key → **Authorize**.

### 3c · Clone the repo into cPanel

1. cPanel → **Git™ Version Control** → **Create**.
2. Toggle **Clone a Repository** ON.
3. **Clone URL**: use the SSH form for private repos
   (`git@github.com:lmohamed95/greenamal.git`) or HTTPS for public ones
   (`https://github.com/lmohamed95/greenamal.git`).
4. **Repository Path**: accept the suggested
   `/home/USERNAME/repositories/greenamal`.
5. **Repository Name**: `greenamal`.
6. **Create**. cPanel clones the repo (a few seconds).

### 3d · First deploy

1. In Git Version Control, click **Manage** next to the new repo.
2. Tab **Pull or Deploy** → **Update from Remote** (fetches latest) →
   **Deploy HEAD Commit** (runs `.cpanel.yml`).
3. Open File Manager → `public_html/` → confirm `index.php`,
   `admin/`, `assets/`, etc. now exist there.

### 3e · Auto-deploy on every push (optional)

The Pull-or-Deploy tab shows a webhook URL near the bottom. Copy it.
On GitHub: repo → **Settings → Webhooks → Add webhook** → paste URL,
**Content type**: `application/json`, **Just the push event** → **Add**.
Future pushes will pull automatically. (You'll still need to click
"Deploy HEAD Commit" once if you don't want a second webhook for that —
or use a tiny PHP redeploy script; ask later if you want one.)

---

## Phase 4 · Create `config.local.php` on the server (5 min)

This file holds the production secrets and **must never be in git**. It
overrides the defaults in `includes/config.php`.

1. cPanel → **File Manager** → navigate to `public_html/includes/`.
2. **+ File** (top toolbar) → name `config.local.php` → Create.
3. Right-click → **Edit** → paste this template, fill the placeholders:

   ```php
   <?php
   // PRODUCTION OVERRIDES — gitignored, lives only on the server.

   // --- Database (from Phase 1) ---
   define('DB_HOST', 'localhost');
   define('DB_PORT', '3306');
   define('DB_NAME', 'greenamal_greenamal');         // ← prefixed name
   define('DB_USER', 'greenamal_greenamal_app');     // ← prefixed user
   define('DB_PASS', 'paste-the-password-here');

   // --- Environment ---
   define('APP_ENV',   'production');
   define('APP_DEBUG', false);

   // --- HMAC secret for tokenised order links (from Phase 0) ---
   define('APP_SECRET', 'paste-64-hex-chars-from-phase-0');

   // --- Public URLs / contact ---
   define('SITE_URL',      'https://yourdomain.com');
   define('CONTACT_EMAIL', 'contact@yourdomain.com');
   ```

4. **Save**. Close the editor.

> The constants in `includes/config.php` use the
> `defined(...) || define(...)` pattern, so anything you set here wins
> over the dev defaults.

---

## Phase 5 · HTTPS (5–15 min)

1. **DNS**: if your custom domain isn't pointed at Stellar yet, set its
   nameservers (or A record) per Namecheap's instructions for your plan.
   Wait for propagation (anywhere from minutes to a few hours).
2. cPanel → **SSL/TLS Status** → confirm both `yourdomain.com` and
   `www.yourdomain.com` show green padlocks. AutoSSL runs Let's Encrypt
   automatically; if you don't see the lock after ~30 min, click
   **Run AutoSSL** manually.
3. Force HTTPS: open `public_html/.htaccess` in File Manager and
   uncomment lines 9–10:

   ```apacheconf
   RewriteCond %{HTTPS} !=on
   RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [R=301,L]
   ```

   Save.

---

## Phase 6 · Create the first admin user (2 min)

The seed file may not include one (or it uses the dev `admin123` password
which we've kept off the production login page but not removed from the
DB). Replace it with a real account:

phpMyAdmin → your database → **SQL** tab → run:

```sql
DELETE FROM admin_users WHERE email = 'admin@greenamal.com';

INSERT INTO admin_users (email, password_hash, first_name, last_name, role)
VALUES (
  'you@yourdomain.com',
  '$2y$10$paste-the-bcrypt-hash-from-phase-0',
  'You', 'Admin', 'admin'
);
```

---

## Phase 7 · Smoke test (5 min)

In order — stop and investigate at any failure:

1. `https://yourdomain.com/` — homepage renders, no stack trace, no
   "Database connection failed".
2. `https://yourdomain.com/admin/login.php` — login form appears,
   **demo credentials block must NOT be visible**. (If you see "Compte
   de démo", `APP_DEBUG` didn't take effect — check `config.local.php`.)
3. Log into admin with the email + password you set in Phase 6.
4. Place a test order from the public site (use a real email you can
   check).
5. The order-confirmation email should arrive with a "Voir le détail de
   ma commande →" link. Open it in a private/incognito window — it
   should load (it carries the HMAC token).
6. Try guessing an order: open
   `https://yourdomain.com/order-confirmation.php?order=GA-2026-9999` →
   should redirect to the homepage (IDOR blocked).
7. Try blocked paths: `https://yourdomain.com/sql/schema.sql`,
   `https://yourdomain.com/includes/config.local.php`,
   `https://yourdomain.com/.cpanel.yml` — each should return 404 or 403.
8. Visit `https://yourdomain.com/contact.php`, submit the form — verify
   the success banner.
9. Tail the error log: cPanel File Manager →
   `public_html/storage/logs/php-errors.log` — should be empty or have
   only minor warnings.

---

## Day-2 operations

### Pushing an update

```bash
git push origin main
```

If you wired the webhook in 3e, cPanel pulls automatically. Otherwise:
cPanel → Git Version Control → **Manage** your repo → **Pull or Deploy**
→ **Update from Remote** → **Deploy HEAD Commit**.

The deploy uses rsync **without** `--delete`, so your
`includes/config.local.php`, customer-uploaded images
(`assets/img/uploads/products/…`), and log files are never wiped.

### Backups

cPanel → **JetBackup** (or "Backup Wizard" depending on plan):
- Daily automated DB + home directory backups are usually included on
  Stellar Plus / Business plans.
- Take a manual full backup before any migration, password change, or
  schema update.

### Rotating APP_SECRET

If you ever suspect APP_SECRET is leaked, change it in
`config.local.php`. Existing email links to order pages will stop
working (the HMAC won't match), but logged-in customers and
post-checkout sessions are unaffected.

### Adding a new admin

phpMyAdmin → SQL tab:
```sql
INSERT INTO admin_users (email, password_hash, first_name, last_name, role)
VALUES ('new@yourdomain.com', '$2y$10$…', 'First', 'Last', 'admin');
```
(Generate the hash locally with the one-liner from Phase 0.)

---

## Troubleshooting

| Symptom                                            | Likely cause                                                                   |
|----------------------------------------------------|--------------------------------------------------------------------------------|
| Blank white page / 500                             | PHP version <8.0 → fix in MultiPHP Manager                                     |
| "Database connection failed"                        | `config.local.php` missing or wrong creds → re-check Phase 4                   |
| Demo credentials still visible on `/admin/login`    | `APP_DEBUG` not set to `false` in `config.local.php`                           |
| Order confirmation email link returns to home      | `APP_SECRET` differs between request and email-send → ensure config is stable  |
| `Set-Cookie` lacks `Secure` flag                    | Site loaded over HTTP → finish Phase 5                                         |
| Deploy task runs but nothing changes in `public_html` | `DEPLOYPATH` in `.cpanel.yml` points at the wrong cPanel user                  |
| `Permission denied` writing to `storage/logs/`      | File Manager → right-click `storage/` → Change Permissions → `0755`            |
