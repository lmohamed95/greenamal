# Codex Audit Check

Read-only audit checklist for the GreenAmal project. This document explains what to fix and how to verify each point.

## Critical / High Priority

- [x] Fix missing `customers.address` and `customers.postcode` columns — **DONE (2026-05-22)**

  Problem: `account.php` and `customer_user()` expect columns that are not in `sql/schema.sql`.

  Resolution:

  - Added `address VARCHAR(500)` and `postcode VARCHAR(20)` to `customers` in [sql/schema.sql:95-96](sql/schema.sql#L95-L96) (after `city`).
  - Added idempotent migration [sql/migrations/2026-05-22-customers-address-postcode.sql](sql/migrations/2026-05-22-customers-address-postcode.sql) for existing prod DBs.
  - `php -l account.php` ✅ clean.

  Still TODO on production:

  - Import the migration via phpMyAdmin so prod gets the two new columns.
  - Log in as a customer, open `/mon-compte`, save the profile to confirm.

- [x] Remove or protect `db-test.php` before production — **DONE (2026-05-22)**

  Problem: Public file exposed DB host/user/name, tables, product details, and connection errors.

  Resolution:

  - File removed from production via cPanel.
  - Kept locally for dev/testing.
  - Added to `.gitignore` and untracked (`git rm --cached db-test.php`) so it can never be redeployed accidentally via git.

  Verify:

  - Visit `/db-test.php` on production → `404`. ✅
  - `git ls-files | grep db-test.php` returns empty. ✅

- [x] Replace default admin seed credentials — **PARTIALLY DONE (2026-05-22)**

  Problem: Seed files disclose `admin@greenamal.com / admin123`.

  Resolution:

  - Production admin password has been rotated (confirmed by user).
  - `admin123` no longer works on production.

  Still TODO (low priority, but recommended):

  - Strip the plaintext password comment from `sql/seed-prod.sql:36` and `sql/seed.sql:116` — anyone cloning the repo still sees the old default.
  - Replace the bcrypt hash in `sql/seed-prod.sql` with a placeholder that requires regeneration on first install.

  Suggested patch:

  ```sql
  -- sql/seed-prod.sql
  -- Generate hash with:  php -r "echo password_hash('YOUR_PASSWORD', PASSWORD_DEFAULT), PHP_EOL;"
  -- Then replace the placeholder below before importing.
  ('admin@greenamal.com', '<<REPLACE_WITH_GENERATED_BCRYPT_HASH>>', ...)
  ```

  Verify:

  - `grep -r "admin123" sql/` returns nothing.
  - Fresh seed import requires manual hash insertion.

## Medium Priority

- [ ] Convert category delete from GET to POST + CSRF

  Problem: `admin/categories.php?delete=ID` deletes via GET.

  How to fix:

  - Replace the delete link with a small POST form.
  - Include `csrf_field()`.
  - In `admin/categories.php`, handle deletion only when `$_SERVER['REQUEST_METHOD'] === 'POST'`.
  - Call `csrf_verify()` before deleting.

  Verify:

  - Direct visit to `admin/categories.php?delete=1` does nothing.
  - Delete button still works from the admin UI.

- [ ] Convert logout to POST

  Problem: `admin/logout.php` and `logout.php` mutate session state via GET.

  How to fix:

  - Change logout links into POST forms with CSRF fields.
  - In logout handlers, require POST and call `csrf_verify()`.
  - For GET requests, redirect back or return `405`.

  Verify:

  - Visiting `/logout.php` directly does not log out.
  - Clicking the actual logout control works.

- [ ] Disable admin error display in production

  Problem: `includes/auth.php` forces `display_errors=1` on `/admin/`.

  How to fix:

  - Wrap admin debug display in `APP_DEBUG`.

    ```php
    if (APP_DEBUG && strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/') === 0) {
        // show admin debug output
    }
    ```

  - In production, log errors only.

  Verify:

  - With `APP_ENV=production`, admin fatal errors do not expose file paths/messages.
  - Logs still receive errors.

- [ ] Clamp cart update quantities to available stock

  Problem: `cart_update()` stores any quantity, even more than stock.

  How to fix:

  - In `cart_update()`, fetch product stock/status before saving.
  - If product is inactive or missing, remove it.
  - If quantity exceeds stock, clamp to stock.
  - If stock is `0`, remove it.

  Verify:

  - Try updating cart quantity above stock.
  - Cart shows max available quantity.
  - Checkout still blocks race-condition oversells.

## Lower Priority / Hardening

- [ ] Enforce admin roles, or remove roles from UI/schema

  Problem: Roles exist but all logged-in admins can access destructive routes.

  Fix option A:

  - Add helpers like:

    ```php
    admin_require_role(['super_admin', 'admin']);
    ```

  - Apply them to product/category/order/settings/coupon mutation routes.

  Fix option B:

  - If roles are not needed, remove role display to avoid false confidence.

  Verify:

  - A `viewer` cannot delete products, edit settings, upload images, or export sensitive data.

- [ ] Add deployment safety checks

  Confirm production has:

  - `APP_ENV='production'`
  - `APP_DEBUG=false`
  - strong `APP_SECRET`
  - no default admin password
  - no public `db-test.php`
  - HTTPS enabled

  Verify:

  - Add or update a pre-deploy checklist in `HOSTING-DEPLOY.md`.

## Suggested Fix Order

1. ~~Remove/protect `db-test.php`.~~ ✅ Done — gitignored + removed from prod.
2. ~~Rotate default admin password.~~ ✅ Done — strip leftover seed comments for full closure.
3. ~~Fix schema mismatch (`customers.address` / `postcode`).~~ ✅ Done — schema + migration added; needs import on prod.
4. Fix GET delete + logout CSRF. ← next
5. Disable admin debug output in production.
6. Clamp cart quantities.
7. Add role enforcement.

## Final Verification Checklist

- [ ] `php -l` passes for all PHP files.
- [ ] Fresh database import works.
- [ ] Customer account page opens and saves.
- [x] Admin login works with new password only.
- [x] `/db-test.php` is inaccessible in production.
- [ ] GET category delete no longer works.
- [ ] Logout requires intended UI action.
- [ ] Production does not display PHP stack/file errors.
- [ ] Cart quantity cannot exceed stock.
- [ ] Admin role permissions match expected access.
