-- GreenAmal — Seed data
-- Run AFTER schema.sql, from inside the target database.
-- (`mysql greenamal < seed.sql` locally · phpMyAdmin → select DB → Import on cPanel)

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
-- Products
-- =====================================================================
INSERT INTO products (slug, sku, name, category_id, description_short, description_long, price, compare_at_price, cost, stock, image_main, status, is_featured, rating_avg, rating_count, sales_count, tags) VALUES
('huile-argan-pure-100ml', 'ARG-100-COSM', 'Huile d''argan pure 100ml',
  (SELECT id FROM categories WHERE slug='huiles-vegetales'),
  'Première pression à froid, non torréfiée. Pressée à la main par les femmes de la coopérative Al Amal.',
  'L''huile d''argan GreenAmal est extraite à partir de fruits récoltés à la main dans les arganeraies du Moyen Atlas, classées réserve de biosphère par l''UNESCO. Pressée à froid, sans aucun additif, elle conserve toutes ses propriétés nourrissantes. Riche en vitamine E, en acides gras essentiels (oméga 6 et 9) et en antioxydants.',
  129.00, 160.00, 75.00, 42, 'https://images.unsplash.com/photo-1608571423902-eed4a5ad8108?w=900&q=80', 'active', 1, 4.9, 127, 127, 'argan,bio,pressé à froid,cosmétique'),

('huile-essentielle-romarin-30ml', 'HE-ROM-30', 'Huile essentielle de romarin 30ml',
  (SELECT id FROM categories WHERE slug='huiles-essentielles'),
  'Romarin de l''Atlas, distillé à la vapeur d''eau. Tonifiant, stimulant.',
  'Distillée artisanalement à partir de romarin sauvage de l''Atlas. Idéale en aromathérapie pour stimuler la concentration, soulager les tensions musculaires et favoriser la circulation.',
  89.00, NULL, 42.00, 3, 'https://images.unsplash.com/photo-1608248543803-ba4f8c70ae0b?w=900&q=80', 'active', 1, 4.8, 89, 89, 'romarin,huile essentielle,aromathérapie'),

('couscous-orge-bio-1kg', 'CSC-ORG-1K', 'Couscous d''orge bio 1kg',
  (SELECT id FROM categories WHERE slug='couscous'),
  'Roulé à la main, séché au soleil. Le goût authentique du Maroc rural.',
  'Couscous d''orge biologique préparé selon la tradition amazighe. Roulé à la main par les femmes de la coopérative, séché au soleil de l''Atlas. Plus rustique et plus parfumé que le couscous de blé.',
  45.00, NULL, 18.00, 9, 'https://images.unsplash.com/photo-1612537786051-1d4cc6cf16d8?w=900&q=80', 'active', 1, 4.9, 214, 214, 'couscous,orge,bio,artisanal'),

('hydrolat-rose-damascena-250ml', 'HYD-ROS-250', 'Hydrolat de rose damascène 250ml',
  (SELECT id FROM categories WHERE slug='eau-florale'),
  'Eau florale pure de rose de Damas. Tonique, apaisante.',
  'Distillée à partir de pétales de rose de Damas cultivées dans la région de Kelaat M''Gouna. Excellent tonique pour la peau, calmante et hydratante. Utilisable aussi en cuisine.',
  65.00, NULL, 28.00, 38, 'https://images.unsplash.com/photo-1556228841-a3c527ebefe5?w=900&q=80', 'active', 1, 4.9, 156, 156, 'rose,hydrolat,damas,tonique'),

('verveine-sechee-100g', 'PLT-VRV-100', 'Verveine séchée artisanale 100g',
  (SELECT id FROM categories WHERE slug='pam'),
  'Verveine du Moyen Atlas, séchée à l''ombre. Pour vos infusions.',
  'Cueillie à la main dans les jardins amazighs, séchée lentement à l''ombre pour préserver tous ses arômes. Digestive, relaxante.',
  35.00, NULL, 12.00, 7, 'https://images.unsplash.com/photo-1471193945509-9ad0617afabf?w=900&q=80', 'active', 0, 4.5, 42, 42, 'verveine,plante,infusion,relaxation'),

('savon-noir-beldi-200g', 'SAV-BLD-200', 'Savon noir Beldi à l''eucalyptus 200g',
  (SELECT id FROM categories WHERE slug='savons'),
  'Savon noir traditionnel à l''huile d''olive et eucalyptus.',
  'Savon noir Beldi authentique, fabriqué à base d''huile d''olive et de feuilles d''eucalyptus broyées. Utilisé dans les hammams marocains depuis des siècles. Exfoliant doux, prépare la peau au gommage au gant kessa.',
  59.00, 69.00, 22.00, 54, 'https://images.unsplash.com/photo-1612871689353-cccf581d667b?w=900&q=80', 'active', 1, 4.9, 98, 98, 'savon,beldi,hammam,exfoliant'),

('ghassoul-argile-500g', 'GHS-ARG-500', 'Ghassoul argile naturelle 500g',
  (SELECT id FROM categories WHERE slug='poudres'),
  'Argile volcanique du Moyen Atlas. Soin cheveux et visage.',
  'Argile minérale extraite uniquement au Maroc. Purifie la peau et nettoie les cheveux en douceur, sans agresser. Utilisé depuis des générations dans les rituels de beauté amazighs.',
  49.00, NULL, 18.00, 76, 'https://images.unsplash.com/photo-1556228720-195a672e8a03?w=900&q=80', 'active', 1, 4.9, 184, 184, 'ghassoul,argile,cheveux,visage'),

('huile-eucalyptus-30ml', 'HE-EUC-30', 'Huile essentielle d''eucalyptus 30ml',
  (SELECT id FROM categories WHERE slug='huiles-essentielles'),
  'Eucalyptus globulus de l''Atlas. Décongestionnant respiratoire.',
  'Idéale pour dégager les voies respiratoires en hiver. À diffuser ou diluer dans une huile végétale pour massage.',
  79.00, NULL, 38.00, 32, 'https://images.unsplash.com/photo-1601493700631-2b16ec4b4716?w=900&q=80', 'active', 0, 4.8, 73, 73, 'eucalyptus,huile essentielle,respiratoire'),

('hydrolat-fleur-oranger-250ml', 'HYD-OR-250', 'Hydrolat fleur d''oranger 250ml',
  (SELECT id FROM categories WHERE slug='eau-florale'),
  'Eau florale d''oranger, douce et apaisante.',
  'Distillée à partir de fleurs d''oranger de la région de Marrakech. Calmante, parfaite pour les peaux sensibles, et incontournable en pâtisserie marocaine.',
  55.00, NULL, 25.00, 28, 'https://images.unsplash.com/photo-1565299585323-38d6b0865b47?w=900&q=80', 'active', 0, 4.9, 112, 112, 'oranger,hydrolat,calmant'),

('thym-sauvage-80g', 'PLT-THY-80', 'Thym sauvage de l''Atlas 80g',
  (SELECT id FROM categories WHERE slug='pam'),
  'Thym sauvage cueilli dans les hauteurs de l''Atlas.',
  'Variété endémique du Maroc, plus parfumée que le thym commun. En infusion, en cuisine, ou en gargarisme pour soigner les maux de gorge.',
  29.00, NULL, 11.00, 45, 'https://images.unsplash.com/photo-1466692476868-aef1dfb1e735?w=900&q=80', 'active', 0, 4.9, 67, 67, 'thym,plante,infusion,cuisine'),

('creme-visage-argan-50ml', 'COS-CRM-50', 'Crème visage à l''argan 50ml',
  (SELECT id FROM categories WHERE slug='divers'),
  'Crème nourrissante à l''huile d''argan pure.',
  'Notre nouvelle crème visage formulée à 30 % d''huile d''argan pure. Hydrate, nourrit et redonne de l''éclat à la peau. Sans parabène, sans silicone.',
  119.00, NULL, 52.00, 28, 'https://images.unsplash.com/photo-1571781926291-c477ebfd024b?w=900&q=80', 'draft', 0, 4.9, 28, 28, 'argan,crème,visage,nouveau'),

('couscous-semoule-fine-1kg', 'CSC-SF-1K', 'Couscous semoule fine 1kg',
  (SELECT id FROM categories WHERE slug='couscous'),
  'Couscous de blé dur, roulé à la main, fin et léger.',
  'La semoule fine traditionnelle pour le couscous du vendredi. Roulé manuellement, séché à l''air libre.',
  38.00, NULL, 14.00, 62, 'https://images.unsplash.com/photo-1547592180-85f173990554?w=900&q=80', 'active', 0, 4.7, 95, 95, 'couscous,semoule,artisanal');

-- =====================================================================
-- Product images (gallery — main image already in products.image_main)
-- =====================================================================
INSERT INTO product_images (product_id, url, display_order) VALUES
((SELECT id FROM products WHERE slug='huile-argan-pure-100ml'), 'https://images.unsplash.com/photo-1556228720-195a672e8a03?w=900&q=80', 1),
((SELECT id FROM products WHERE slug='huile-argan-pure-100ml'), 'https://images.unsplash.com/photo-1601493700631-2b16ec4b4716?w=900&q=80', 2),
((SELECT id FROM products WHERE slug='huile-argan-pure-100ml'), 'https://images.unsplash.com/photo-1604908176997-125f25cc6f3d?w=900&q=80', 3);

-- =====================================================================
-- Coupons
-- =====================================================================
INSERT INTO coupons (code, description, type, value, min_order, max_uses, uses_count, status, expires_at) VALUES
('first25', 'Bienvenue −25% (première commande)', 'percent', 25.00, 0, NULL, 284, 'active', NULL),
('RAMADAN26', 'Ramadan 2026 −15% sur toute la boutique', 'percent', 15.00, 0, 500, 89, 'active', '2026-05-15 23:59:59'),
('FREESHIP500', 'Livraison gratuite dès 500 د.م.', 'free_shipping', 30.00, 500, NULL, 32, 'active', NULL),
('VIP30', 'VIP −30%', 'percent', 30.00, 0, 42, 7, 'active', '2026-12-31 23:59:59'),
('ARGAN50', '−50 د.م. sur les huiles d''argan', 'fixed', 50.00, 100, 100, 0, 'scheduled', '2026-06-30 23:59:59');

-- =====================================================================
-- Admin user
-- Password: admin123 (bcrypt hash, change after first login)
-- =====================================================================
INSERT INTO admin_users (email, password_hash, first_name, last_name, role) VALUES
('admin@greenamal.com', '$2y$12$f3AQQtOZlUXYdqnKHkinx.7DX55ARvX2rVWt2de8ZFBUMKB8rS8Ze', 'Fatima', 'Zahra', 'super_admin');

-- =====================================================================
-- Sample customers
-- =====================================================================
INSERT INTO customers (email, first_name, last_name, phone, city, total_orders, lifetime_value, segment, last_order_at) VALUES
('amina.b@email.com', 'Amina', 'Benali', '+212661234567', 'Casablanca', 7, 2145.00, 'vip', NOW()),
('youssef.a@email.com', 'Youssef', 'Alami', '+212662345678', 'Rabat', 3, 584.00, 'regular', NOW()),
('sara.m@email.com', 'Sara', 'Mahmoudi', '+212663456789', 'Marrakech', 12, 3821.00, 'vip', DATE_SUB(NOW(), INTERVAL 1 DAY)),
('mehdi.k@email.com', 'Mehdi', 'Khalifi', '+212664567890', 'Tanger', 2, 308.00, 'regular', DATE_SUB(NOW(), INTERVAL 1 DAY)),
('fatima.z@email.com', 'Fatima', 'Zahra', '+212665678901', 'Fès', 1, 85.00, 'new', DATE_SUB(NOW(), INTERVAL 1 DAY)),
('rachid.b@email.com', 'Rachid', 'Bouhssini', '+212666789012', 'Casablanca', 5, 1247.00, 'regular', DATE_SUB(NOW(), INTERVAL 3 DAY)),
('nadia.a@email.com', 'Nadia', 'Amrani', '+212667890123', 'Agadir', 4, 892.00, 'regular', DATE_SUB(NOW(), INTERVAL 3 DAY)),
('karim.e@email.com', 'Karim', 'El Idrissi', '+212668901234', 'Casablanca', 9, 2879.00, 'vip', DATE_SUB(NOW(), INTERVAL 4 DAY));

-- =====================================================================
-- Sample orders
-- =====================================================================
INSERT INTO orders (order_number, customer_id, status, payment_method, payment_status, subtotal, shipping, discount, total, shipping_name, shipping_email, shipping_phone, shipping_address, shipping_city, shipping_postcode, coupon_code, created_at) VALUES
('GA-2026-0312', (SELECT id FROM customers WHERE email='amina.b@email.com'), 'pending', 'cmi', 'pending', 318.00, 30.00, 4.00, 344.00, 'Amina Benali', 'amina.b@email.com', '+212661234567', '12 rue des Roses, Apt 4B, Quartier Maârif', 'Casablanca', '20100', 'first25', NOW()),
('GA-2026-0311', (SELECT id FROM customers WHERE email='youssef.a@email.com'), 'processing', 'cod', 'pending', 89.00, 30.00, 0, 119.00, 'Youssef Alami', 'youssef.a@email.com', '+212662345678', '5 avenue Hassan II', 'Rabat', '10000', NULL, DATE_SUB(NOW(), INTERVAL 3 HOUR)),
('GA-2026-0310', (SELECT id FROM customers WHERE email='sara.m@email.com'), 'shipped', 'cmi', 'paid', 482.00, 30.00, 0, 512.00, 'Sara Mahmoudi', 'sara.m@email.com', '+212663456789', '23 boulevard Mohamed V', 'Marrakech', '40000', NULL, DATE_SUB(NOW(), INTERVAL 1 DAY)),
('GA-2026-0309', (SELECT id FROM customers WHERE email='mehdi.k@email.com'), 'delivered', 'cod', 'paid', 189.00, 30.00, 0, 219.00, 'Mehdi Khalifi', 'mehdi.k@email.com', '+212664567890', '8 rue Ibn Battouta', 'Tanger', '90000', NULL, DATE_SUB(NOW(), INTERVAL 1 DAY)),
('GA-2026-0308', (SELECT id FROM customers WHERE email='fatima.z@email.com'), 'cancelled', 'cmi', 'refunded', 55.00, 30.00, 0, 85.00, 'Fatima Zahra', 'fatima.z@email.com', '+212665678901', '14 rue Talâa Kebira', 'Fès', '30000', NULL, DATE_SUB(NOW(), INTERVAL 2 DAY)),
('GA-2026-0307', (SELECT id FROM customers WHERE email='rachid.b@email.com'), 'delivered', 'cmi', 'paid', 359.00, 30.00, 0, 389.00, 'Rachid Bouhssini', 'rachid.b@email.com', '+212666789012', '7 boulevard Anfa', 'Casablanca', '20100', NULL, DATE_SUB(NOW(), INTERVAL 3 DAY)),
('GA-2026-0306', (SELECT id FROM customers WHERE email='nadia.a@email.com'), 'delivered', 'cod', 'paid', 164.00, 30.00, 0, 194.00, 'Nadia Amrani', 'nadia.a@email.com', '+212667890123', '12 avenue du Prince Moulay Abdallah', 'Agadir', '80000', NULL, DATE_SUB(NOW(), INTERVAL 3 DAY)),
('GA-2026-0305', (SELECT id FROM customers WHERE email='karim.e@email.com'), 'delivered', 'cmi', 'paid', 694.00, 30.00, 0, 724.00, 'Karim El Idrissi', 'karim.e@email.com', '+212668901234', '34 rue Bab Marrakech', 'Casablanca', '20100', NULL, DATE_SUB(NOW(), INTERVAL 4 DAY));

-- =====================================================================
-- Sample order items (for order GA-2026-0312)
-- =====================================================================
INSERT INTO order_items (order_id, product_id, product_name, product_sku, product_image, variant, unit_price, quantity, total) VALUES
((SELECT id FROM orders WHERE order_number='GA-2026-0312'),
  (SELECT id FROM products WHERE slug='huile-argan-pure-100ml'),
  'Huile d''argan pure 100ml', 'ARG-100-COSM',
  'https://images.unsplash.com/photo-1608571423902-eed4a5ad8108?w=200&q=80',
  '100ml · Cosmétique', 129.00, 1, 129.00),
((SELECT id FROM orders WHERE order_number='GA-2026-0312'),
  (SELECT id FROM products WHERE slug='hydrolat-rose-damascena-250ml'),
  'Hydrolat de rose damascène 250ml', 'HYD-ROS-250',
  'https://images.unsplash.com/photo-1556228841-a3c527ebefe5?w=200&q=80',
  '250ml', 65.00, 2, 130.00),
((SELECT id FROM orders WHERE order_number='GA-2026-0312'),
  (SELECT id FROM products WHERE slug='savon-noir-beldi-200g'),
  'Savon noir Beldi à l''eucalyptus 200g', 'SAV-BLD-200',
  'https://images.unsplash.com/photo-1612871689353-cccf581d667b?w=200&q=80',
  '200g · Eucalyptus', 59.00, 1, 59.00);

INSERT INTO order_items (order_id, product_id, product_name, product_sku, product_image, unit_price, quantity, total) VALUES
((SELECT id FROM orders WHERE order_number='GA-2026-0311'),
  (SELECT id FROM products WHERE slug='huile-essentielle-romarin-30ml'),
  'Huile essentielle de romarin 30ml', 'HE-ROM-30',
  'https://images.unsplash.com/photo-1608248543803-ba4f8c70ae0b?w=200&q=80',
  89.00, 1, 89.00);

-- =====================================================================
-- Order events (timeline for #GA-2026-0312)
-- =====================================================================
INSERT INTO order_events (order_id, event_type, description, created_by, created_at) VALUES
((SELECT id FROM orders WHERE order_number='GA-2026-0312'), 'created', 'Commande créée', 'client', NOW()),
((SELECT id FROM orders WHERE order_number='GA-2026-0312'), 'payment_initiated', 'Paiement initié · CMI · en attente de confirmation', 'system', NOW());

-- =====================================================================
-- Newsletter
-- =====================================================================
INSERT INTO newsletter_subscribers (email, status, source) VALUES
('amina.b@email.com', 'subscribed', 'checkout'),
('youssef.a@email.com', 'subscribed', 'footer'),
('sara.m@email.com', 'subscribed', 'exit-intent');

-- =====================================================================
-- Settings
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
