# JQ Food Hub - update

Copy these files over your project (same paths), then:

    php artisan storage:link
    npm run build
    php artisan test

Delete these now-unused files (optional):
    resources/views/catering/index.blade.php
    resources/views/catalog/packages.blade.php

Changes
- Catering is booked from the menu's Food Packages / Budget / Snacks / Buffet tabs ("Book this package").
  /catering and /packages now redirect to the menu. The calendar and booking form sit under the package cards.
- My Orders shows catering requests (pending/rejected/cancelled) next to orders.
- Admin: one "Order Calendar" with regular, bulk, packed lunch and catering orders plus pending catering requests.
  Click a day to see its orders, set capacity or block the date.
- Admin Orders page shows catering requests awaiting review with Approve / Reject. The separate nav item is gone.
- Capacity (max per day) now counts active catering, bulk and packed-lunch orders; cancelling frees the slot.
  Regular orders show on the calendar but do not use capacity.
- Bug fix: a regular order used to create the date with capacity 0, making it look "Fully booked" for catering.
- Approving a catering request emails the customer the 50% down payment instructions.
- Payment proof upload / admin verify + reject, packed-lunch down payment fix, FAQ page (from the previous update).

Not run: no PHP here, so Blade/PHP was checked by reading only.

---
Catalog cleanup
- /products now redirects to /menu?tab=lumpias (search, category_id, sort and page are carried over). The product detail page /products/{product} is unchanged.
- All "Browse / Order / View all products" links now point at the menu's Lumpias tab.
- Removed unused views: catalog/index.blade.php, catalog/packages.blade.php, catering/index.blade.php.
- Behavior change: the menu only lists available products; the old /products page also listed unavailable ones.
- PublicCatalogTest now targets /menu and covers the /products redirect.

Category cleanup
- Menu > Lumpias: removed the category chip row and the category filter. Product cards and the product page no longer show the category badge/link.
- Admin: removed the Categories page (sidebar item, routes, CategoryController, CategoryRequest, admin/categories views). Dropped the category CRUD test.
- Kept the database `categories` table and the Category dropdown on the admin product form: checkout uses the "Packed Lunch"/"Packed Meals" category to apply the packed-lunch rules.
- Delete these (the zip cannot delete files):
    app/Http/Controllers/CategoryController.php
    app/Http/Requests/CategoryRequest.php
    resources/views/admin/categories/   (whole folder)
