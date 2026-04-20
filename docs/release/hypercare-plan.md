# Hypercare Plan (Phase 10)

## 1) Purpose
Define the post-launch monitoring window, ownership, and escalation thresholds for the first production release.

## 2) Hypercare windows

### First 1 hour (continuous monitoring)
- Confirm critical routes return healthy responses:
  - `/`
  - `/app/login`
  - `/admin-panel/login`
- Verify customer login and catalog load.
- Verify tailor login and active-orders load.
- Verify queue worker is alive and not restart-looping.
- Verify Reverb is running and no auth-flood errors appear.
- Verify no repeated 500-level spikes in `storage/logs/laravel.log`.

### First 24 hours
- Re-run critical smoke checks at least 3 times (start/mid/end of day).
- Review failed jobs growth and top exception signatures.
- Review authentication errors for abnormal spikes.
- Verify realtime order events are being consumed without persistent reconnect loops.
- Confirm asset/version consistency after any hotfix deployment.

### Launch week (daily checks, days 2-7)
- Daily error-log review (top 5 recurring errors).
- Daily queue and failed-job trend review.
- Daily spot-check of customer order creation and tailor order handling.
- Daily admin-panel access and core resource browse sanity check.

## 3) Suggested ownership model
- Release commander: coordinates go/no-go and rollback decisions.
- Backend owner: API/auth/orders/realtime server health.
- Frontend owner: SPA/public UX and route-level smoke coverage.
- Ops owner: process supervision, infra metrics, deployment integrity.
- QA owner: manual matrix execution and blocker tracking.

## 4) Monitoring touchpoints
- Laravel logs: `storage/logs/laravel.log`
- Failed jobs: `php artisan queue:failed`
- Queue worker process status (supervisor/systemd)
- Reverb process status/log stream
- CI dashboard status for release commit
- E2E artifact history for latest release run

## 5) Classification rules
- `BLOCKER`:
  - core login is broken for customer/tailor/admin
  - order create/accept/status flow is broken
  - repeated 500 errors with user-visible impact
  - unrecoverable migration/data integrity issue
- `MONITOR-ONLY`:
  - minor UI text/layout defects
  - non-critical localization content gaps
  - low-frequency recoverable warnings without customer impact

## 6) Escalation and response time
- `BLOCKER`: immediate escalation, start incident response within 15 minutes.
- `MAJOR`: owner assignment within 1 hour, mitigation plan same day.
- `MINOR`: triage into backlog with owner and ETA.

## 7) Hypercare exit criteria
- No open `BLOCKER` issues.
- Stable auth/order/realtime signals for 72 hours.
- Queue and logs show no unresolved high-severity pattern.
- Open post-launch issues are tracked with owners and planned fixes.
