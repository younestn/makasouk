# Production Cutover Plan (Phase 9)

## 1) Objective
Provide a controlled sequence for production release with clear rollback and smoke gates.

## 2) Pre-cutover prerequisites
- Staging signoff is complete (`docs/release/staging-signoff.md`).
- Release commit SHA is frozen and tagged.
- Backup/snapshot plan is confirmed for database.
- On-call owner for the release window is assigned.
- Hosted CI is green on the release commit (`quality-gates` + `e2e`).
- No open `BLOCKER` issues remain.

## 3) Cutover sequence
1. Announce release window and expected user impact.
2. Deploy backend/frontend artifact for the release commit.
3. Run migration command:
```bash
php artisan migrate --force
```
4. Clear and warm caches:
```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```
5. Restart supervised workers/services:
- queue worker
- Reverb server
6. Verify app health endpoint and entry routes:
- `/up`
- `/`
- `/app/login`
- `/admin-panel/login`
7. Execute smoke tests (section 5 below).
8. Announce completion if all checks pass.

### 3.1) Recommended operator checkpoints
- `T-30m`: confirm env values, secrets, DB connectivity, Redis connectivity.
- `T-10m`: confirm rollback artifact is available and deploy owner is online.
- `T+0`: deploy + migrate + cache warm sequence.
- `T+10m`: execute smoke tests and confirm logs/queue/realtime baseline.
- `T+30m`: mark release status (`SUCCESS` or `ROLLED BACK`) and start hypercare schedule.

## 4) Migration and data caution points
- Do not run `migrate:fresh` in production.
- Do not run demo/QA seeds in production.
- If a migration fails midway, stop and evaluate rollback/roll-forward strategy before retrying.

## 5) Immediate post-cutover smoke tests
- Customer can login and browse catalog.
- Customer can create order and reach order details.
- Tailor can login and access active orders and details.
- Admin panel login page is reachable and admin can open dashboard.
- No repeated 500 errors in Laravel logs.

## 6) Rollback plan
1. Put app in maintenance mode if needed.
2. Redeploy previous known-good artifact.
3. Rebuild caches for the restored version.
4. Restart queue/Reverb processes.
5. Validate critical routes and login.
6. Communicate rollback status and incident summary.

### 6.1) Rollback trigger examples
- Repeated login/auth failures affecting core users.
- Order create/accept flow fails at high rate.
- Broad 500 error spike after cutover.
- Queue or Reverb process instability with user-visible impact.
- Migration issue with unsafe data impact risk.

## 7) First-hour watch checklist
- Monitor Laravel error rate and auth failures.
- Monitor queue backlog and failed jobs.
- Monitor Reverb process stability and websocket/auth errors.
- Confirm no asset mismatch (manifest/build references resolve).

## 8) Final release confirmation
- Release owner:
- Deploy timestamp:
- Commit/tag:
- Result: `SUCCESS` / `ROLLED BACK`
- Follow-up ticket links:
