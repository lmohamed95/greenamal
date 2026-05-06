# GreenAmal — Local Setup

Get the PHP version running on your Mac in **5 minutes**.

You have two options. **MAMP is recommended** — it mirrors what you'll get on Namecheap (Apache + PHP + MySQL + phpMyAdmin in one stack).

---

## Option A — MAMP (recommended)

### 1. Start MAMP

- Open **MAMP** (already installed in `/Applications`)
- Click **"Start Servers"**
- It boots Apache on `localhost:8888` and MySQL on `localhost:8889`

### 2. Point MAMP at this folder

- MAMP → **Preferences → Server**
- Set **"Document Root"** to:
  ```
  /Users/mohamedlagrighi/Documents/GitHub/GREENAMALTEST
  ```
- Click **OK** (MAMP will restart Apache)

### 3. Create the database

- Open phpMyAdmin: **MAMP webstart page → Tools → phpMyAdmin**
  (or directly: <http://localhost:8888/phpMyAdmin/>)
- Login: user `root`, password `root`
- Click the **SQL** tab
- Open `sql/schema.sql` in your editor → copy ALL → paste into the SQL tab → **Go**
- Then open `sql/seed.sql` → copy ALL → paste → **Go**
- You should see `greenamal` database in the left sidebar with ~12 tables

### 4. Open the site

- Storefront: <http://localhost:8888/index.php>
- Admin: <http://localhost:8888/admin/login.php>
  - Email: `admin@greenamal.com`
  - Password: `admin123`

---

## Option B — Homebrew PHP + MySQL (CLI nerds)

If you prefer running PHP and MySQL natively on your Mac (not MAMP).

### 1. Make sure MySQL is running

```bash
brew services start mysql
```

### 2. Update DB credentials

Edit `includes/config.php`:

```php
define('DB_PORT', '3306');     // Homebrew MySQL default
define('DB_USER', 'root');
define('DB_PASS', '');         // empty unless you set one
```

### 3. Create the database + import data

```bash
mysql -u root < sql/schema.sql
mysql -u root greenamal < sql/seed.sql
```

### 4. Start PHP's built-in server

From the project root:

```bash
php -S localhost:8000
```

### 5. Open in browser

- Storefront: <http://localhost:8000/index.php>
- Admin: <http://localhost:8000/admin/login.php>
  - Email: `admin@greenamal.com`
  - Password: `admin123`

⚠️ Update `SITE_URL` in `includes/config.php` to `http://localhost:8000` if you use this option.

---

## What you can do right now

### Storefront
- Browse products by category, price, search, sort
- Add products to a real cart (server-side session, persists across pages)
- Cart drawer slides in from the right
- Apply the coupon `first25` (−25%) on the cart page
- Complete a real checkout — the order gets saved to the `orders` table
- See an order confirmation page

### Admin
- Real login (bcrypt password verification)
- Dashboard with KPIs computed from real DB data (revenue, orders, AOV, top products, low-stock alerts, recent orders, revenue chart)
- Browse orders with status filters
- View an order, change its status (saved to DB, logs an event in the timeline)
- Browse products with category and stock filters
- Edit a product (every field saves to DB)
- Browse customers segmented by VIP / new / inactive
- Browse coupons with usage stats
- Update store settings (saved to `settings` table)

---

## Project structure

```
GREENAMALTEST/
├── index.php                  # Homepage
├── shop.php                   # Catalog with filters
├── product.php                # Product detail
├── cart.php                   # Cart page
├── checkout.php               # Checkout form
├── order-confirmation.php     # Thank-you page
├── about.php                  # About page
│
├── includes/
│   ├── config.php             # DB credentials, site config
│   ├── db.php                 # PDO wrapper
│   ├── helpers.php            # e(), price(), cart_*, etc.
│   ├── auth.php               # Admin auth
│   ├── header.php             # Storefront <header>
│   └── footer.php             # Storefront <footer>
│
├── api/                       # AJAX endpoints
│   ├── cart-add.php
│   ├── cart-update.php
│   ├── cart-remove.php
│   ├── cart-state.php
│   └── newsletter.php
│
├── admin/
│   ├── login.php / logout.php
│   ├── index.php              # Dashboard
│   ├── orders.php / order-detail.php
│   ├── products.php / product-edit.php
│   ├── customers.php
│   ├── coupons.php
│   ├── settings.php
│   ├── _includes/             # Admin sidebar/topbar
│   └── assets/css/admin.css
│
├── assets/
│   ├── css/styles.css
│   └── js/main.js
│
├── sql/
│   ├── schema.sql             # Tables
│   └── seed.sql               # Sample data
│
├── _maquette-html-backup/     # Original static HTML mockup (reference)
├── ROADMAP.md
└── SETUP.md
```

---

## Common issues

### "Database connection failed"
- Is MAMP running?
- Did you import both `schema.sql` AND `seed.sql`?
- Does `includes/config.php` have the right port (8889 for MAMP, 3306 for Homebrew)?

### "No products on homepage"
- Did you run `seed.sql`? Check phpMyAdmin → `greenamal` → `products` → should show 12 rows.

### "Can't login as admin"
- Use `admin@greenamal.com` / `admin123` exactly (the seed uses bcrypt; case matters)

### Images don't load
- They're hotlinked from Unsplash. Make sure you have internet. (When we go to production, we'll add real product photos.)

### Cart drawer doesn't open
- Hard-refresh (`Cmd+Shift+R`) — the JS file changed.

---

## Next steps

Once you've verified everything works locally:

1. **Confirm the flow** — make a test order through the storefront, then watch it appear in Admin → Orders
2. **Edit a product** in the admin and confirm the change shows on the storefront
3. **Then we go to Namecheap:**
   - Upload files via cPanel File Manager (or FTP)
   - Create the production database in cPanel → MySQL Databases
   - Import `schema.sql` + `seed.sql` via Namecheap's phpMyAdmin
   - Update `includes/config.php` with production credentials
   - Point `greenamal.com` DNS at the hosting

That's covered in `DEPLOYMENT.md` (next up).
