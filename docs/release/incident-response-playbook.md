# Incident Response Playbook (Phase 10)

## 1) Purpose
Provide a practical triage/containment workflow for common production incidents during launch and hypercare.

## 2) Fast severity model
- `SEV-1`: customer-visible outage in auth/orders/admin core flows.
- `SEV-2`: degraded behavior with workaround.
- `SEV-3`: non-critical defect.

## 3) Universal first-response steps
1. Acknowledge incident and assign incident lead.
2. Capture timestamp, affected surfaces, and first error signature.
3. Check latest deploy commit/tag and confirm if issue started post-deploy.
4. Decide: mitigate in-place or rollback.
5. Update stakeholders every 15 minutes for `SEV-1`.

## 4) Scenario playbooks

### 4.1 Login/auth failures
- Symptoms:
  - repeated 401/419/422 auth responses
  - login redirect loops
- Checks:
  - `APP_URL`, `SANCTUM_STATEFUL_DOMAINS`, `SESSION_DOMAIN`, `SESSION_SECURE_COOKIE`, `SESSION_SAME_SITE`
  - browser cookie presence and domain alignment
  - Laravel logs for auth/session exceptions
- Containment:
  - fix env mismatch and reload app config
  - if widespread and unresolved quickly, rollback

### 4.2 Reverb/realtime failures
- Symptoms:
  - connection failures, repeated reconnect loops, missing updates
- Checks:
  - Reverb process status
  - `REVERB_*` and `VITE_REVERB_*` alignment
  - `REVERB_ALLOWED_ORIGINS` coverage for active origins
  - `/broadcasting/auth` failures in logs
- Containment:
  - restart Reverb service
  - correct origin/env mismatch
  - fallback to manual refresh guidance temporarily if needed

### 4.3 Queue failures
- Symptoms:
  - failed jobs growth, delayed background processing
- Checks:
  - worker status/restarts
  - `QUEUE_CONNECTION`, `REDIS_*`
  - `php artisan queue:failed`
- Containment:
  - restart worker processes
  - fix failing job root cause; replay safe jobs after fix

### 4.4 Asset/build mismatch
- Symptoms:
  - blank pages, chunk load errors, stale asset references
- Checks:
  - `public/build/manifest.json` exists and matches release artifact
  - browser/network console chunk 404s
- Containment:
  - redeploy frontend assets for the same release commit
  - clear caches and hard refresh

### 4.5 Migration/database issues
- Symptoms:
  - deploy-time migration failure, SQL errors, data-shape mismatch
- Checks:
  - failed migration output and DB status
  - PostGIS extension presence if geo queries fail
- Containment:
  - stop further deploy steps
  - decide roll-forward hotfix migration vs rollback based on data risk

## 5) Rollback trigger criteria
- Any unresolved `SEV-1` beyond 30 minutes.
- Data integrity risk from migration/state mismatch.
- Core customer/tailor order flow is unavailable with no safe hotfix.

## 6) Immediate rollback sequence
1. Put app in maintenance mode if needed.
2. Redeploy previous known-good backend/frontend artifact.
3. Rebuild caches for restored version.
4. Restart queue and Reverb services.
5. Execute smoke checks (`/`, `/app/login`, `/admin-panel/login`, customer + tailor login).
6. Publish rollback communication and next-action plan.

## 7) Post-incident requirements
- Record incident timeline, root cause, mitigation, and preventive actions.
- Create follow-up tasks with owners and due dates.
- Update release docs/playbooks when new learning is found.
