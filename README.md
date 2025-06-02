# Employee Leave Management System

Laravel 12 application untuk manajemen cuti pegawai dengan role-based access control.

## Prerequisites

-   PHP >= 8.2
-   Composer >= 2.0
-   Node.js >= 18.0
-   MySQL >= 8.0

## Installation

### 1. Clone Repository

```bash
git clone <repository-url>
cd employee-leave-management
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install Frontend Dependencies

```bash
npm install
```

### 4. Environment Setup

```bash
cp .env.example .env
php artisan key:generate
```

### 5. Database Setup (MySQL)

Edit `.env` file:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=skill_test_deeptech
DB_USERNAME=root
DB_PASSWORD=your_password
```

Create database:

```sql
CREATE DATABASE skill_test_deeptech;
```

### 6. Run Migrations

```bash
php artisan migrate
```

### 7. Seed Database

```bash
php artisan db:seed
```

### 8. Build Frontend Assets

```bash
npm run build
# atau untuk development:
npm run dev
```

### 9. Start Development Servers

```bash
# Terminal 1 - Laravel
php artisan serve

# Terminal 2 - Vite (jika menggunakan npm run dev)
npm run dev
```

## Verification & Testing

### Web Application

-   Access: http://localhost:8000
-   Login with:
    -   **SuperAdmin:** `superadmin@company.com` / `password`
    -   **Admin:** `admin@company.com` / `password`

### API Testing

```bash
# Login & get token
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email": "superadmin@company.com", "password": "password"}'

# Test authenticated endpoint
curl -X GET http://localhost:8000/api/v1/auth/me \
  -H "Authorization: Bearer {your_token}"
```

## Troubleshooting

### 1. Permission Denied (Linux/Mac)

```bash
chmod -R 775 storage bootstrap/cache
chown -R $USER:www-data storage bootstrap/cache
```

### 2. Database Connection Error

```bash
# Check database credentials
mysql -u root -p -e "SHOW DATABASES;"

# Verify .env configuration
grep DB_ .env
```

### 3. Composer Memory Limit

```bash
php -d memory_limit=2G composer install
```

### 4. Migration Failed

```bash
# Reset database
php artisan migrate:fresh --seed
```

### 5. Assets Not Loading

```bash
# Clear caches
php artisan optimize:clear

# Rebuild assets
npm run build
```

## Default Login Credentials

| Role       | Email                  | Password | Access                               |
| ---------- | ---------------------- | -------- | ------------------------------------ |
| SuperAdmin | superadmin@company.com | password | Full system access                   |
| Admin      | admin@company.com      | password | Limited access (no admin management) |

## API Documentation

-   **Base URL:** http://localhost:8000/api/v1
-   **Authentication:** Bearer Token (Laravel Sanctum)
-   **Endpoints:** `/auth`, `/employees`, `/leaves`, `/admin/leaves`

## Development

```bash
# Clear all caches
php artisan optimize:clear

# Reset database with fresh data
php artisan migrate:fresh --seed

# View routes
php artisan route:list

# Run tests
php artisan test
```
