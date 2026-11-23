# JQ Food Hub: Project Specification

A web-based product catalog, online ordering, and catering booking management system for **JQ Food Products** (Villa Patricia Subd., Mankilam, Tagum City). Owner: Raquel L. Tingson.

This file is the source of truth for the build. Follow table and column names exactly.

---

## 1. Overview

JQ Food Products is a home-based, family-run business (started 2018 as Deliciousnest, rebranded 2023, team of 10). It sells frozen and ready-to-cook lumpia (ubod, shanghai, cheesesticks), fresh lumpia (best seller), packed lunches, and catering packages priced about PHP 4,000 to 20,000. Customers are individuals, families, restaurants, and meat shops, mostly in Tagum City and the Davao Region.

**Current problems:** orders and inquiries come through Messenger, calls, texts, and group chats; records are in notebooks; stock and delivery schedules are tracked by hand; there is no view of which dates are already booked.

**Solution:** a responsive website with two sides:
- **Public/customer side:** catalog, packages, cart, checkout, catering booking calendar, order tracking, reviews.
- **Admin side:** dashboard, product/order/payment/booking management, availability calendar, daily prep sheet, reports, user management.

**Visual theme:** simple and clean. Yellow, black, white, red (matches the existing logo).

---

## 2. Tech Stack

| Layer | Technology |
|---|---|
| Front end | HTML5, CSS3, Bootstrap 5, JavaScript, FullCalendar, Chart.js |
| Back end | PHP 8 + Laravel, Laravel Mail |
| PDF / CSV | DomPDF, Laravel Excel |
| Database | MySQL 8 |
| Dev tools | XAMPP, Composer, Node.js + npm, VS Code, Git/GitHub |
| Deployment | Shared PHP/MySQL hosting + domain (to be arranged with client) |
| Email | SMTP service (Mailtrap for development) |

---

## 3. Roles and Permissions

| Role | Can do | Cannot do |
|---|---|---|
| **Guest** | Browse catalog, packages, About, Contact, approved reviews; search and filter | Order, book, upload payment, see any order data. Must register/log in to check out or book. |
| **Customer** | Manage own profile and address; cart and orders (pickup or delivery, date, time); upload payment proof; track status; submit catering requests; check availability; receive emails and .ics; write reviews for completed orders | See other customers' data; access admin pages; change prices, statuses, or calendar |
| **Admin / Staff** | Manage products, categories, packages, availability; review orders and update statuses; verify or reject payments; manage bookings and calendar; prep sheet, dashboard, reports; moderate reviews | Create, delete, or change roles of admin accounts; change owner-only settings |
| **Owner** | Everything Admin can do, plus create, edit, and deactivate admin/staff accounts, assign roles, set booking capacity per date | Owner account cannot be deleted by others |

Customers have a `customer_type` of `Individual` or `Business`. Passwords are hashed.

---

## 4. Major Features

| ID | Feature | Type |
|---|---|---|
| F1 | User registration, login, role-based access | Beyond CRUD |
| F2 | Product, category, and food package management | CRUD |
| F3 | Product catalog with search, filter, availability display | Beyond CRUD |
| F4 | Online ordering: cart, checkout, fulfillment scheduling | Beyond CRUD |
| F5 | Catering booking with capacity-based availability calendar | Beyond CRUD |
| F6 | Payment proof upload and verification workflow | Beyond CRUD |
| F7 | Order status tracking, notifications, calendar (.ics) reminders | Beyond CRUD |
| F8 | Admin dashboard, daily prep sheet, reports | Beyond CRUD |
| F9 | Customer reviews and feedback management | CRUD |

### F1. Auth and roles
Register with name, contact details, email, password. After login the menu shows only pages allowed for the role. Owner creates and deactivates staff accounts.

### F2. Product / category / package management
Admin adds, edits, and deactivates products, categories, and food packages (photo, price, unit, lead time, availability toggle). Changes appear immediately in the public catalog. Includes CSV import (see Section 8).

### F3. Catalog
Search by name, filter by category and availability (e.g. "Available today only", "Bulk order friendly"), sort. Product cards show photo, price, availability, Add to Cart.

### F4. Ordering
Customer reviews cart, picks Pickup or Delivery, date, time, delivery address (if delivery), and payment method (Cash / E-Wallet / Bank Transfer). System computes totals and down payment, validates schedule against product `lead_time_days` and calendar capacity, saves order as `Pending`, shows a reference number under My Orders, and sends an email confirmation.

### F5. Catering booking
Monthly calendar with colors: **Available** (green), **Fully booked** (red), **Not available/blocked** (gray). Customer picks an open date and submits: event type, number of guests, food package, event time, event location, special requests. Admin sets max bookings per date and blocks dates. A date becomes **fully booked** automatically when confirmed bookings reach `max_bookings`. Booking statuses: `Pending`, `Confirmed`, `Rejected`, `Cancelled`. A confirmed booking generates one order for payment.

### F6. Payment proof
Customer uploads an image and reference number on an order. Admin marks it `Verified` or `Rejected` (with a reason when rejected). A verified down payment confirms the order or booking. An order may have several payments (down payment, balance, full payment).

### F7. Status and notifications
Order statuses: `Pending`, `Confirmed`, `Preparing`, `Ready`, `Out for Delivery`, `Completed`, `Cancelled`. Each change is visible to the customer and creates a notification and email. Confirmation emails include an `.ics` file. Admins get alerts for new orders and pending payments.

### F8. Dashboard, prep sheet, reports
- **Dashboard:** New Orders, Pending Payments, Deliveries Today, Sales This Month, 7-day sales chart, recent orders.
- **Daily prep sheet:** totals quantity per product across all confirmed orders for a chosen date. Printable (DomPDF) and CSV export.
- **Reports:** sales and order counts for a period, best-selling products. Print and CSV export.

### F9. Reviews
Customers who completed an order submit a rating (1 to 5) and comment. Admin approves, hides, or deletes. Approved reviews show on the public Reviews page. Customers can edit their own pending review.

---

## 5. Business Rules

- **Down payment:** 50% of total for `Bulk`, `Packed Lunch`, and `Catering` orders. `Regular` orders have no required down payment unless stated otherwise.
- **Lead time:** requested date must be at least `lead_time_days` (of the longest-lead item in the cart) from today.
- **Capacity:** only bulk/catering bookings count toward `max_bookings`. Only `Confirmed` bookings count.
- **Price snapshot:** `order_items.unit_price` stores the price at time of order. Later price changes do not affect past orders.
- **Order confirmation:** admin confirms an order or booking only after the down payment is `Verified`. Confirming counts it against the calendar capacity.
- **Soft deactivation:** products, packages, and accounts are deactivated or archived rather than hard-deleted.
- **Ownership:** customers can only read and edit their own orders, payments, bookings, and reviews.

---

## 6. Pages

### Public / Customer
1. Home (hero, Order Now / Book Catering buttons, best sellers)
2. About Us / Our Story
3. Product Catalog
4. Product Details
5. Food Packages and Catering Services
6. Catering Booking Request (calendar + form) *Customer*
7. Contact and Location (map, Facebook, Messenger links)
8. Customer Reviews
9. Register / Login (with password reset)
10. Shopping Cart *Customer*
11. Checkout: Schedule and Payment *Customer*
12. Order Confirmation (reference no., Upload Payment, Add to Calendar) *Customer*
13. My Orders / Order Tracking *Customer*
14. My Account *Customer*

### Admin (Admin/Staff and Owner unless noted)
15. Dashboard
16. Manage Products and Categories
17. Manage Food Packages
18. Manage Orders
19. Payment Verification
20. Catering Bookings
21. Availability Calendar
22. Daily Prep Sheet
23. Reports
24. Manage Reviews
25. Manage Users *(Owner only)*

**Layout:** public pages share one top navigation bar (Home, Products, Packages, Catering, Reviews, Contact, Cart, Login/Account). Admin pages share one sidebar (Dashboard, Products, Packages, Orders, Payments, Catering Bookings, Calendar, Prep Sheet, Reports, Reviews, Users).

---

## 7. Database Schema (13 tables, MySQL 8)

### roles
| Field | Type | Key | Notes |
|---|---|---|---|
| role_id | INT AUTO_INCREMENT | PK | |
| role_name | VARCHAR(30) | | Customer, Admin, Owner |
| description | VARCHAR(150) | | |

### users
| Field | Type | Key | Notes |
|---|---|---|---|
| user_id | INT AUTO_INCREMENT | PK | |
| role_id | INT | FK roles.role_id | |
| full_name | VARCHAR(100) | | |
| email | VARCHAR(100) | | Unique, used to log in |
| phone | VARCHAR(20) | | |
| password_hash | VARCHAR(255) | | Hashed |
| address | VARCHAR(255) | | Default delivery address |
| customer_type | ENUM('Individual','Business') | | |
| business_name | VARCHAR(100) | | Nullable |
| status | ENUM('Active','Inactive') | | |
| created_at | DATETIME | | |

### categories
| Field | Type | Key |
|---|---|---|
| category_id | INT AUTO_INCREMENT | PK |
| category_name | VARCHAR(80) | |
| description | VARCHAR(200) | |

### products
| Field | Type | Key | Notes |
|---|---|---|---|
| product_id | INT AUTO_INCREMENT | PK | |
| category_id | INT | FK categories.category_id | |
| product_name | VARCHAR(100) | | |
| description | TEXT | | |
| unit | VARCHAR(20) | | pack, box, etc. |
| unit_price | DECIMAL(10,2) | | |
| stock_qty | INT | | |
| lead_time_days | INT | | Minimum days of notice |
| image_path | VARCHAR(255) | | |
| is_available | TINYINT(1) | | 1 orderable, 0 not |
| created_at | DATETIME | | |

### food_packages
| Field | Type | Key | Notes |
|---|---|---|---|
| package_id | INT AUTO_INCREMENT | PK | |
| package_name | VARCHAR(100) | | |
| description | TEXT | | |
| price | DECIMAL(10,2) | | |
| min_pax | INT | | |
| max_pax | INT | | |
| event_type | VARCHAR(50) | | Birthday, meeting, etc. |
| image_path | VARCHAR(255) | | |
| is_available | TINYINT(1) | | |

### package_items
| Field | Type | Key | Notes |
|---|---|---|---|
| package_item_id | INT AUTO_INCREMENT | PK | |
| package_id | INT | FK food_packages.package_id | |
| item_description | VARCHAR(150) | | |
| quantity | VARCHAR(50) | | |

### availability_calendar
| Field | Type | Key | Notes |
|---|---|---|---|
| calendar_id | INT AUTO_INCREMENT | PK | |
| slot_date | DATE | | Unique |
| max_bookings | INT | | Max confirmed bulk/catering bookings |
| is_blocked | TINYINT(1) | | 1 = not available |
| remarks | VARCHAR(150) | | |

### orders
| Field | Type | Key | Notes |
|---|---|---|---|
| order_id | INT AUTO_INCREMENT | PK | |
| user_id | INT | FK users.user_id | Customer |
| calendar_id | INT | FK availability_calendar.calendar_id | Fulfillment date |
| order_type | ENUM('Regular','Bulk','Packed Lunch','Catering') | | |
| fulfillment_method | ENUM('Pickup','Delivery') | | |
| fulfillment_time | TIME | | |
| delivery_address | VARCHAR(255) | | If delivery |
| total_amount | DECIMAL(10,2) | | |
| downpayment_amount | DECIMAL(10,2) | | |
| status | ENUM('Pending','Confirmed','Preparing','Ready','Out for Delivery','Completed','Cancelled') | | |
| notes | TEXT | | |
| created_at | DATETIME | | |

### order_items
| Field | Type | Key | Notes |
|---|---|---|---|
| order_item_id | INT AUTO_INCREMENT | PK | |
| order_id | INT | FK orders.order_id | |
| product_id | INT | FK products.product_id | Null if package |
| package_id | INT | FK food_packages.package_id | Null if product |
| quantity | INT | | |
| unit_price | DECIMAL(10,2) | | Price at time of order |
| subtotal | DECIMAL(10,2) | | quantity x unit_price |

### payments
| Field | Type | Key | Notes |
|---|---|---|---|
| payment_id | INT AUTO_INCREMENT | PK | |
| order_id | INT | FK orders.order_id | |
| verified_by | INT | FK users.user_id | Nullable |
| payment_method | ENUM('Cash','EWallet','Bank Transfer') | | |
| payment_type | ENUM('Down Payment','Balance','Full Payment') | | |
| amount | DECIMAL(10,2) | | |
| reference_no | VARCHAR(50) | | |
| proof_image | VARCHAR(255) | | |
| status | ENUM('Pending','Verified','Rejected') | | |
| submitted_at | DATETIME | | |
| verified_at | DATETIME | | Nullable |

### catering_bookings
| Field | Type | Key | Notes |
|---|---|---|---|
| booking_id | INT AUTO_INCREMENT | PK | |
| user_id | INT | FK users.user_id | Customer |
| package_id | INT | FK food_packages.package_id | |
| calendar_id | INT | FK availability_calendar.calendar_id | Event date |
| order_id | INT | FK orders.order_id | Set on confirmation, nullable |
| event_type | VARCHAR(50) | | |
| event_time | TIME | | |
| event_location | VARCHAR(255) | | |
| guest_count | INT | | |
| special_requests | TEXT | | |
| status | ENUM('Pending','Confirmed','Rejected','Cancelled') | | |
| created_at | DATETIME | | |

### reviews
| Field | Type | Key | Notes |
|---|---|---|---|
| review_id | INT AUTO_INCREMENT | PK | |
| user_id | INT | FK users.user_id | |
| order_id | INT | FK orders.order_id | One review per order |
| rating | TINYINT | | 1 to 5 |
| comment | TEXT | | |
| status | ENUM('Pending','Approved','Hidden') | | |
| created_at | DATETIME | | |

### notifications
| Field | Type | Key | Notes |
|---|---|---|---|
| notification_id | INT AUTO_INCREMENT | PK | |
| user_id | INT | FK users.user_id | Recipient |
| order_id | INT | FK orders.order_id | Nullable |
| type | VARCHAR(40) | | New Order, Status Change, etc. |
| message | VARCHAR(255) | | |
| is_read | TINYINT(1) | | |
| email_sent | TINYINT(1) | | |
| created_at | DATETIME | | |

### Relationships
| Parent | Child | Cardinality |
|---|---|---|
| roles | users | 1:M |
| users | orders | 1:M |
| availability_calendar | orders | 1:M |
| categories | products | 1:M |
| orders | order_items | 1:M |
| products | order_items | 1:M (optional) |
| food_packages | order_items | 1:M (optional) |
| food_packages | package_items | 1:M |
| orders | payments | 1:M |
| users | payments (verified_by) | 1:M |
| users | catering_bookings | 1:M |
| food_packages | catering_bookings | 1:M |
| availability_calendar | catering_bookings | 1:M |
| orders | catering_bookings | 1:1 (optional) |
| users | reviews | 1:M |
| orders | reviews | 1:1 (optional) |
| users | notifications | 1:M |
| orders | notifications | 1:M |

---

## 8. Initial CSV Import

**File:** `JQ_Initial_Products_Import_Template.csv` (UTF-8, comma-separated)

**Columns:** `category_name, product_name, description, unit, unit_price, lead_time_days, is_available, image_filename`

**Starter records (about 5):** fresh lumpia, lumpia ubod, lumpia shanghai, cheesesticks, packed lunch. Prices and lead times are blank, to be completed with the client. Food packages are entered through the admin form.

**Import screen:** inside Manage Products.

**Rules:**
- Create the category if it does not exist, then insert the product.
- Duplicates (same product name + category) are skipped and listed in the report.
- Reject rows with a missing product name, category, or unit.
- Reject invalid rows: non-numeric or negative price, negative lead time, `is_available` not 0 or 1.
- Report rejected rows with row numbers. Save valid rows; a bad row must not stop the rest of the import.

---

## 9. Automated Processes

- Order totals, 50% down payment, and balances
- Date validation against product lead time and calendar capacity
- Automatic "fully booked" marking at `max_bookings`
- Email confirmations and status notifications with `.ics` file
- New-order and pending-payment alerts for admins
- Daily prep sheet and sales/order reports

---

## 10. Out of Scope

- Payment gateway integration (payments are proof uploads verified manually)
- Real-time delivery tracking, courier integration, distance-based delivery fees
- Raw-material inventory, suppliers, accounting, payroll, employee scheduling
- Native mobile app (website is responsive instead)
- Two-way Facebook/Messenger integration (links only)

---

## 11. Suggested Build Phases

1. Migrations, models, seeders (13 tables; seed roles and one Owner account)
2. Auth, registration, role middleware (F1)
3. Product, category, package CRUD + CSV import (F2)
4. Public catalog with search and filters (F3)
5. Cart, checkout, scheduling logic (F4)
6. Availability calendar and catering bookings (F5)
7. Payment proof upload and verification (F6)
8. Order status, notifications, emails, .ics (F7)
9. Dashboard, prep sheet, reports (F8)
10. Reviews (F9), polish, deployment

---

## 12. Open Questions (decide before building)

- How is "fully booked" computed: confirmed bookings only, or confirmed + pending?
- Is lead time checked per item or by the longest-lead item in the cart?
- Do `Regular` orders require a down payment?
- Is `stock_qty` decremented automatically on confirmation, or manually by admin?
- Does a Package order go through `catering_bookings` or straight through checkout?
