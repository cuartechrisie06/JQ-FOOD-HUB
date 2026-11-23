# JQ Food Hub

JQ Food Hub is a Laravel application for product ordering, catering bookings, payment tracking, customer reviews, and staff operations.

## Requirements

- PHP 8.2 or newer with the extensions required by Laravel, Laravel Excel, and DomPDF (including `pdo_mysql`, `fileinfo`, `gd`, and `zip`)
- Composer
- MySQL 8
- Node.js and npm

## Local setup

From the project directory:

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
npm install
```

Set `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` in `.env`, then initialize the database and build the frontend:

```powershell
php artisan migrate --seed
php artisan storage:link
npm run build
php artisan serve
```

The local Owner account seeded by `DatabaseSeeder` defaults to `owner@jqfoodhub.local` with password `password`. Change the password immediately and configure a unique `JQ_OWNER_PASSWORD` before seeding a production database. The Owner email/name/phone/address can be configured with `JQ_OWNER_EMAIL`, `JQ_OWNER_NAME`, `JQ_OWNER_PHONE`, and `JQ_OWNER_ADDRESS`.

No Admin account is seeded by default; create one from **Admin → Users** while signed in as the Owner.

To run the application with the Vite development server and queue worker, use:

```powershell
composer run dev
```

### Class demo data

The optional demo seeder creates two demo customers, sample orders, a pending catering booking, and approved/pending reviews. Run it only against a local or class-demo database:

```powershell
php artisan db:seed --class=DemoSeeder
```

Demo customer accounts use `ava.demo@example.com` and `ben.demo@example.com`; both have the local-only password `password`. The seeder refuses to run when `APP_ENV=production`.

## Tests

Run the complete test suite:

```powershell
php artisan test
```

Build the production frontend assets:

```powershell
npm run build
```

## Mail and queues

Development defaults to the `log` mailer. Set `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, and `MAIL_FROM_ADDRESS` for the production mail provider. Do not commit credentials.

The default queue connection is `database`. Run a worker persistently in production:

```powershell
php artisan queue:work --tries=3
```

For shared hosting without a persistent worker, configure a cron job to run `php artisan queue:work --stop-when-empty --tries=3` regularly, or intentionally set `QUEUE_CONNECTION=sync` and accept that mail delivery runs during web requests. Monitor failures with `php artisan queue:failed`.

## Shared-hosting deployment

1. Deploy the application so the web server document root points to Laravel's `public` directory; do not expose the project root, `.env`, `vendor`, or storage directories directly.
2. Set production `.env` values: `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL`, database credentials, mail settings, `QUEUE_CONNECTION`, and a unique `JQ_OWNER_PASSWORD` of at least 12 characters. Keep `.env` outside version control and restrict its permissions.
3. Install production dependencies and build assets where the hosting environment permits:

   ```sh
   composer install --no-dev --optimize-autoloader
   npm ci
   npm run build
   ```

4. Ensure `storage` and `bootstrap/cache` are writable by the PHP process. Create the public storage link if the host supports symlinks:

   ```sh
   php artisan storage:link
   ```

   If symlinks are disabled, configure the host's supported public-upload arrangement without exposing private application files.
5. Run migrations and seed the role/Owner account only after setting the production Owner password:

   ```sh
   php artisan migrate --force
   php artisan db:seed --force
   ```

6. Configure a persistent queue worker or the queue cron job above. Verify mail delivery, file uploads, and storage permissions after deployment.
7. Cache the production configuration and views after `.env` is finalized:

   ```sh
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

   Clear these caches after changing deployment configuration.

## Feature overview

- F1 Authentication and role-based access
- F2 Product, category, package, and import management
- F3 Public catalog and business pages
- F4 Customer cart and online ordering
- F5 Catering bookings and availability calendar
- F6 Payment submissions and verification
- F7 Order status tracking and notifications
- F8 Dashboard, daily prep sheet, and reports
- F9 Customer reviews and moderation
