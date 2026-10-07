# Membership Management System

A secure Laravel 13 REST API for member accounts, plans, enrollment and renewal payments, access check-ins, and operational reporting.

## Features

- Sanctum token authentication with throttled login and registration
- Admin, staff, and member roles with ownership policies
- Unique non-sequential member numbers and member directory data
- Configurable membership plans with immutable enrollment price snapshots
- Transactional enrollment/renewal and payment creation
- Automatic non-overlapping scheduled renewals
- Active-membership validation and one check-in per member per day
- Dashboard metrics, structured JSON errors, CORS allowlist, Docker, and CI

## Quick start

Requires PHP 8.3+, Composer, and MySQL 8.

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

Seeded accounts use password `ChangeMe123!`: `admin@example.com` and `member@example.com`. Change these immediately outside local development.

## API

All routes use `/api`. Members can browse active plans and view only their memberships. Staff/admin users manage plans, enroll or renew members, cancel memberships, record check-ins, and access `/dashboard`.

```json
{
  "membership_plan_id": 1,
  "amount": 35,
  "method": "card",
  "reference": "PAY-10001"
}
```

## Docker and checks

Generate a key with `php artisan key:generate --show`, replace the Compose placeholder, and run `docker compose up --build`.

```bash
composer test
vendor/bin/pint --test
composer audit
```

CI additionally validates migrations and seeders against MySQL 8.4.

## License

MIT
