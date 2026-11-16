# Gym Inventory Management System

A Laravel application for tracking gym equipment and supplements, recording stock movements, and managing supplier replenishment.

## Requirements

- PHP 8.2+
- Composer
- Node.js and npm (for front-end assets)
- A database supported by Laravel

## Setup

```bash
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
npm run build
php artisan serve
```

Newly registered users receive the **Staff** role. Register the account that should manage the system, then grant administrator access from the command line:

```bash
php artisan inventory:make-admin admin@example.com
```

For a local demo database, `DatabaseSeeder` also creates a verified `admin@gym.test` account. Set `INVENTORY_ADMIN_PASSWORD` in `.env` before seeding to choose its password (for example, `password123` for local use only); if unset, the seeder generates a random password. The seeder does not replace an existing password unless this environment variable is set.

Administrators can manage products, manufacturers, equipment types, suppliers, and user roles. Staff can view inventory and record stock transactions.

## Email verification

The application sends verification links through SMTP. For Gmail, enable 2-Step Verification, create an App Password from your Google Account's **Security → App passwords** page, and put your full Gmail address and the 16-character app password (without spaces) in `MAIL_USERNAME` and `MAIL_PASSWORD` in `.env`. Use `smtp.gmail.com`, port `587`, and `MAIL_ENCRYPTION=tls`; `MAIL_FROM_ADDRESS` should match the authenticated Gmail address. Keep the real `.env` credentials private.

To test locally, set `APP_URL` to the URL where Laravel is running, clear cached configuration with `php artisan config:clear`, then run `php artisan serve`. Register using an inbox you can access, open the verification message (also check spam), and follow its link. Changing the email from Profile sends a new verification link and returns you to the verification prompt. Gmail SMTP can deliver to other valid recipient addresses, subject to Google's account policies and sending limits.

## Inventory behavior

- A stock-out transaction cannot reduce available inventory below zero.
- When a product's quantity is at or below its reorder level, the system creates one pending purchase order. Repeated checks do not duplicate a pending order.
- Transactions use `in` and `out` types. Pending purchase orders can be received by an administrator; receiving adds stock and records an `in` transaction.
- `php artisan inventory:check-reorder-points` checks all products. Laravel's scheduler runs this check hourly.
- Products, brands, categories, suppliers, purchase orders, transactions, and users use soft deletes. Admin and Staff permissions are enforced by policies.
- Registered users must verify their email before opening inventory pages. Set a working mail transport in `.env` to deliver verification links; Laravel's local `log` mailer writes them to the application log.
- CSV downloads are available for products, brands, categories, suppliers, transactions, purchase orders, and (for administrators) users. Password data is never exported.

The migration `2026_11_16_000002_remove_legacy_fitness_tables` permanently drops the former workout, trainer, plan, activity-log, and notification tables. Back up any existing database data before applying it.

## Tests

```bash
php artisan test
```
