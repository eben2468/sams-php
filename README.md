# SAMS — Student Attendance Management System (Plain PHP)

> Valley View University | Digital Attendance Tracking System

A full-stack web application for digitally recording student attendance at church
services and university events. This is the **plain PHP version** — it was
converted from Laravel to a small, self-contained MVC framework with **no
framework dependency**.

## Tech Stack

- **Backend:** Plain PHP 8.2+ (custom front-controller MVC + PDO)
- **Frontend:** Plain-PHP templates + Tailwind CSS (Play CDN) + Alpine.js (CDN)
- **Database:** MySQL / MariaDB (PDO)
- **Auth:** Native PHP sessions + role-based middleware
- **Scanner:** html5-qrcode + Tesseract.js (CDN)
- **PDF reports:** dompdf
- **Excel reports:** native CSV export
- **Charts:** Chart.js (CDN)

There is **no build step** — no Node, npm, Vite, or Composer is required to run
the app. CSS/JS are served from `public/` and the CDNs.

## Requirements

- PHP 8.2+ with `pdo_mysql`, `mbstring`, `gd`, `dom`, `fileinfo`
- MySQL 8 / MariaDB 10.4+
- XAMPP (or Apache + PHP + MySQL)

## Project Structure

```
/sams-php
├── app/
│   ├── Core/                ← mini-framework (Router, Model, QueryBuilder,
│   │                           Database, Request, Response, View, Auth,
│   │                           Validator, Session, Collection, Paginator, …)
│   ├── Http/Controllers/    ← controllers
│   ├── Http/Middleware/     ← auth / guest / role middleware
│   ├── Models/              ← PDO-backed active-record models
│   ├── Exports/             ← CSV export
│   ├── autoload.php         ← PSR-4 + Composer ClassLoader (Carbon, dompdf)
│   └── helpers.php          ← global helpers (route, auth, view, e, old, …)
├── config/config.php        ← reads .env
├── database/
│   ├── schema.sql           ← database schema
│   ├── seed.php             ← demo-data seeder
│   └── install.php          ← creates DB + schema + seed (one step)
├── resources/views/         ← plain-PHP templates (layout/section/yield)
├── routes/                  ← web.php + api.php
├── public/                  ← document root (index.php front controller)
│   ├── css/app.css          ← custom styles
│   ├── js/app.js            ← global JS helpers
│   └── uploads/             ← student photos
├── vendor/                  ← Carbon + dompdf (Composer, optional to refresh)
├── .env                     ← environment config
└── composer.json
```

## Setup

### 1. Configure the environment

Copy and edit `.env`:

```bash
cp .env.example .env
```

```env
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sams_php_db
DB_USERNAME=root
DB_PASSWORD=
```

### 2. Create the database, schema and demo data

Make sure MySQL is running, then run the one-step installer:

```bash
php database/install.php
```

This creates the `sams_php_db` database, loads `schema.sql`, and seeds demo
data. (You can re-seed any time with `php database/seed.php`.)

### 3. Run the app

**With XAMPP** (project in `htdocs/sams-php`): start Apache + MySQL and visit:

```
http://localhost/sams-php/
```

The root `.htaccess` forwards requests into `public/`, and `public/.htaccess`
routes everything through the front controller. (`mod_rewrite` must be enabled —
it is by default in XAMPP.)

**Or with PHP's built-in server** (document root = `public/`):

```bash
php -S localhost:8000 -t public
# then visit http://localhost:8000
```

## Login Credentials

| Role       | Email                  | Password      |
|------------|------------------------|---------------|
| Admin      | admin@vvu.edu.gh       | admin123      |
| Officer    | officer1@vvu.edu.gh    | officer123    |
| Supervisor | supervisor@vvu.edu.gh  | supervisor123 |

## Features

- ✅ Role-based access control (Admin, Officer, Supervisor)
- ✅ QR/Barcode scanning + OCR ID scanning for attendance
- ✅ Manual ID entry with verification flagging
- ✅ Event management with officer assignment & semesters
- ✅ Student management with CSV import
- ✅ Attendance reports (PDF via dompdf, Excel via CSV)
- ✅ Departmental attendance analytics & dashboard charts
- ✅ Complete audit trail
- ✅ Mobile-responsive design
- ✅ JSON API (`/api/...`) mirroring the web features

## Optional: refresh Composer dependencies

`vendor/` already contains Carbon and dompdf, so the app runs as-is. If you want
to slim `vendor/` down to just the plain-PHP dependencies:

```bash
composer update
```

## How the conversion maps to the old Laravel version

| Laravel                         | Plain PHP replacement                         |
|---------------------------------|-----------------------------------------------|
| Routing (`Route::`)             | `App\Core\Router` (+ `routes/web.php`, `api.php`) |
| Eloquent                        | `App\Core\Model` + `QueryBuilder` (PDO)       |
| Blade                           | `App\Core\View` (plain-PHP `layout/section/yield`) |
| Form Requests / `$request->validate` | `App\Core\Validator`                     |
| Auth + Sanctum                  | `App\Core\Auth` (PHP sessions)                |
| Migrations / Seeders            | `database/schema.sql` + `database/seed.php`   |
| `barryvdh/laravel-dompdf`       | `dompdf/dompdf` directly                       |
| `maatwebsite/excel`             | native CSV (`App\Exports\AttendanceExport`)   |
| Vite / Tailwind build           | Tailwind Play CDN + `public/css/app.css`      |

## Troubleshooting

- **DB connection error** — verify MySQL is running and `.env` credentials.
- **403/404 on every page** — ensure `mod_rewrite` is enabled and you are
  visiting through Apache (or use `php -S ... -t public`).
- **Reset data** — `php database/install.php`.

## License

© 2026 Valley View University — SAMS
