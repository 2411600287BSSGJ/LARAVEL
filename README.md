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

Administrators can manage products, manufacturers, equipment types, suppliers, and user roles. Staff can view inventory and record stock transactions.

## Inventory behavior

- A stock-out transaction cannot reduce available inventory below zero.
- When a product's quantity falls below its reorder level, the system creates one pending purchase order for the quantity needed to reach that level. Repeated checks do not duplicate a pending order.
- Pending purchase orders can be received by an administrator. Receiving adds the ordered quantity to inventory and records an inbound transaction.
- `php artisan inventory:check-reorder-points` checks all products. Laravel's scheduler runs this check hourly.
- Product, supplier, purchase-order, and transaction records use soft deletes.
- CSV downloads are available for products, brands, categories, suppliers, transactions, purchase orders, and (for administrators) users. Password data is never exported.

The migration `2026_11_16_000002_remove_legacy_fitness_tables` permanently drops the former workout, trainer, plan, activity-log, and notification tables. Back up any existing database data before applying it.

## Tests

```bash
php artisan test
```
