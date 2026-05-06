# Admin Dashboard — Upgrade Plan

Roadmap to make `admin/index.php` feel professional, not amateur.

---

## Why it feels amateur right now

- CSS-drawn bar chart (no real chart library) — instantly screams "demo"
- Only one chart type. Pro dashboards mix lines, donuts, sparklines, heatmaps
- KPIs are static text. No mini trend, no animation, no comparison context
- No date range picker — hardcoded "30 derniers jours"
- No drill-down (no breakdowns by category / city / payment method)
- "Visiteurs en direct" is fake — pro dashboards either omit it or actually track it
- Trends say "↑ 12.4%" but don't show vs *what* — no comparison period
- Empty space when data is thin (no skeleton loaders, no anomaly badges)

---

## Libraries to use (all CDN, no build step, works on Namecheap)

| Library | Why | Size |
|---|---|---|
| **ApexCharts** | Best-looking defaults, free (MIT), easy to theme to our olive palette. Lines / bars / donuts / sparklines / heatmaps in one library. | 150 KB |
| **Litepicker** | Date range picker with presets (today / 7d / 30d / custom) and built-in comparison mode | 25 KB |
| **CountUp.js** | Animates KPI numbers from 0 → final value. Tiny detail, huge perceived-quality lift | 8 KB |
| **Lucide** (optional) | Successor to Feather Icons — more variety, same vibe as what we currently use | tree-shakeable |

Skipped: Chart.js (uglier defaults), ECharts (overkill for our scale), Tremor / Recharts (React-only).

---

## Tier 1 — biggest perceived-quality jump (~1–2 h)

- [ ] Replace the CSS bar chart with an **ApexCharts area chart** — gradient fill, smooth curves, hover tooltip showing exact revenue per day, optional comparison line for previous period
- [ ] **Sparklines inside each KPI card** — tiny 14-day trend line under each number (revenue, orders, AOV, customers)
- [ ] **CountUp animation** on KPI values — they tick up from 0 on page load
- [ ] **Date range picker** in the page header with presets + comparison toggle
- [ ] **Real comparison deltas** — when picker is set to "30j", auto-compare vs the 30j before. The "↑ 12.4%" badge becomes meaningful

## Tier 2 — adds analytical depth (~2–3 h)

- [ ] **Revenue-by-category donut** — which product family drives the business
- [ ] **Revenue-by-city horizontal bar chart** (top 10) — geographic insight
- [ ] **Revenue-by-payment-method donut** (CMI vs COD vs Virement) — operational insight
- [ ] **Conversion funnel** — Visiteurs → Vues produit → Ajouts panier → Checkout → Payé. Even if visitor tracking is stubbed for now, the structure is there
- [ ] **Goal tracker** — "Revenus du mois : 32k / 60k د.م. (53%)" with a progress arc

## Tier 3 — polish details that pros notice (~1–2 h)

- [ ] **Recent activity feed** — replaces fake "live visitors" with a real chronological feed: orders + new customers + reviews + low-stock alerts, mixed
- [ ] **Hour-of-day heatmap** — when orders actually come in (pulled from `orders.created_at`)
- [ ] **Top customers leaderboard** — top 5 by LTV
- [ ] **Anomaly badges** — "↓ 38% vs hier" in red on relevant cards when something drops sharply
- [ ] **Skeleton loaders** while charts initialize (~200ms perceived smoothness)

## Tier 4 — for later

- [ ] Live revenue ticker (would need a `live_sessions` table + polling)
- [ ] Cohort retention table
- [ ] Map of orders across Maroc (would need Leaflet + city coords)

---

## Things to NOT add

- ❌ A second sidebar of widgets — clutter
- ❌ Customizable widget drag-and-drop — overkill, breaks easily on mobile
- ❌ Dark mode — nice-to-have, not "professional" per se
- ❌ Notification center panel — bell icon in topbar is enough; full panel is bloat
- ❌ AI insights / anomaly summary — feels gimmicky unless the model is actually good

---

## Color / style guidance for charts (matching the brand)

- Primary lines/bars: olive `#3A5A40`
- Accent / comparison: saffron `#E0A458`
- Negative / loss: terracotta `#C8553D`
- Grid lines: very subtle `#EFF1ED`
- Tooltip background: ink `#1F2421`, text cream
- Donut: 5-color palette mixing the four brand colors + a soft neutral

Keeps the dashboard *visibly* part of the GreenAmal brand, not a generic "ApexCharts default" look.

---

## Recommended sequence

Ship **Tier 1 + Tier 2 first** (~5 h, ~9 widgets total). That's where 80% of the "feels professional" comes from. Tier 3 is icing.

Decision needed: green-light Tier 1 to start?
