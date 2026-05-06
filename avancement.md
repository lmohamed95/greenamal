# GreenAmal — Avancement du projet

État du projet : **Sprint Pre-deploy terminé**. Le site est désormais lançable techniquement (auth client, pages légales, emails, sécurité, contenu, P3 bonus). Reste à faire : chargement du contenu blog/médias finaux, logo SVG dédié (à venir), puis Sprint Post-deploy (hosting, email prod, CMI).

**Mis à jour :** 6 mai 2026

---

## Stack & hosting

- **Backend :** PHP 8 + MySQL 8 (vanilla, pas de framework)
- **Frontend :** HTML / CSS / JavaScript vanilla
- **Hosting :** Namecheap shared hosting (cPanel + phpMyAdmin)
- **Domaine :** greenamal.com (Namecheap)
- **Local dev :** MAMP ou `php -S localhost:8000` + Homebrew MySQL
- **SSL :** Namecheap AutoSSL (Let's Encrypt, gratuit)
- **Paiement :** Paiement à la livraison (COD) uniquement pour le moment
- **Images :** Auto-redimensionnement + WebP via PHP GD

---

## ✅ Ce qui est fait

### Site public (storefront)

- [x] Page d'accueil — hero, USP strip, 7 catégories en grille, best-sellers, story, newsletter, blog teaser
- [x] Boutique (`shop.php`) — filtres par catégorie, prix, recherche, tri, pagination
- [x] Page produit (`product.php`) — galerie, variantes, qty selector, sticky CTA, urgency, trust grid, tabs (description / livraison / avis), cross-sells
- [x] Page panier (`cart.php`) — items, qty edit, free-shipping progress, coupon, summary
- [x] Page Catégories (`categories.php`) — landing avec banners haute qualité par catégorie
- [x] Checkout (`checkout.php`) — formulaire shipping, paiement à la livraison, validation, création commande
- [x] Confirmation de commande (`order-confirmation.php`)
- [x] À propos (`about.php`) — story, valeurs, processus
- [x] Contact (`contact.php`) — formulaire, coordonnées, WhatsApp CTA
- [x] Cart drawer slide-in depuis la droite (avec localStorage backend → maintenant session-based)
- [x] WhatsApp floating button + animation pulse
- [x] Exit-intent newsletter modal (−25 %, code `first25`)
- [x] Toast notifications
- [x] Free-shipping progress bar dynamique

### Admin CMS

- [x] Login (`admin/login.php`) — auth réelle avec bcrypt
- [x] Dashboard (`admin/index.php`) — KPI, chart, top produits, commandes récentes, stocks bas
- [x] Commandes (`admin/orders.php` + `order-detail.php`) — filtres par statut, mise à jour de statut, timeline, infos client
- [x] Produits (`admin/products.php` + `product-edit.php`) — liste filtrable, édition complète, statut, catégorie, tags, SEO
- [x] **Actions groupées sur les produits** — select-all + bulk activate / draft / archive / feature / unfeature / delete avec barre d'actions sticky et confirmations
- [x] **Catégories (`admin/categories.php` + `category-edit.php`)** — grille visuelle, édition avec image bannière, suppression conditionnelle
- [x] Clients (`admin/customers.php`) — segments (VIP, nouveaux, inactifs), KPI
- [x] **Coupons (`admin/coupons.php`)** — liste + KPI + **side panel de création/édition** (slide-in droite) avec :
  - segmented control type (% / DH / livraison) avec preview live
  - segmented control cible (boutique / produits / catégories)
  - chip-pickers filtrables pour produits & catégories (recherche + chips amovibles)
  - bouton « Générer » pour code aléatoire
  - conditions (commande min, limite totale, limite par client)
  - programmation (début / expiration) + statut
  - footer sticky, escape-to-close, click-outside-to-close
- [x] Paramètres (`admin/settings.php`) — store info, livraison, équipe, intégrations
- [x] Sidebar fixe + topbar + drawer mobile

### Upload & images

- [x] **Upload d'images drag-and-drop** dans l'admin (`admin/upload.php`)
- [x] Validation MIME / taille / `getimagesize()` pour anti-polyglot
- [x] Filename aléatoire (no path-traversal)
- [x] `.htaccess` qui bloque l'exécution PHP dans `/uploads/`
- [x] **Auto-redimensionnement** + génération WebP via GD (4 variantes par image)
- [x] Helper `picture_tag()` qui émet `<picture>` + `srcset` + `sizes` + `loading` + `fetchpriority`
- [x] Script CLI `bin/optimize-images.php` pour traiter le batch existant

### Base de données

- [x] Schéma complet (`sql/schema.sql`) — 15 tables (ajout `coupon_products`, `coupon_categories`)
- [x] Seed data (`sql/seed.sql`) — 10 catégories, 8 clients, 8 commandes, 5 coupons (produits remplacés par le vrai catalogue, voir ci-dessous)
- [x] Catégories alignées sur le shooting réel (Couscous, Eau Florale, Farine, Huile essentielle, Les poudres, Oil, Other, PAM, Packs, Savon — Videos exclu)
- [x] Schéma `coupons` enrichi : `applies_to` (all/products/categories), `starts_at`, `max_uses_per_customer`
- [x] Helpers `coupon_eligible_subtotal()` + `coupon_discount()` qui respectent les restrictions par produit / catégorie
- [x] `cart.php` et `checkout.php` mis à jour : validation stricte (status, fenêtre temporelle, max_uses, min_order, items éligibles), free-shipping correctement zéro le shipping

### Catalogue produits réel (mai 2026)

- [x] **Renommage des 159 photos du shooting** ("Al amal products shooting-2") d'après l'analyse visuelle du contenu de chaque image (lecture LLM des étiquettes)
- [x] **Pipeline d'import one-shot** (`bin/import-shooting.php` + `sql/products-shooting.sql`) :
  - copie + optimisation des 159 images dans `/assets/img/uploads/products/` (4 dérivés chacune = 636 fichiers)
  - groupement multi-shots → un produit avec galerie via `product_images`
  - DELETE des produits seed + INSERT du catalogue réel (transactionnel, FK-safe)
- [x] **89 produits réels** créés à partir du shooting, status `draft`, prix placeholder 100 DH (à éditer)
- [x] 70 images de galerie liées aux produits multi-shots (jusqu'à 5 photos/produit pour les packs)
- [x] Distribution : 17 PAM · 14 Huiles végétales · 11 Couscous · 9 Farines · 9 Savons · 9 Packs · 8 Huiles essentielles · 7 Eaux florales · 4 Poudres · 1 Divers

### SEO (Sprint 1 + 2 done)

- [x] `<title>` + `<meta description>` dynamiques par page
- [x] **Canonical URLs** sur toutes les pages, avec strip des params (sort, utm)
- [x] **Open Graph + Twitter Cards** — type, title, description, url, image, site_name, locale=fr_MA
- [x] **Schema.org JSON-LD** :
  - Organization + WebSite (avec sitelinks search) sur l'accueil
  - Product (avec offers, availability, aggregateRating) sur la page produit
  - BreadcrumbList sur boutique, catégories, produit
  - ContactPage sur contact
- [x] `robots.txt` + sitemap dynamique (`sitemap.php`)
- [x] `noindex` sur cart, checkout, order-confirmation, sorted listings
- [x] Tous les `href="#"` cassés du footer remplacés par de vrais liens
- [x] Lang `fr-MA`, charset UTF-8, viewport meta correct
- [x] Single H1 par page, hiérarchie H2/H3 propre

### Performance (Sprint 2)

- [x] Toutes les bannières resized de 4000×6000 → 1600×*
- [x] Conversion JPEG → WebP (quality 78)
- [x] Variantes mobile (800px) générées
- [x] `<picture>` + `srcset` + `sizes` partout
- [x] `loading="lazy"` sur tout le below-the-fold
- [x] `fetchpriority="high"` sur les LCP candidates (hero, PDP main image, premier banner catégorie)
- [x] `width` / `height` partout (CLS-safe)
- [x] **Résultat : page Catégories 7.77 MB → 856 KB desktop / 296 KB mobile (89-96 % de gain)**

### Responsive

- [x] Storefront responsive jusqu'à 320 px
- [x] Admin avec drawer sidebar mobile
- [x] Cart drawer plein écran sur mobile
- [x] Tableaux admin scrollables horizontalement sur mobile

### Documentation

- [x] `SETUP.md` — installation locale (MAMP + Homebrew)
- [x] `ROADMAP.md` — features manquantes pour v1
- [x] `SEO-AUDIT.md` — audit complet avec preuves
- [x] `DASHBOARD-TODO.md` — plan d'amélioration du dashboard
- [x] `avancement.md` — ce document

---

## 🔄 En cours

- Cropping d'images dans l'admin (frame selector)
- Preview d'upload plus compact
- Paiement COD uniquement (CMI / virement masqués pour l'instant)
- **Édition manuelle des 89 produits importés** : prix réels, descriptions FR/AR, stock, statut → active

---

## 🚧 À faire — par priorité

> **Plan en deux phases** : tout ce qui peut/doit être fait **en local avant déploiement** (Sprint Pre-deploy), puis tout ce qui **nécessite le serveur de prod / domaine / SSL / compte marchand** (Sprint Post-deploy).

---

## 🛠 Sprint Pre-deploy — TERMINÉ ✅

### P0 — Bloqueurs absolus

- [x] **Contenu produits** : 89 produits réels en SQL (`bin/build-product-content.php` → `sql/products-content.sql`)
  - [x] Prix réels (29-499 DH selon catégorie, moyenne 82 DH)
  - [x] Descriptions courtes + longues SEO-friendly en FR pour chaque produit
  - [x] Meta-titles + meta-descriptions générés automatiquement
  - [x] Stock = 30 par défaut
  - [x] Statut `draft` → `active` (visibles sur le shop)
  - [x] Galeries multi-images conservées
- [x] **Pages légales** (loi 09-08 + obligations e-commerce Maroc)
  - [x] CGV (`cgv.php`)
  - [x] Politique de confidentialité (`privacy.php`)
  - [x] Mentions légales (`mentions.php`)
  - [x] Politique de retour (`returns.php`)
  - [x] Politique cookies (`cookies.php`)
- [x] **Bandeau cookies** + consentement (cookie `ga_cookie_consent`, bouton "Préférences cookies" dans le footer pour rouvrir)
- [x] **Auth client complète**
  - [x] `login.php`, `register.php`
  - [x] `forgot-password.php` (token 1 h), `reset-password.php`
  - [x] `account.php` (3 onglets : Commandes / Profil & adresse / Mot de passe)
  - [x] `logout.php`
  - [x] Helpers `customer_user()`, `customer_login()`, `customer_logout()`, `customer_require_login()` dans `includes/helpers.php`
  - [x] Schéma : `customers` enrichi (`reset_token`, `reset_token_expires_at`, `last_login_at`, `email_verified`, `address`, `postcode`)
- [x] **Emails transactionnels** (`includes/mail.php`)
  - [x] Helper `send_mail()` avec layout HTML responsive (mode debug = log dans `/tmp/greenamal-mail.log`)
  - [x] Confirmation de commande au client + notification admin (déclenchés dans `checkout.php`)
  - [x] Mail status change (processing/shipped/delivered/cancelled) déclenché dans `admin/order-detail.php`
  - [x] Mail réinitialisation de mot de passe
  - [x] Mail de bienvenue au register
- [x] **Page 404** (`404.php`) + page 500 (`500.php`) + ErrorDocument dans `.htaccess`
- [x] **Recherche fonctionnelle** (`search.php`) avec filtre LIKE multi-colonnes (name, descriptions, tags, sku)
- [x] **FAQ** (`faq.php`) — 6 sections, FAQPage JSON-LD inclus
- [x] **Stock-zero guard** au checkout : refuse les items avec `stock <= 0` ou qty > stock, et décrémente le stock à la création de commande
- [x] `cart_add()` respecte le stock max et refuse les produits inactifs/épuisés

### P1 — Sécurité

- [x] **CSRF tokens** sur tous les formulaires admin et storefront critiques
  - admin : login, products (bulk), product-edit, coupons (save+delete+bulk), order-detail (status), settings, category-edit
  - storefront : cart (coupon), checkout, login, register, forgot-password, reset-password, account
- [x] Helpers `csrf_token()`, `csrf_field()`, `csrf_verify()` dans `includes/helpers.php`
- [x] **Rate limiting** :
  - 5 tentatives / 60 s sur `client_login`, `client_register`
  - 5 tentatives / 5 min sur `admin_login`
  - 3 tentatives / 10 min sur `forgot_pw`
  - 8 tentatives / 60 s sur `coupon_apply`
- [x] `includes/config.local.example.php` créé (template à dupliquer en prod)
- [x] `APP_DEBUG=false` ne fuite aucune erreur PDO (déjà couvert dans `db.php`)

### P2 — Pré-déploiement & finitions

- [ ] **Logo SVG/PNG dédié** — utilisateur l'enverra prochainement
- [ ] **OG image dédiée** 1200×630 — à régler quand le logo arrive
- [ ] **Audit alt text** complet — à finir
- [x] **Pretty URLs** via `.htaccess` (`/p/<slug>`, `/c/<slug>`, `/post/<slug>`, `/blog`)
- [x] **Headers sécurité** dans `.htaccess` (X-Content-Type-Options, X-Frame-Options, Referrer-Policy, Permissions-Policy)
- [x] **Compression gzip + cache navigateur** configurés dans `.htaccess`
- [x] **Force-HTTPS** dans `.htaccess` (commenté, à activer après déploiement)
- [x] **Clean prod SQL dump** (`sql/seed-prod.sql`) — categories + welcome coupon + admin + settings, **sans** produits/clients/commandes de démo
- [x] Pipeline d'import documenté : `schema.sql` → `seed-prod.sql` → `products-shooting.sql` → `products-content.sql`

### P3 — UX bonus

- [x] **FAQ** (`faq.php`)
- [x] **Wishlist** (table `wishlists` + `wishlist.php` + `api/wishlist.php` toggle)
- [x] **Blog index + article** (`blog.php` + `blog-post.php`, table `posts`, 2 articles seed dont un sur l'huile d'argan et un sur la coopérative)
- [x] **Mobile sticky add-to-cart** sur la PDP (`pdp-sticky-mobile`, visible uniquement < 768 px, respect `safe-area-inset-bottom`)

---

## ☁️ Sprint Post-deploy — à faire **après** mise en ligne

> **Objectif :** infrastructure réelle, paiement, monitoring, déliverabilité email.

### D0 — Mise en ligne (la première fois)

- [ ] Acheter / configurer un plan Namecheap (Stellar Plus recommandé pour cron + SSH)
- [ ] Pointer le DNS de **greenamal.com** vers le hosting
- [ ] **AutoSSL Let's Encrypt** (cPanel → SSL/TLS Status)
- [ ] Créer la base MySQL via cPanel + import `schema.sql`
- [ ] Importer le `sql/products-shooting.sql` (catalogue réel uniquement, sans le seed démo)
- [ ] Uploader `/assets/img/uploads/products/` (~636 fichiers) via FTP / File Manager
- [ ] Créer `includes/config.local.php` avec les credentials de prod + `APP_ENV='production'` + `SITE_URL='https://greenamal.com'`
- [ ] Tourner un compte admin réel (changer mot de passe, supprimer `admin@greenamal.com / admin123`)

### D1 — Email transactionnel en prod

- [ ] Configurer SPF + DKIM + DMARC pour `greenamal.com` (cPanel Email Deliverability)
- [ ] Tester `mail()` natif Namecheap, sinon basculer sur **Resend** (gratuit jusqu'à 3 000/mois) ou Brevo
- [ ] Vérifier la délivrabilité (`mail-tester.com` → score ≥ 9/10)
- [ ] Tester chaque mail transactionnel en bout-en-bout

### D2 — Smoke tests prod

- [ ] **Commande test réelle de bout en bout** (ajout panier → checkout → confirmation → email reçu → admin notifié → marquer expédié → mail expédition)
- [ ] Test connexion / inscription / mot de passe oublié client
- [ ] Test coupon avec restriction produit + restriction catégorie
- [ ] Test sur mobile réel (iOS Safari + Android Chrome)
- [ ] **Lighthouse + PageSpeed Insights** en prod (visé : 90+ desktop, 80+ mobile)

### D3 — Monitoring & obs

- [ ] **Search Console** + sitemap soumis
- [ ] **Google Analytics 4** ou Plausible
- [ ] **UptimeRobot** (ping toutes les 5 min, alerte SMS/email)
- [ ] Activity logs admin (qui a modifié quoi) — table `admin_logs`

### D4 — Ops & cron (cPanel)

- [ ] Cron `mysqldump` quotidien → archive sur disque + copie distante (Drive/B2)
- [ ] Cron de cleanup paniers abandonnés (> 30 jours)
- [ ] Cron de transition automatique `coupons.status` (`scheduled` → `active` → `expired`) selon dates
- [ ] Procédure documentée de restauration DB en cas d'incident

### D5 — Paiement en ligne (à démarrer en parallèle dès maintenant — délai admin)

- [ ] **Demander compte marchand CMI** dès maintenant (paperasse 3-6 semaines)
- [ ] Intégration CMI (3-D Secure via formulaire signé HMAC-SHA256)
- [ ] Tests en **sandbox CMI** avant prod
- [ ] Activer le choix CMI dans le checkout (actuellement masqué)
- [ ] Page de retour / callback paiement

---

## 📊 Données utiles

| Élément | Valeur |
|---|---|
| Code promo bienvenue | `first25` (−25 %) |
| Seuil livraison gratuite | 350 د.م. |
| Frais de livraison standard | 30 د.م. |
| WhatsApp coopérative | +212 627-634472 |
| Email contact | contact@greenamal.com |
| Catégories actives | 10 |
| Produits en base | 89 (drafts, prix placeholder 100 DH — issus du shooting réel) |
| Images produits optimisées | 159 originales × 4 dérivés = 636 fichiers |
| Compte admin local | admin@greenamal.com / admin123 |

---

## 🗂 Structure du repo

```
GREENAMALTEST/
├── index.php / shop.php / product.php / categories.php / cart.php /
│   checkout.php / order-confirmation.php / about.php / contact.php
├── sitemap.php
├── robots.txt
│
├── includes/
│   ├── config.php   ← credentials + URL site
│   ├── db.php       ← PDO wrapper
│   ├── helpers.php  ← e(), price(), cart_*, SEO helpers
│   ├── image.php    ← image_make_responsive(), picture_tag()
│   ├── auth.php     ← admin auth
│   ├── header.php / footer.php
│
├── api/             ← AJAX endpoints (cart, newsletter)
├── admin/           ← Admin CMS (10 pages + upload + assets)
├── assets/          ← CSS, JS, images
│   └── img/
│       ├── categories/  ← bannières (originale + WebP + mobile)
│       └── uploads/     ← uploads admin (categories/, products/)
├── sql/             ← schema.sql + seed.sql
├── bin/             ← scripts CLI (optimize-images.php)
├── _maquette-html-backup/  ← maquette HTML originale (référence)
└── *.md             ← docs (SETUP, ROADMAP, SEO-AUDIT, DASHBOARD-TODO, avancement)
```

---

## 🎯 Plan de marche concret

**Pre-deploy : ✅ TERMINÉ**
- Code prêt à être déployé. Le site fonctionne en local de bout en bout : un visiteur peut s'inscrire, parcourir le catalogue de 89 produits, ajouter au panier, commander en COD, recevoir une confirmation par email (loggée dans `/tmp/greenamal-mail.log` en mode debug), suivre sa commande dans son compte.

**Avant déploiement : 2 derniers items en attente**
1. Réception du **logo SVG/PNG dédié** (utilisateur l'enverra) → finir P2 (logo + OG image 1200×630).
2. **Audit alt text** final.

**En parallèle, à lancer dès aujourd'hui :**
- D5 — demande de compte marchand **CMI** (3-6 semaines de délai administratif).

**Le jour du déploiement (1 journée) :**
- D0 mise en ligne sur Namecheap + D1 emails (SPF/DKIM/DMARC) + D2 smoke-tests bout-en-bout.

**Semaine 2 post-deploy :**
- D3 (Search Console + GA4 + UptimeRobot) + D4 (cron backup mysqldump + cleanup paniers).
- L'intégration CMI arrive quand le compte marchand est validé.
