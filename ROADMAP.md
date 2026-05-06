# GreenAmal — Roadmap & Missing Pieces

Track of what's built, what's missing, and what's planned to call this site production-ready.

---

## Hosting & stack (locked in)

- **Hosting:** Namecheap shared hosting (LAMP stack, cPanel)
- **Backend:** PHP 8 + MySQL 8
- **Frontend:** static HTML/CSS/JS maquette → converted to PHP templates
- **Domain:** Namecheap (already owned)
- **SSL:** Namecheap AutoSSL (free Let's Encrypt)
- **Payments:** CMI (Maroc) + COD
- **Email:** PHP `mail()` via Namecheap → upgrade to Postmark/Resend if deliverability issues

## Built so far

### Storefront (`/`)
- [x] Design system (CSS variables, typography, components)
- [x] Homepage (`index.html`)
- [x] Shop / category listing (`shop.html`)
- [x] Product detail page (`product.html`)
- [x] Cart page (`cart.html`)
- [x] About / story page (`about.html`)
- [x] Slide-in cart drawer (with localStorage state)
- [x] Free-shipping progress bar
- [x] Exit-intent newsletter modal
- [x] WhatsApp floating button
- [x] Toast notifications

### Admin CMS (`/admin/`)
- [x] Admin design system (sidebar layout, tables, KPI cards, forms)
- [x] Login screen (`admin/login.html`)
- [x] Dashboard with KPIs, chart, top products, recent orders, low-stock alerts, live visitors (`admin/index.html`)
- [x] Orders list with status tabs + filters (`admin/orders.html`)
- [x] Order detail with items, timeline, customer, shipping, payment (`admin/order-detail.html`)
- [x] Products list with status/category filters (`admin/products.html`)
- [x] Product edit page with images, variants, pricing, SEO (`admin/product-edit.html`)
- [x] Customers list with KPIs and segments (`admin/customers.html`)
- [x] Coupons list with usage stats (`admin/coupons.html`)
- [x] Settings (general, shipping, payments, languages, team, integrations) (`admin/settings.html`)

---

## Critical — blocks launch

### Pages
- [ ] **Checkout flow** (shipping → billing → payment → review) — `checkout.html`
- [ ] **Order confirmation / thank you** — `order-confirmation.html`
- [ ] **Login** — `login.html`
- [ ] **Register** — `register.html`
- [ ] **Password reset** flow (request + new-password pages)
- [ ] **Account dashboard** — `account.html`
- [ ] **Order history** — `account-orders.html`
- [ ] **Order detail** — `account-order-detail.html`
- [ ] **Address book** — `account-addresses.html`
- [ ] **Search results** — `search.html`
- [ ] **404 page** — `404.html`
- [ ] **500 / generic error** — `error.html`
- [ ] **Contact page** with form, map, hours — `contact.html`
- [ ] **Shipping & returns** info — `shipping.html`

### Legal (required by loi 09-08 + CMI payment audit)
- [ ] **CGV** (Conditions générales de vente) — `terms.html`
- [ ] **Politique de confidentialité** — `privacy.html`
- [ ] **Mentions légales** — `legal.html`
- [ ] **Politique de retours** — `returns.html`
- [ ] **Politique de cookies** — `cookies.html`

### Compliance
- [ ] Cookie consent banner (GDPR if targeting EU diaspora)

---

## Important — v1.1

### Pages
- [ ] **FAQ** — `faq.html`
- [ ] **Wishlist** — `wishlist.html` (heart icons currently lead nowhere)
- [ ] **Blog index** — `blog.html`
- [ ] **Blog single post** — `blog-post.html`
- [ ] **Category landing pages** (one per category, SEO-rich):
  - [ ] `category-huiles-essentielles.html`
  - [ ] `category-huiles-vegetales.html`
  - [ ] `category-plantes.html`
  - [ ] `category-cosmetiques.html`
  - [ ] `category-hydrolats.html`
  - [ ] `category-couscous.html`
  - [ ] `category-divers.html`
- [ ] **Newsletter confirmation** page (double opt-in)

### UX features
- [ ] Mobile sticky add-to-cart bar on PDP
- [ ] Mobile filter drawer (replace inline sidebar)
- [ ] Recently viewed products strip
- [ ] Bottom navigation bar (mobile)
- [ ] Quick view modal (currently the eye icon does nothing)

---

## Strategic — phase 2

- [ ] **Wholesale / B2B inquiry** — `wholesale.html` (restaurants, spas)
- [ ] **Loyalty / referral** program — `rewards.html`
- [ ] **Gift cards** (purchase + redeem) — `gift-cards.html`
- [ ] **Press / media kit** — `press.html`
- [ ] **Coffrets cadeaux / bundles** — `gift-sets.html`
- [ ] Multi-language (FR / AR / EN) — RTL support for Arabic
- [ ] Multi-currency (DH / EUR / USD)
- [ ] Stock notification ("Notify me when available")
- [ ] Product compare page
- [ ] Reviews submission flow (with photo upload)

---

## Technical (not pages, but production blockers)

### Backend / data
- [ ] Real backend integration (currently localStorage-only)
- [ ] Product catalog CMS (cooperative needs self-service)
- [ ] Order management dashboard (admin)
- [ ] Inventory / stock tracking

### Payments
- [ ] CMI gateway integration (Maroc)
- [ ] Stripe / PayPal for international
- [ ] PayZone alternative
- [ ] COD (cash on delivery) workflow with delivery partner

### Emails (transactional)
- [ ] Order confirmation
- [ ] Order shipped
- [ ] Order delivered
- [ ] Password reset
- [ ] Welcome email (with `first25` code)
- [ ] Abandoned cart recovery

### SEO / discoverability
- [ ] `sitemap.xml`
- [ ] `robots.txt`
- [ ] Schema.org structured data:
  - [ ] `Product`
  - [ ] `Review` / `AggregateRating`
  - [ ] `Organization`
  - [ ] `BreadcrumbList`
  - [ ] `FAQPage`
- [ ] Open Graph + Twitter Card meta on every page
- [ ] Canonical URLs
- [ ] Hreflang (when multi-language ships)

### Performance
- [ ] Image optimization pipeline (WebP/AVIF, responsive `srcset`)
- [ ] Lazy loading on product grids
- [ ] Critical CSS inlining
- [ ] CDN for static assets

### Analytics & marketing
- [ ] GA4
- [ ] Meta Pixel (for retargeting ads)
- [ ] TikTok Pixel (Moroccan e-commerce trend)
- [ ] Hotjar / Microsoft Clarity (heatmaps)

### Accessibility
- [ ] WCAG AA audit (color contrast, focus states, keyboard nav)
- [ ] Screen reader testing
- [ ] Alt text audit on all product photos

---

## Suggested next sprint

To get to "looks like a real shop" for stakeholder review:

1. Checkout flow (3 steps)
2. Order confirmation
3. Login / Register
4. Contact page
5. One legal page template (CGV) — copy-paste-able for the others
6. 404 page

---

## Notes

- Promo bar code: `first25` (−25% first purchase)
- Free-shipping threshold: 350 د.م.
- WhatsApp: +212 627-634472
- Cooperative: Al Amal, Azrou, Maroc
- Certification: ONSSA
