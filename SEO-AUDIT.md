# GreenAmal — SEO Audit (May 2026)

**Status:** Auditing the live PHP/MySQL site at `http://localhost:8000` before going to Namecheap. Scoring framework: pass / partial / fail. Each finding lists severity, evidence, and the fix.

---

## Executive summary

| Area | Score | One-line |
|---|---|---|
| Page titles & descriptions | 🟡 Partial | Titles present, but several pages missing `$page_desc`; product titles too short |
| Meta robots / canonical | 🔴 Fail | No canonical tags, no robots meta — duplicate-content risk |
| Open Graph / Twitter | 🔴 Fail | Zero OG / Twitter card markup → social shares look broken |
| Structured data (Schema.org) | 🔴 Fail | No JSON-LD on any page → no rich snippets |
| `robots.txt` / `sitemap.xml` | 🔴 Fail | Neither file exists |
| URL structure | 🟡 Partial | `?slug=X` ugly query strings; `.php` extensions exposed |
| Heading hierarchy | 🟢 Pass | One H1 per page, sensible H2/H3 nesting |
| Image alt text | 🟡 Partial | 4 of 13 images have missing or empty `alt` |
| Image performance | 🔴 Fail | Banner JPEGs 437 KB – 1.3 MB at 4000×6000 px (way oversized); no `srcset`; almost no lazy loading |
| Mobile-friendly | 🟢 Pass | Responsive CSS in place, viewport meta correct |
| Page weight | 🟡 Partial | HTML+CSS+JS = ~92 KB on homepage (good); images are the problem |
| Lang / i18n | 🟡 Partial | `lang="fr"` set; no `hreflang` (will matter when AR/EN ship) |
| Internal linking | 🟡 Partial | Broken `href="#"` placeholders in footer (5 of them) |
| Speed / Core Web Vitals | ⚠ Untested | Need real Lighthouse run; oversized images will tank LCP |

**Bottom line:** the site is structurally solid but **invisible to Google as-is** because of missing canonical, sitemap, structured data, and OG tags. Plus the image weight will hurt rankings (Core Web Vitals). All fixable in 4–6 hours of focused work.

---

## 🔴 Critical findings (block launch)

### 1. No `robots.txt` or `sitemap.xml`

**Evidence:** `ls -la robots.txt sitemap.xml` returns nothing.

**Impact:** Crawlers don't know what to index, can't discover deep URLs efficiently. Search Console will refuse the site until you submit a sitemap.

**Fix:**
- Static `robots.txt` at root with `Sitemap:` directive
- Dynamic `sitemap.xml` PHP that lists: homepage, categories index, each category, each active product, each blog post (when added). Refresh on every product/category save.

### 2. No canonical URLs

**Evidence:** `grep -rn canonical` returns nothing.

**Impact:** `shop.php`, `shop.php?cat=savons`, `shop.php?sort=recent`, etc. all serve very similar content. Google sees duplicates → splits ranking signal.

**Fix:** Add `<link rel="canonical" href="...">` in `includes/header.php`. For shop with filters, canonicalize to the base shop URL or to the category-specific URL but never to the sort variants.

### 3. No Open Graph / Twitter Card meta

**Evidence:** `grep -rn "og:|twitter:"` returns nothing.

**Impact:** When someone shares a product on WhatsApp / Facebook / Instagram (your main traffic source for Moroccan e-commerce), the preview is blank. Click-through rate from social drops 30–60 % vs. proper OG.

**Fix:** Add to `includes/header.php`:
- `og:title`, `og:description`, `og:type`, `og:url`, `og:image`, `og:locale="fr_MA"`, `og:site_name`
- `twitter:card="summary_large_image"`, `twitter:title`, `twitter:description`, `twitter:image`
- For products: also `og:type="product"`, `product:price:amount`, `product:price:currency="MAD"`

### 4. No Schema.org structured data

**Evidence:** `grep "application/ld+json"` returns nothing.

**Impact:** No star ratings in Google results, no price/availability, no breadcrumbs, no "Coopérative" knowledge panel. Competitors with schema will out-rank a richer-content site.

**Fix:** Inline JSON-LD blocks per page type:
- **Homepage:** `Organization` + `WebSite` (with sitelinks search box)
- **Product page:** `Product` (name, image, description, sku, brand, offers with price/availability/priceCurrency, aggregateRating)
- **Category page:** `BreadcrumbList`
- **About:** `Organization` with founding date, num employees, address
- **Order confirmation:** `Order` (private, but useful)

This single fix typically delivers the largest SERP CTR improvement.

### 5. Oversized banner images

**Evidence:**
| File | Size | Dimensions |
|---|---|---|
| `couscous.jpg` | 1.3 MB | (likely 4000×6000) |
| `farine.jpg` | 1.1 MB | (likely 4000×6000) |
| `pam.jpg` | 1.0 MB | similar |
| 7 others | 433 KB – 902 KB | similar |

Sample check confirmed `huiles-essentielles.jpg` is **4000 × 6000 px** — 5 × bigger than needed.

**Impact:** Categories landing page = ~7 MB of image bytes on first paint. LCP > 4 s on 4G → fails Google's "Good" Core Web Vitals threshold → ranking penalty.

**Fix:**
- Resize all banners to 1600 × 900 (max) and 800 × 450 (mobile srcset)
- Convert to WebP (≈ 70 % smaller than JPEG at same quality)
- Use `<picture>` with `srcset` + `sizes` for responsive delivery
- Set explicit `width` and `height` on `<img>` tags to prevent layout shift (CLS)
- Same applies to product images once we have real ones

### 6. Missing `loading="lazy"` on most images

**Evidence:** `grep -c 'loading="lazy"'` returned 1 hit (only on `categories.php`).

**Impact:** Below-the-fold images load immediately, blocking LCP. Free win.

**Fix:** Add `loading="lazy"` to every `<img>` except the LCP element (hero image on homepage, main product image on PDP — those should be `fetchpriority="high"`).

---

## 🟡 Important findings (do before launch)

### 7. Page descriptions missing on several pages

| Page | Has `$page_desc` |
|---|---|
| index.php | ✅ |
| shop.php | ❌ uses default |
| product.php | ✅ uses DB `meta_description` |
| cart.php | ❌ |
| checkout.php | ❌ |
| about.php | ❌ |
| categories.php | ✅ |
| order-confirmation.php | ❌ |

**Fix:** Add `$page_desc` per page. Cart/checkout/order-confirmation should set `noindex` instead (those aren't ranking pages).

### 8. Ugly URLs

**Evidence:** `product.php?slug=huile-argan-pure-100ml`, `shop.php?cat=savons`.

**Impact:** Modest SEO penalty (Google handles query strings fine), bigger UX/sharing impact. `greenamal.com/huile-argan-pure-100ml` is more clickable than `greenamal.com/product.php?slug=...`.

**Fix:** `.htaccess` rewrite rules on Apache:
```
RewriteRule ^p/([a-z0-9-]+)/?$ product.php?slug=$1 [L,QSA]
RewriteRule ^c/([a-z0-9-]+)/?$ shop.php?cat=$1 [L,QSA]
```
Or even cleaner without the `/p/` `/c/` prefix if we accept the routing complexity.

### 9. Broken `href="#"` placeholder links in footer

**Evidence:** 5 instances of `href="#"` (Livraison & retours, Suivi de commande, FAQ, CGV, Politique de confidentialité, Contact).

**Impact:** Search engines and users follow these into a no-op. Wastes crawl budget and signals an unfinished site.

**Fix:** Either ship the actual pages (legal pages are required for CMI anyway — see ROADMAP) or remove the links until pages exist.

### 10. Image alt text — 4 of 13 missing or empty

**Evidence:**
- 2 images have no `alt=` attribute at all
- 2 images have `alt=""`

**Note:** `alt=""` is correct for purely decorative images. Need to audit which ones are decorative vs. content.

**Fix:** Decorative SVG icons can keep `alt=""` or use `aria-hidden="true"`. Product/category/hero images need real descriptive alt text including product name.

### 11. Hotlinked Unsplash images in product seed

**Evidence:** 19 Unsplash URLs in `sql/seed.sql` for product main images.

**Impact:**
- Unsplash can revoke / move images at any time → broken images
- No CDN optimisation control
- Slower (extra DNS lookup, no priority)

**Fix:** Once you import real product photos via the upload widget, all 12 seeded products will use local images. Already half-done — no migration needed, just upload the real photos.

### 12. No `<title>` distinction for sorted/filtered listings

**Evidence:** `shop.php?sort=sales` returns same `<title>Boutique — GreenAmal</title>` as the unsorted page.

**Impact:** Less critical than #2 (we'll canonicalise away), but if we do want to rank `shop.php?cat=savons`, the title should reflect "Savons artisanaux du Maroc".

**Fix:** Build dynamic titles: `Boutique — Savons artisanaux | GreenAmal`. Same for sorted listings (or `noindex` them).

---

## 🟢 What's already good

- Single `<h1>` per page, semantic H2/H3 below ✅
- `<html lang="fr">` set correctly ✅
- Viewport meta present, mobile-responsive layout ✅
- Page weight reasonable: HTML 28 KB, CSS 50 KB, JS 14 KB ✅
- Clean, semantic HTML — `<nav>`, `<header>`, `<footer>`, `<article>`, `<section>` used correctly ✅
- Slugs are SEO-friendly (`huile-argan-pure-100ml`, not `prod-1`) ✅
- French content with proper accents (encoding works) ✅
- HTTPS-ready (we'll get free Let's Encrypt SSL on Namecheap) ✅
- No JS-rendered content — everything is server-side, fully crawlable ✅

---

## What to do, in order

### Sprint 1 — fundamentals (~3 hours, biggest ROI)

1. Add Open Graph + Twitter Card meta to `includes/header.php` (configurable per page via `$og_image`, `$og_type`)
2. Add canonical URL helper + emit `<link rel="canonical">` per page
3. Generate `robots.txt` + dynamic `sitemap.xml`
4. Add Schema.org JSON-LD: `Organization` + `WebSite` on homepage, `Product` on product page, `BreadcrumbList` on shop & categories
5. Fix all 5 `href="#"` footer links (link to real pages or remove)
6. Add `noindex` to cart, checkout, order-confirmation, account pages

### Sprint 2 — performance (~2 hours)

7. Resize + WebP-convert all 10 category banners (offline batch — `cwebp` or `sips`)
8. Add `<picture>` + `srcset` + `loading="lazy"` + `width/height` on every `<img>`
9. Mark hero image and main product image as `fetchpriority="high"`
10. Set up image optimization in upload widget (auto-resize on upload to max 1600px)

### Sprint 3 — polish (~1 hour)

11. Pretty URLs via `.htaccess` (`/p/slug` + `/c/slug`)
12. Add `$page_desc` to remaining pages (or `noindex` for cart/checkout)
13. Dynamic titles for category & sorted listings
14. Audit alt text on every image — descriptive where it's content, empty where decorative

### Sprint 4 — analytics & verification (post-launch on Namecheap)

15. Google Search Console: verify domain, submit sitemap
16. Bing Webmaster Tools: same
17. GA4 install (already in admin/settings as "to do")
18. Run Lighthouse + PageSpeed Insights, fix any remaining issues
19. Test rich-snippet preview: <https://search.google.com/test/rich-results>
20. Test OG preview: <https://www.opengraph.xyz>

---

## Tracking

When we ship Sprint 1, this audit becomes the verification checklist. Re-run the same `grep` commands above — every 🔴 should turn 🟢.
