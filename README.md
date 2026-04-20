# Makasouk Backend (Laravel 11 Baseline)

## Required software
- PHP 8.2+
- Composer 2+
- PostgreSQL 14+ with PostGIS extension
- Redis 6+
- Node.js 20+ (required for the Phase 5 Vue web client)

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

The no-op frontend build from earlier phases has been replaced in Phase 5 by a real Vue/Vite build.

### Safety safeguards
- Panel access enforced by `User::canAccessPanel()`.
- User suspend action prevents self-lockout and blocks suspending admin accounts.
- Tailor approval and suspension actions run in transactions.
- Category delete is blocked when products or tailor profiles still reference the category.
- Orders remain observability-first (list/view, no arbitrary status mutation).
- Review moderation delete action is intentionally disabled in this stage.

## Phase 4 Client Integration Contract

Integration docs:
- API contract: `docs/integration/api-contract.md`
- Realtime contract: `docs/integration/realtime-contract.md`

### Integration-ready API additions
- Catalog: `/api/catalog/categories`, `/api/catalog/products`, `/api/catalog/products/{product}`
- Customer: `/api/customer/orders-active`
- Tailor: `/api/tailor/profile`, `/api/tailor/availability`, `/api/tailor/orders-active`

### Realtime contract (Reverb + Sanctum)
- Private channels:
  - `private-customer.{customerId}`
  - `private-tailor.{tailorId}`
  - `private-admin.{adminId}`
- Order events:
  - `order.created`
  - `order.accepted`
  - `order.status_updated`
  - `order.cancelled_by_customer`
  - `order.cancelled_by_tailor`

### Order lifecycle quick reference
- Main statuses:
  - `searching_for_tailor`, `no_tailors_available`, `accepted`, `processing`, `ready_for_delivery`, `completed`, `cancelled_by_customer`, `cancelled_by_tailor`, `cancelled`
- Tailor transitions:
  - `accepted -> processing -> ready_for_delivery -> completed`
- Customer cancel window:
  - `searching_for_tailor`, `accepted`, `processing`
- Tailor cancel window:
  - `accepted`, `processing`, `ready_for_delivery`

## Phase 5 Web Client Foundation (Vue)

### SPA entry
- Web client base path: `/app`
- Login page: `/app/login`
- Admin users are redirected to `/admin-panel`

### Frontend commands
```bash
npm install
npm run dev
npm run build
npm run test
```

### Frontend environment variables
Add these to `.env`:

```env
VITE_SPA_BASE=/app/
VITE_API_BASE_URL=/api
VITE_BROADCAST_AUTH_ENDPOINT=/broadcasting/auth
VITE_REVERB_KEY=makasouk-key
VITE_REVERB_HOST=127.0.0.1
VITE_REVERB_PORT=8080
VITE_REVERB_SCHEME=http
```

### What Phase 5 implements
- Vue 3 + Vite SPA shell with router + Pinia state.
- Auth flow with token bootstrap via `/api/auth/me`.
- Customer shell: catalog, create order, active/history orders, details, review/cancel actions.
- Tailor shell: dashboard, active orders, detail actions (accept/update/cancel), availability/profile pages.
- Realtime client integration with Echo/Reverb private channels.

### Phase 5 doc
- `docs/integration/web-client-foundation.md`

## Phase 6 Public Website + UX Polish

### Public website routes
- `/`
- `/how-it-works`
- `/for-customers`
- `/for-tailors`
- `/faq`
- `/contact`

These routes now render the dedicated public Vue entrypoint (`resources/js/public/main.js`) through:
- `resources/views/public-site.blade.php`

### SPA + Admin coexistence
- Client SPA remains mounted under `/app/*` via `resources/views/spa.blade.php`.
- Filament admin panel remains under `/admin-panel`.
- Public pages, app shell, and admin panel are now explicitly separated at route level.

### Phase 6 frontend commands
```bash
npm install
npm run build
npm run test
php artisan optimize:clear
php artisan route:list
php artisan test
```

### Phase 6 implementation docs
- `docs/integration/web-client-foundation.md` (updated)
- `docs/integration/public-website-and-ux-polish.md` (new)
