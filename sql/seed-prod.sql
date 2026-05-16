-- GreenAmal — Production seed
-- Run AFTER schema.sql.
-- Then run, in order:
--   1. sql/seed-prod.sql      ← THIS FILE  (categories, admin, settings)
--   2. sql/products-shooting.sql  (89 product rows + galleries, status=draft)
--   3. sql/products-content.sql   (real prices, FR descriptions, activates them)
--
-- ⚠️  After import, log in to /admin/login.php with the credentials below
--     and IMMEDIATELY change the admin password in /admin/settings.php.
-- Run from inside the target database (no USE statement — won't work on cPanel
-- where the DB user lacks USE/CREATE privileges on un-prefixed names).

-- =====================================================================
-- Categories
-- =====================================================================
INSERT INTO categories (slug, name, description, image_url, display_order) VALUES
('huiles-essentielles', 'Huiles essentielles',          'Distillées au feu de bois dans la pure tradition amazighe.',                  '/assets/img/categories/huiles-essentielles.jpg', 1),
('huiles-vegetales',    'Huiles végétales',              'Pressées à froid, 100 % pures — argan, olive, nigelle et plus.',            '/assets/img/categories/huiles-vegetales.jpg', 2),
('eau-florale',         'Eaux florales (hydrolats)',     'Eaux distillées de roses, fleurs d''oranger, lavande et menthe.',            '/assets/img/categories/eau-florale.jpg', 3),
('pam',                 'Plantes aromatiques & médicinales','Plantes cueillies à la main et séchées à l''ombre dans l''Atlas.',         '/assets/img/categories/pam.jpg', 4),
('couscous',            'Couscous artisanal',            'Roulé à la main par les femmes de la coopérative.',                          '/assets/img/categories/couscous.jpg', 5),
('farine',              'Farines',                       'Farines anciennes : orge, blé dur, maïs, sarrasin.',                         '/assets/img/categories/farine.jpg', 6),
('poudres',             'Poudres',                       'Ghassoul, henné, gingembre, curcuma — pour la beauté et la cuisine.',        '/assets/img/categories/poudres.jpg', 7),
('savons',              'Savons',                        'Savon noir Beldi, savons à l''huile d''olive et aux plantes.',               '/assets/img/categories/savons.jpg', 8),
('packs',               'Packs & coffrets',              'Coffrets cadeaux et bundles découverte de nos best-sellers.',                '/assets/img/categories/packs.jpg', 9),
('divers',              'Divers',                        'Spécialités du terroir et nouveautés à découvrir.',                          '/assets/img/categories/divers.jpg', 10);

-- =====================================================================
-- Welcome coupon — −25% on first order
-- =====================================================================
INSERT INTO coupons (code, description, type, value, applies_to, min_order, max_uses, max_uses_per_customer, status) VALUES
('FIRST25', 'Bienvenue : −25% sur votre première commande', 'percent', 25.00, 'all', 0, NULL, 1, 'active');

-- =====================================================================
-- Admin user (CHANGE PASSWORD IMMEDIATELY AFTER FIRST LOGIN)
-- Default credentials: admin@greenamal.com / admin123
-- =====================================================================
INSERT INTO admin_users (email, password_hash, first_name, last_name, role) VALUES
('admin@greenamal.com', '$2y$12$f3AQQtOZlUXYdqnKHkinx.7DX55ARvX2rVWt2de8ZFBUMKB8rS8Ze', 'Admin', '', 'super_admin');

-- =====================================================================
-- Site settings
-- =====================================================================
INSERT INTO settings (setting_key, setting_value) VALUES
('site_name', 'GreenAmal'),
('site_tagline', 'Coopérative Al Amal'),
('contact_email', 'contact@greenamal.com'),
('contact_phone', '+212 627-634472'),
('whatsapp_number', '212627634472'),
('shipping_standard_fee', '30'),
('shipping_free_threshold', '350'),
('currency_symbol', 'د.م.'),
('currency_code', 'MAD'),
('default_lang', 'fr');
