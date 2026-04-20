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
php artisan vendor:publish --provider="Laravel\\Sanctum\\SanctumServiceProvider"
```

Filament admin panel package is included in `composer.json` (`filament/filament:^3.3`).

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

## Phase 3.1 Admin Panel (Filament)
- Panel URL: `/admin-panel`
- Access: admin users only (`role=admin`, `is_suspended=false`)
- Core resources: Users, Categories, Products, Orders, Reviews
- Pending tailor approvals: handled in Users resource via `Pending Tailors` filter + `Approve Tailor` action

### Filament install and runtime validation
```bash
composer install
npm install
npm run build
php artisan optimize:clear
php artisan migrate:fresh --seed
php artisan test --filter=Filament
php artisan test
```

`npm run build` is a backend-safe no-op in this repository (there is no customer/tailor frontend bundle in this stage).

### Safety safeguards
- Panel access enforced by `User::canAccessPanel()`.
- User suspend action prevents self-lockout and blocks suspending admin accounts.
- Tailor approval and suspension actions run in transactions.
- Category delete is blocked when products or tailor profiles still reference the category.
- Orders remain observability-first (list/view, no arbitrary status mutation).
- Review moderation delete action is intentionally disabled in this stage.
