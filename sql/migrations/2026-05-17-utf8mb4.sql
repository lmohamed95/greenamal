-- 2026-05-17 · Convert all tables to utf8mb4
--
-- Production was provisioned with latin1 (cPanel default), so any save that
-- contains a 3-byte UTF-8 character (★, em-dashes, emoji, smart quotes…)
-- crashed with `SQLSTATE[22007] · Incorrect string value`.
--
-- Local dev runs utf8mb4 already, so this is a no-op there. On production it
-- converts every table — preserving existing data — to utf8mb4 / unicode_ci so
-- it can store the full UTF-8 range.
--
-- Safe to re-run: CONVERT TO is idempotent when the table is already utf8mb4.

-- 1. Default charset for any NEW tables created later
ALTER DATABASE `greenamal_greenamal` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 2. Convert existing tables (the one immediately needed first, then the rest)
ALTER TABLE `settings`                CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `products`                CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `categories`              CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `product_images`          CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `product_components`      CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `reviews`                 CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `customers`               CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `admin_users`             CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `orders`                  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `order_items`             CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `order_events`            CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `coupons`                 CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `coupon_products`         CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `coupon_categories`       CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `newsletter_subscribers`  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
