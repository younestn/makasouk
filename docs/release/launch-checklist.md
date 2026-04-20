# Launch Checklist (Phase 9)

## 0) Production release gates (must all be true)
- One hosted CI run is green on the exact release commit (`quality-gates` + `e2e`).
- `docs/release/staging-signoff.md` is completed and signed with `GO`.
- No open `BLOCKER` issues remain from `docs/release/manual-qa-matrix.md` or bug bash.

## 1) Release blockers (must pass)
- `composer install` succeeds on a clean machine.
- `npm install` succeeds and lockfiles are committed.
- `php artisan migrate:fresh --seed` runs successfully with PostGIS enabled.
- `npm run test` passes.
- `php artisan test` passes.
- `npm run build` passes and produces the Vite manifest.
- `npm run e2e:ci` passes (or CI Playwright job is green).
- GitHub Actions `quality-gates` and `e2e` jobs are green on the release commit.
- Private route auth boundaries are verified (`/app/*` role guards, `/admin-panel` admin-only).

## 2) Non-blockers (can follow after release)
- Additional cross-browser E2E coverage beyond Chromium.
- Full Arabic copy translation for all long-form marketing text.
- Advanced observability dashboards for queue/Reverb throughput.

## 3) Environment requirements
- PHP 8.2+, Composer 2+, Node.js 20+, npm.
- PostgreSQL 14+ with PostGIS extension.
- Redis 6+.
- Reverb runtime available for realtime flows.

## 4) Seeded QA accounts
- Admin: `admin@makasouk.local` / `Admin@12345`
- Customer: `customer@makasouk.local` / `Password@123`
- Tailor: `tailor@makasouk.local` / `Password@123`

## 5) Pre-release command sequence
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

## 5.1) Staging signoff requirement
- Complete `docs/release/staging-signoff.md` before production cutover.
- Every `BLOCKER` item in the manual QA matrix must be PASS.

## 6) Runtime readiness checks
- Queue worker starts and processes jobs: `php artisan queue:work`.
- Reverb server starts without auth/channel errors: `php artisan reverb:start`.
- Public pages load (`/`, `/how-it-works`, `/for-customers`, `/for-tailors`, `/faq`, `/contact`).
- SPA and admin entrypoints load (`/app/login`, `/app/customer`, `/app/tailor`, `/admin-panel/login`).

## 7) Post-deploy smoke (critical)
- Customer login, catalog browse, and order creation.
- Tailor login, active orders list, and order details access.
- Admin login page reachable and panel auth still enforced.
- No unexpected exceptions in Laravel logs.

## 8) Supportability checks (first 30 minutes)
- Confirm queue worker has no fast-fail restart loop.
- Confirm Reverb server accepts connections and no auth flood is present.
- Confirm failed jobs table is not growing unexpectedly.
- Confirm frontend assets are current (`public/build/manifest.json` timestamp matches release).

## 9) Launch closure criteria
- First-hour hypercare checks are complete with no unresolved `BLOCKER`.
- First-24-hour checks are complete and documented in `docs/release/hypercare-plan.md`.
- Any remaining `MAJOR` or `MINOR` items have an owner and ETA.
- Final release decision is recorded as:
  - `GO for production release`, or
  - `CONDITIONAL GO for production release`, or
  - `NO-GO for production release`.
