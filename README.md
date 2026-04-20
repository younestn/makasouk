# Makasouk Backend (Laravel 11 Baseline)

## Required software
- PHP 8.2+
- Composer 2+
- PostgreSQL 14+ with PostGIS extension
- Redis 6+
- Node.js 20+ (optional, only if frontend assets are needed)

## PostgreSQL + PostGIS setup
```sql
CREATE DATABASE makasouk;
\c makasouk;
CREATE EXTENSION IF NOT EXISTS postgis;
```

## Redis requirement
Redis is required for cache, queue, and broadcasting scaling support.

## Environment setup
```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` for Postgres, Redis, Sanctum domains, and Reverb keys.

## Install dependencies
```bash
composer install
composer require laravel/sanctum laravel/reverb
php artisan vendor:publish --provider="Laravel\\Sanctum\\SanctumServiceProvider"
```

## Migrate and seed
```bash
php artisan migrate:fresh --seed
```

## Run API server
```bash
php artisan serve
```

## Run queue worker
```bash
php artisan queue:work
```

## Run Reverb
```bash
php artisan reverb:start
```

## Default local credentials
- Admin: `admin@makasouk.local` / `Admin@12345`

## Run tests
```bash
php artisan test
```

## Optional Docker dependencies
```bash
docker compose up -d
```

## Phase 2 runtime validation notes
- RBAC hardened using Policies + middleware (`active`, `tailor.approved`).
- API responses normalized with Json Resources for key entities.
- Additional FormRequests added to reduce inline validation and improve controller clarity.
- Broadcasting auth tested via `/broadcasting/auth` feature test.

## Phase 3 Admin Panel (Filament)
- Panel URL: `/admin-panel`
- Access: admin users only (`role=admin`, `is_suspended=false`)
- Core resources: Users, Pending Tailors (via Users filters/actions), Categories, Products, Orders, Reviews
- Local admin: `admin@makasouk.local` / `Admin@12345`
- Safety guards:
  - self-suspend prevention in panel action
  - confirmation on destructive actions
  - no arbitrary order-status mutation from panel resource

### Filament install commands
```bash
composer require filament/filament
php artisan filament:install --panels
```
