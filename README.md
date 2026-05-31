# SAMS — Student Attendance Management System (PHP/Laravel Version)

> Valley View University | Digital Attendance Tracking System

## Overview

SAMS is a full-stack web application for digitally recording student attendance at church services and university events. This is the **PHP/Laravel version** converted from the original React.js/Node.js stack.

## Tech Stack

- **Backend:** Laravel 11 (PHP 8.2+)
- **Frontend:** Blade Templates + Tailwind CSS v3 + Alpine.js
- **Database:** MySQL 8.0+ (Eloquent ORM)
- **Auth:** Laravel Session Authentication
- **Scanner:** html5-qrcode (JavaScript)
- **Reports:** DomPDF + Laravel Excel
- **Charts:** Chart.js

## Prerequisites

- PHP 8.2 or higher
- Composer 2.x
- MySQL 8.0+
- Node.js 18+ (for asset compilation)
- XAMPP (or standalone MySQL/Apache)

## Setup Instructions

### 1. Create MySQL Database

```sql
CREATE DATABASE sams_php_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install Node dependencies (for Tailwind CSS)
npm install
```

### 3. Configure Environment

```bash
# Copy environment file
copy .env.example .env

# Generate application key
php artisan key:generate
```

Edit `.env` file:
```env
APP_NAME="SAMS - Student Attendance Management System"
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sams_php_db
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
SESSION_LIFETIME=480
```

### 4. Set Up Database

```bash
# Run migrations
php artisan migrate

# Seed database with sample data
php artisan db:seed
```

### 5. Create Storage Link

```bash
php artisan storage:link
```

### 6. Compile Assets

```bash
# Development
npm run dev

# Production
npm run build
```

### 7. Start Development Server

```bash
php artisan serve
```

Visit: **http://localhost:8000**

## Login Credentials

| Role       | Email                    | Password      |
|------------|--------------------------|---------------|
| Admin      | admin@vvu.edu.gh         | admin123      |
| Officer 1  | officer1@vvu.edu.gh      | officer123    |
| Officer 2  | officer2@vvu.edu.gh      | officer123    |
| Supervisor | supervisor@vvu.edu.gh    | supervisor123 |

## Features

- ✅ Role-based access control (Admin, Officer, Supervisor)
- ✅ QR/Barcode scanning for attendance
- ✅ Manual ID entry with verification flagging
- ✅ Real-time student verification
- ✅ Event management with officer assignment
- ✅ Student management with CSV import
- ✅ Attendance reports (PDF/Excel export)
- ✅ Departmental attendance analytics
- ✅ Complete audit trail
- ✅ Mobile-responsive design
- ✅ Dashboard with real-time statistics

## Project Structure

```
/sams-php
├── app/
│   ├── Http/
│   │   ├── Controllers/      ← All controllers
│   │   ├── Middleware/       ← Custom middleware
│   │   └── Requests/         ← Form validation
│   ├── Models/               ← Eloquent models
│   └── Services/             ← Business logic
├── database/
│   ├── migrations/           ← Database schema
│   └── seeders/              ← Sample data
├── resources/
│   ├── views/                ← Blade templates
│   │   ├── layouts/          ← Layout files
│   │   ├── auth/             ← Login page
│   │   ├── dashboard/        ← Dashboard
│   │   ├── students/         ← Student management
│   │   ├── events/           ← Event management
│   │   ├── attendance/       ← Attendance marking
│   │   ├── reports/          ← Reports
│   │   ├── users/            ← User management
│   │   ├── audit/            ← Audit logs
│   │   └── settings/         ← System settings
│   └── css/                  ← Stylesheets
├── routes/
│   ├── web.php               ← Web routes
│   └── api.php               ← API routes
├── public/
│   ├── css/                  ← Compiled CSS
│   ├── js/                   ← Compiled JS
│   └── uploads/              ← User uploads
├── .env                      ← Environment config
├── composer.json             ← PHP dependencies
├── package.json              ← Node dependencies
└── README.md                 ← This file
```

## API Endpoints

All API endpoints are prefixed with `/api/`

### Authentication
- `POST /api/auth/login` - User login
- `POST /api/auth/logout` - User logout
- `GET /api/auth/me` - Get current user

### Students
- `GET /api/students` - List all students
- `POST /api/students` - Create student (Admin)
- `GET /api/students/{id}` - Get student details
- `PUT /api/students/{id}` - Update student (Admin)
- `DELETE /api/students/{id}` - Delete student (Admin)
- `POST /api/students/import` - Import students from CSV (Admin)

### Events
- `GET /api/events` - List all events
- `POST /api/events` - Create event (Admin)
- `GET /api/events/{id}` - Get event details
- `PUT /api/events/{id}` - Update event (Admin)
- `DELETE /api/events/{id}` - Delete event (Admin)
- `GET /api/events/active` - Get active events

### Attendance
- `POST /api/attendance/mark` - Mark attendance (Officer)
- `GET /api/attendance/event/{id}` - Get event attendance
- `GET /api/attendance/student/{id}` - Get student attendance
- `GET /api/attendance/absentees/{eventId}` - Get absentees
- `DELETE /api/attendance/{id}` - Delete attendance record (Admin)

### Reports
- `GET /api/reports/student/{id}` - Student attendance report
- `GET /api/reports/event/{id}` - Event attendance report
- `GET /api/reports/export/pdf` - Export PDF report (Admin/Supervisor)
- `GET /api/reports/export/excel` - Export Excel report (Admin/Supervisor)

### Dashboard
- `GET /api/dashboard/stats` - Get dashboard statistics (Admin/Supervisor)

### Users
- `GET /api/users` - List all users (Admin)
- `POST /api/users` - Create user (Admin)
- `PUT /api/users/{id}` - Update user (Admin)
- `DELETE /api/users/{id}` - Delete user (Admin)

### Audit Logs
- `GET /api/audit-logs` - Get audit logs (Admin)

### Departments & Programs
- `GET /api/departments` - List departments
- `POST /api/departments` - Create department (Admin)
- `GET /api/programs` - List programs
- `POST /api/programs` - Create program (Admin)

### System Settings
- `GET /api/settings` - Get system settings (Admin)
- `PUT /api/settings` - Update settings (Admin)

## Web Routes

- `GET /` - Redirect to login or dashboard
- `GET /login` - Login page
- `POST /login` - Process login
- `POST /logout` - Logout

### Protected Routes (Requires Authentication)
- `GET /dashboard` - Dashboard
- `GET /students` - Student management
- `GET /events` - Event management
- `GET /attendance` - Attendance marking
- `GET /reports` - Reports
- `GET /users` - User management (Admin only)
- `GET /audit-logs` - Audit logs (Admin only)
- `GET /settings` - System settings (Admin only)

## Middleware

### Authentication
- `auth` - Requires user to be logged in
- `guest` - Requires user to be logged out

### Role-Based Access
- `role:admin` - Admin only
- `role:admin,supervisor` - Admin or Supervisor
- `role:admin,officer` - Admin or Officer

## Database Schema

### Users
- id, name, email, password, role, is_active, timestamps

### Students
- id, student_id, first_name, last_name, photo, program_id, level, department_id, faculty, is_active, timestamps

### Events
- id, name, type, start_time, end_time, description, created_by, timestamps

### Attendance
- id, event_id, student_id, marked_by, method, is_verified, timestamp

### Departments
- id, name, timestamps

### Programs
- id, name, department_id, timestamps

### Event Officers
- id, event_id, user_id

### Audit Logs
- id, action, performed_by, target_type, target_id, metadata, timestamp

### System Settings
- key, value, updated_at

## Development Commands

```bash
# Run migrations
php artisan migrate

# Rollback migrations
php artisan migrate:rollback

# Seed database
php artisan db:seed

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Generate IDE helper (optional)
php artisan ide-helper:generate

# Run tests
php artisan test

# Code formatting
./vendor/bin/pint
```

## Production Deployment

### 1. Optimize Application

```bash
# Install production dependencies
composer install --optimize-autoloader --no-dev

# Compile assets
npm run build

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 2. Set Environment

```env
APP_ENV=production
APP_DEBUG=false
```

### 3. Set Permissions

```bash
chmod -R 755 storage bootstrap/cache
```

### 4. Configure Web Server

Point document root to `/public` directory.

**Apache (.htaccess already included)**

**Nginx:**
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

## Differences from Node.js Version

| Feature | Node.js/React | PHP/Laravel |
|---------|---------------|-------------|
| Routing | Express routes | Laravel routes |
| ORM | Prisma | Eloquent |
| Templates | React JSX | Blade |
| Auth | JWT | Session |
| Validation | Manual | Form Requests |
| File Upload | Multer | Laravel Storage |
| PDF | PDFKit | DomPDF |
| Excel | ExcelJS | Laravel Excel |
| Real-time | N/A | Laravel Echo (optional) |

## Troubleshooting

### Database Connection Error
- Verify MySQL is running
- Check `.env` database credentials
- Ensure database exists

### Permission Errors
```bash
chmod -R 755 storage bootstrap/cache
```

### Asset Compilation Errors
```bash
npm install
npm run build
```

### Session Issues
```bash
php artisan session:table
php artisan migrate
```

## Support

For issues or questions, contact the development team at Valley View University.

## License

© 2026 Valley View University — SAMS

---

**Note:** This is the PHP/Laravel version of SAMS. For the original Node.js/React version, see the parent directory.
