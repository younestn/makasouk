# Makasouk Backend (Laravel 11 Baseline)

## Required software
- PHP 8.2+
- Composer 2+
- MySQL 8.0+ / 8.4+
- Redis 6+
- Node.js 20+ (required for the Phase 5 Vue web client)

## MySQL setup
```sql
CREATE DATABASE makasouk CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

## Redis requirement
Redis is required for cache, queue, and broadcasting scaling support.

## Environment setup
```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` for MySQL, Redis, Sanctum domains, and Reverb keys.

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
- Customer: `customer@makasouk.local` / `Password@123`
- Tailor: `tailor@makasouk.local` / `Password@123`

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

## Phase 7 Frontend Hardening + RTL/SEO/QA

### Key hardening upgrades
- Global toast notification center (SPA + public site mount)
- Reusable pagination controls for high-volume list pages
- First-pass localization foundation (`en` / `ar`) + RTL direction switching
- Route-driven SEO metadata management for all public pages
- Expanded frontend test coverage (router guards, services, i18n, pagination, SEO, toast store)
- Additive backend support endpoint for tailor order details:
  - `GET /api/tailor/orders/{order}`

### Phase 7 validation commands
```bash
npm install
npm run test
npm run build
php artisan optimize:clear
php artisan test
php artisan serve
```

### Phase 7 documentation
- `docs/integration/phase-7-frontend-hardening.md` (new)
- `docs/integration/web-client-foundation.md` (updated)
- `docs/integration/public-website-and-ux-polish.md` (updated)

## Phase 8 Launch Readiness (E2E + CI + Release Prep)

### Deterministic QA fixtures
- `database/seeders/ClientAppFixturesSeeder.php` provides stable QA accounts and a deterministic accepted-order fixture.
- `DatabaseSeeder` now calls `ClientAppFixturesSeeder`, so `php artisan migrate:fresh --seed` is enough for QA and E2E data setup.

### Playwright E2E automation
- Config: `playwright.config.js`
- Specs: `tests/e2e/*.spec.js`
- Helpers: `tests/e2e/helpers/*`

Commands:
```bash
npx playwright install --with-deps chromium
npm run e2e
```

### CI quality gates
- Workflow: `.github/workflows/ci.yml`
- Jobs:
  - `quality-gates`: composer install, npm install, migrate/seed, frontend tests, frontend build, backend tests
  - `e2e`: Playwright browser install + browser E2E suite
- Services:
  - MySQL 8.4 (`mysql:8.4`)
  - Redis (`redis:7-alpine`)

### Release and deployment runbooks
- Launch checklist: `docs/release/launch-checklist.md`
- Deployment notes: `docs/release/deployment-notes.md`
- Manual QA matrix: `docs/release/manual-qa-matrix.md`

### Production hardening expectations
- Set `APP_ENV=production` and `APP_DEBUG=false`.
- Keep Sanctum stateful domains aligned with deployed web origins.
- Run supervised processes for queue worker and Reverb server in production.
- Build Vite assets on deploy before traffic cutover.

## Phase 9 Staging Validation + Release Cutover

### Staging readiness docs
- Launch checklist: `docs/release/launch-checklist.md`
- Deployment notes: `docs/release/deployment-notes.md`
- Manual QA/UAT matrix: `docs/release/manual-qa-matrix.md`
- Staging signoff sheet: `docs/release/staging-signoff.md`
- Production cutover plan: `docs/release/production-cutover-plan.md`

### CI flow for release candidates
- Workflow: `.github/workflows/ci.yml`
- Jobs run in sequence:
  - `quality-gates` (install, migrate/seed, frontend tests, build, backend tests, route smoke)
  - `e2e` (Playwright browser + deterministic browser suite)

### Staging-focused environment notes
- `VITE_*` variables are compile-time and must be set before `npm run build`.
- `SANCTUM_STATEFUL_DOMAINS` must match browser-facing app origins.
- `SESSION_SECURE_COOKIE=true` is required on HTTPS staging/production.
- `REVERB_ALLOWED_ORIGINS` should be explicit trusted domains (avoid `*` outside local development).

### Release-candidate validation command set
```bash
composer install
npm install
php artisan optimize:clear
php artisan migrate:fresh --seed
npm run test
npm run build
php artisan test
npx playwright install --with-deps chromium
npm run e2e:ci
```

## Phase 10 Production Release Execution + Hypercare

### Final production gate conditions
- Hosted CI is green on the release commit (`quality-gates` and `e2e`).
- `docs/release/staging-signoff.md` is completed with a `GO` result.
- No open `BLOCKER` issues remain.

### Release execution and operations docs
- Launch checklist: `docs/release/launch-checklist.md`
- Deployment notes: `docs/release/deployment-notes.md`
- Manual QA matrix: `docs/release/manual-qa-matrix.md`
- Production cutover plan: `docs/release/production-cutover-plan.md`
- Hypercare plan: `docs/release/hypercare-plan.md`
- Incident response playbook: `docs/release/incident-response-playbook.md`
- Release notes (v1): `docs/release/release-notes-v1.md`

### Production-sensitive env guidance (summary)
- `APP_ENV=production`, `APP_DEBUG=false`
- `SESSION_SECURE_COOKIE=true` on HTTPS
- `SANCTUM_STATEFUL_DOMAINS` aligned to real web origins
- `REVERB_ALLOWED_ORIGINS` set to explicit trusted origins (no wildcard in production)
- Ensure `VITE_*` values are correct before build; they are compile-time values
