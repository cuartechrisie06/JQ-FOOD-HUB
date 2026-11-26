# JQ Food Hub - UI improvement changes

How to apply
1. Copy these files over your project (same paths). Nothing else in your project was touched.
2. Delete `resources/views/welcome.blade.php` (unused Laravel default).
3. Rebuild assets:  `npm run build`   (public/build is stale until you do)
4. Optional: `npm uninstall tailwindcss @tailwindcss/vite` (Tailwind was installed but never used).
5. Optional: add payment details to `.env` (see `.env.example`) so checkout can show them:
   JQ_EWALLET_NUMBER, JQ_EWALLET_ACCOUNT_NAME, JQ_BANK_NAME, JQ_BANK_ACCOUNT_NUMBER, JQ_BANK_ACCOUNT_NAME
6. Run `php artisan test`. Two assertions were updated for the new peso display:
   tests/Feature/PublicCatalogTest.php and tests/Feature/CustomerOrderingTest.php

I could not run PHP in my environment, so the Blade/PHP changes were checked by reading
and by syntax/balance checks only. Please run the test suite and click through the pages.

## Fixed bugs
- Admin mobile menu button did nothing (targeted a missing element). Sidebar is now a working off-canvas drawer.
- Undefined classes now defined: fw-extrabold, fw-black, stat-icon, hover-red, image-container-overflow, leading-tight, tracking-wider.
- Status badge colors now pass WCAG AA (were 2.0-3.2:1, now 6:1+).
- logo.png shrunk from 1024px / 488 KB to 192px / 24 KB.
- Bootstrap Icons + fonts no longer loaded twice; unused Tailwind plugin removed from vite.config.js.

## Design consistency
- Bold black-border/hard-shadow style kept only on the home hero; all other cards use one soft card style.
- Button hierarchy: btn-jq (primary) > btn-outline-dark (secondary); btn-jq-dark on yellow; btn-jq-gold on dark only.
  Buttons use Bootstrap CSS variables, so hover/active/disabled/focus work without !important.
- Peso sign everywhere; shared status-badge partial; inline styles moved to classes.
- Admin topbar is white with a yellow rule (calmer than yellow slab + black sidebar).

## Features / UX
- Home: real top-selling product (ranked by units sold, cancelled excluded) with its photo; no hard-coded price.
- Catalog: category chips, auto-submitting filters, result count, labeled "Add to cart" buttons, 4:3 photos.
- Product page: quantity stepper; removed unverifiable "Verified Hygiene / Authentic Quality" claims.
- Cart: +/- steppers that update automatically; one responsive list (no sideways scroll on phones).
- Checkout: delivery address hidden for pickup; payment instructions per method; readable earliest date;
  double-submit protection.
- Catering: selecting a date scrolls to the form; live guest-capacity hint for the chosen package;
  event-type suggestions; "Log in to book" on packages for guests.
- Navigation: About/Contact moved to footer; "Book Catering" shown to customers only (no more dead-end to login).
- Login returns the guest to the page they came from.
- Admin orders: Payment column, one-click "Mark <next status>", Cancel with confirmation,
  new Payment and Method filters. Dashboard KPI cards now link to the matching filtered list.
- Admin products: photo thumbnails, "No photo" warning, live preview and guidance in the form.
- Native confirm() popups replaced by one branded confirmation dialog.
- Branded standalone error pages: 403, 404, 419, 429, 500, 503.

## Accessibility / SEO
- Skip-to-content link, visible focus rings, reduced-motion support, aria-labels on icon-only buttons,
  decorative icons hidden from screen readers, aria-current on nav/chips, labelled form controls.
- Meta description, theme-color, Open Graph tags and LocalBusiness JSON-LD on public pages.
