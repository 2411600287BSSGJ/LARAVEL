# 🏋️ Gym Inventory Management System

A full-featured Laravel web application for tracking gym equipment and supplements, recording stock movements, managing supplier replenishment, and securing access with email OTP authentication.

---

## ✨ Features

### 🔐 Authentication & Security
- **Email OTP Login** — After entering valid credentials, a 6-digit one-time password is sent to the user's email via [Resend](https://resend.com). Login only completes after OTP verification.
- **Email OTP Registration** — New accounts are not created until the user verifies their email with a 6-digit OTP. Codes expire in 10 minutes.
- **Role-based access control** — `admin` and `staff` roles with different permissions.
- **Admin account protection** — Administrators cannot delete their own accounts (enforced at both controller and view level).

### 📦 Inventory & Procurement Management
- Full **CRUD** for products, brands, categories, suppliers, and equipment types.
- **Stock In / Stock Out / Adjustment** transactions with automatic quantity tracking.
- Stock-out transactions **cannot reduce inventory below zero**.
- **Manual & Automated Purchase Orders** — Create custom procurement orders or allow automatic creation when stock drops below the reorder level.
- **Receive & Cancel Purchase Orders** — Receiving automatically increments stock and records an inbound transaction; pending orders can be cancelled.
- **Printable Purchase Order Invoice** — Generate clean PO documents with vendor details, line items, and approval sign-off.
- **Supplier Profile Pages** — Detailed vendor profiles with contact info, fulfillment rates, supplied product catalog, and order history.
- **Product Barcode Labels** — Generate printable shelf and warehouse barcode labels with SKU and price tag.
- **Quick Action Buttons** — One-click Stock In, Stock Out, Reorder, and Barcode printing directly from the product page.
- Hourly reorder check via Laravel Scheduler: `php artisan inventory:check-reorder-points`.
- All key models use **soft deletes**.

### 📋 Audit Trail (CRUD, Export & Print)
- **Full CRUD** — Record (Create), view (Read), correct/adjust (Update), and delete/reverse (Delete) audit trail records.
- **Stock Reconciliation on Edit** — Modifying an audit record's quantity automatically recalculates and reconciles the product's live stock balance.
- **Filtered CSV Export** — One-click download of the complete or filtered audit trail with Philippine Peso (₱) valuations.
- **Printable Audit Ledger** — Clean landscape report layout designed for print or save-as-PDF with audit compliance sign-off blocks.
- **Printable Transaction Voucher** — Individual movement audit slips with reference numbers, staff attribution, and approval signature lines.

### 📧 Email Notifications & Alerts (Resend)
- Transactional email powered by [Resend](https://resend.com) (`resend/resend-laravel`).
- **Staff Movement Email Alerts** — Whenever a staff member encodes a **Stock In** or **Stock Out**, an automated notification email is immediately dispatched to **both the staff and all admin accounts** with product details, quantity moved, total value in ₱, updated stock level, and reference number.
- **Email OTP 2FA** — OTP emails sent on every login and registration attempt.
- **Low Stock Alerts** — Automated notifications dispatched when stock falls to or below reorder level.
- **Live stat cards** — Total Products, Low Stock, Out of Stock, Total Inventory Value (auto-refreshed via AJAX every 30 s).
- **Doughnut chart** — Stock status breakdown (In Stock / Low Stock / Out of Stock).
- **Bar chart** — Inventory value by category (₱ Philippine Peso).
- **Line chart** — Stock movements over the last 7 days.
- **Low stock alerts** — Bell notification badge in the navbar with real-time counts.

### 💱 Philippine Peso (₱) Pricing
- All prices displayed in **Philippine Peso (₱)** throughout the app — dashboard stat cards, product listings, detail pages, and API responses.

### 🪟 Glassmorphism UI
- All cards, panels, tables, modals, dropdowns, alerts, and form inputs use a **frosted glass effect** (`backdrop-filter: blur`) over the background image, keeping text fully readable.
- Smooth hover animations and transitions throughout.

### 📧 Email (Resend)
- Transactional email powered by [Resend](https://resend.com) (`resend/resend-laravel`).
- OTP emails sent on every login and registration attempt.

### 📄 Exports
- **CSV downloads** for products, brands, categories, suppliers, transactions, purchase orders, and (admin only) users. Password data is never exported.

### 👤 My Profile
- View and update profile information (name, email).
- Change password with current-password verification.
- Account summary card (role, join date, email status).
- Staff accounts have a **Delete My Account** option (admin-protected via modal + controller guard).

---

## 🚀 Quick Start

### Requirements
- PHP 8.2+
- Composer
- MySQL (XAMPP recommended on Windows)
- Internet connection (for Resend email OTP)

### Option A — One-click startup (Windows / XAMPP)

1. Start **XAMPP** and ensure **Apache** and **MySQL** are running.
2. Double-click **`start.bat`** in the project root.
   - Automatically creates the `inventory_db` database if it doesn't exist.
   - Runs `php artisan migrate --seed`.
   - Starts the development server at **http://127.0.0.1:8000**.

### Option B — Manual setup

```bash
composer install
copy .env.example .env
php artisan key:generate
# Edit .env — set DB_DATABASE, DB_USERNAME, DB_PASSWORD, RESEND_API_KEY
php artisan migrate --seed
php artisan serve
```

---

## ⚙️ Environment Variables

Copy `.env.example` to `.env` and configure:

```dotenv
APP_NAME="Gym Inventory"
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=inventory_db
DB_USERNAME=root
DB_PASSWORD=

# Email — Resend
MAIL_MAILER=resend
MAIL_FROM_ADDRESS=onboarding@resend.dev
MAIL_FROM_NAME="Gym Inventory"
RESEND_API_KEY=your_resend_api_key_here
```

> **Note:** The Resend free tier only allows sending from `onboarding@resend.dev`. To send OTPs to any email address, verify your own domain at [resend.com/domains](https://resend.com/domains) and update `MAIL_FROM_ADDRESS`.

---

## 👥 User Roles

| Action | Admin | Staff |
|---|---|---|
| View inventory | ✅ | ✅ |
| Record stock transactions | ✅ | ✅ |
| Manage products / suppliers | ✅ | ❌ |
| Manage users & roles | ✅ | ❌ |
| Receive purchase orders | ✅ | ❌ |
| Delete own account | ❌ | ✅ |
| Export data | ✅ | ✅ |

Newly registered users receive the **Staff** role. To promote to admin:

```bash
php artisan inventory:make-admin admin@example.com
```

---

## 🗄️ Database

- **Auto-created** on first boot via `AppServiceProvider` (MySQL only; SQLite for tests is handled automatically).
- Main migration: `2026_11_16_000001_create_inventory_management_tables.php`
- OTP migration: `2026_11_16_161925_add_otp_to_users_table.php` — adds `otp_code` and `otp_expires_at` to `users`.

> ⚠️ The migration `2026_11_16_000002_remove_legacy_fitness_tables` permanently drops former workout, trainer, plan, activity-log, and notification tables. Back up any existing data before applying it.

---

## 🧪 Tests

```bash
php artisan test
```

**60 tests, 237 assertions** — all passing.

Test coverage includes:
- Authentication (login, logout, OTP flow)
- Registration (OTP gating — account not created until verified)
- Audit Trail CRUD lifecycle (Create, Read, Update, Delete with stock reconciliation)
- Audit Trail CSV Export & Printable ledger / vouchers
- Staff stock movement email alerts to staff and admin
- Manual Purchase Order creation, cancellation, and printable PO invoices
- Supplier show profile with catalog items and fulfillment performance
- Product barcode label and tag generation
- Product CRUD lifecycle
- Stock In / Stock Out / Adjustment transactions
- Reorder point logic and automated purchase order creation
- Low stock notifications
- CSV export integrity
- API endpoint responses
- Role-based access control
- Profile management

---

## 🛠️ Tech Stack

| Layer | Technology |
|---|---|
| Framework | Laravel 11 |
| Frontend | Bootstrap 5.3.3 + Chart.js 4.4.3 |
| Icons | Bootstrap Icons 1.11.3 |
| Email | Resend (`resend/resend-laravel`) |
| Exports | Maatwebsite Excel + barryvdh/laravel-dompdf |
| Database | MySQL (production) / SQLite in-memory (tests) |
| Auth | Laravel Breeze + custom Email OTP 2FA |

---

## 📁 Key File Structure

```
app/
├── Http/Controllers/
│   ├── Auth/
│   │   ├── AuthenticatedSessionController.php  # OTP login trigger
│   │   ├── RegisteredUserController.php        # OTP registration trigger
│   │   └── OtpController.php                   # OTP send/verify logic
│   ├── ProfileController.php                   # Admin delete guard
│   └── Api/DashboardApiController.php          # Live dashboard JSON
├── Models/User.php                             # OTP fields + role
└── Providers/AppServiceProvider.php            # Auto DB creation

resources/views/
├── auth/otp.blade.php                          # 6-digit OTP UI
├── dashboard.blade.php                         # Charts + live stats
├── profile/edit.blade.php                      # My Profile page
└── layouts/app.blade.php                       # Main layout

public/css/
├── style.css                                   # Core glassmorphism theme
└── custom.css                                  # Global glass card overrides

start.bat                                       # Windows one-click launcher
```

---

## 📜 License

MIT
