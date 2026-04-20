# Deployment Notes (Phase 9)

## 1) Production expectations
- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL` points to public HTTPS origin.
- Database points to PostgreSQL with PostGIS available.
- Redis is enabled for cache/queue/realtime scaling.
- Frontend assets are built before traffic cutover.

## 2) Required environment variables
- Core: `APP_NAME`, `APP_KEY`, `APP_ENV`, `APP_DEBUG`, `APP_URL`
- DB: `DB_CONNECTION=pgsql`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
- Redis/Queue/Cache: `REDIS_HOST`, `REDIS_PORT`, `CACHE_STORE`, `QUEUE_CONNECTION`
- Sanctum: `SANCTUM_STATEFUL_DOMAINS` for trusted web origins
- Reverb:
  - `BROADCAST_CONNECTION=reverb`
  - `REVERB_APP_ID`, `REVERB_APP_KEY`, `REVERB_APP_SECRET`
  - `REVERB_HOST`, `REVERB_PORT`, `REVERB_SCHEME`
  - `REVERB_SERVER_HOST`, `REVERB_SERVER_PORT`
- Frontend runtime:
  - `VITE_SPA_BASE`
  - `VITE_API_BASE_URL`
  - `VITE_BROADCAST_AUTH_ENDPOINT`
  - `VITE_REVERB_KEY`, `VITE_REVERB_HOST`, `VITE_REVERB_PORT`, `VITE_REVERB_SCHEME`
  - `VITE_*` values must be present before `npm run build` (they are compile-time values).

## 2.1) Strongly recommended staging/production values
- `SESSION_SECURE_COOKIE=true` on HTTPS environments.
- `SESSION_SAME_SITE=lax` (or stricter if cross-site flow is not needed).
- `REVERB_ALLOWED_ORIGINS` should be explicit production domains, not `*`.
- `SANCTUM_STATEFUL_DOMAINS` should match the web app hostnames used by browsers.

## 3) Deployment sequence
```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
```

Optional (staging/UAT only):
```bash
php artisan db:seed --force
```

## 4) Process supervision
- App runtime: PHP-FPM (or Octane if explicitly adopted later).
- Queue worker should be supervised (`php artisan queue:work`).
- Reverb server should be supervised (`php artisan reverb:start`).
- Configure restart policies for worker and Reverb to recover from host restarts.

## 5) Logging and observability
- Production `LOG_LEVEL` should be `info` or stricter unless debugging an incident.
- Monitor `storage/logs/laravel.log` for:
  - broadcasting auth failures
  - queue failures
  - DB/PostGIS errors
- Track failed jobs table and configure alerting on growth spikes.
- For queue worker health:
  - check process uptime/restarts in supervisor logs
  - check `php artisan queue:failed` output
- For Reverb health:
  - confirm `php artisan reverb:start` process is running
  - check for repeated `/broadcasting/auth` failures in logs

## 6) CORS/session/mail notes
- This project currently relies on Laravel framework CORS defaults (no local `config/cors.php` override).
- If cross-origin browser clients are introduced, publish and harden CORS config to trusted origins only.
- Keep session cookie secure on HTTPS (`SESSION_SECURE_COOKIE=true` in production).
- Mail driver should be configured to non-local transport before user-facing notifications.

## 7) Rollback notes
- Keep previous build artifacts available for quick rollback.
- If deploy fails after migrate, roll forward with a hotfix migration when possible.
- If rollback is required:
  - redeploy previous app + assets
  - revert only safe migrations (avoid data-loss down migrations in production)
  - clear and rebuild caches (`optimize:clear`, then cache commands).

## 8) CI assumptions for release confidence
- CI workflow expects:
  - PostgreSQL + PostGIS service container
  - Redis service container
  - Node.js 20 and PHP 8.2
- `quality-gates` must pass before `e2e` runs.
- E2E uses deterministic seeded fixtures; seed failures are considered release blockers.
