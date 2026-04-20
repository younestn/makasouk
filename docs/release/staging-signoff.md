# Staging Signoff (Phase 9)

## 1) Purpose
This document is the final staging gate before production cutover.

## 2) Mandatory criteria
- CI `quality-gates` job is green on the candidate commit.
- CI `e2e` job is green on the candidate commit.
- `docs/release/manual-qa-matrix.md` completed with no open `BLOCKER`.
- No unresolved backend/frontend regressions affecting auth, orders, realtime, or admin access.

## 3) Staging verification checklist

| Area | Status | Notes |
|---|---|---|
| Public website routes healthy |  |  |
| SPA login and role redirects healthy |  |  |
| Customer core flow healthy |  |  |
| Tailor core flow healthy |  |  |
| Admin panel login and resource browse healthy |  |  |
| Realtime baseline healthy |  |  |
| Queue worker baseline healthy |  |  |
| Logs free of repeated 500/auth failures |  |  |

## 4) Blocker tracking

| ID | Description | Severity | Owner | ETA | Status |
|---|---|---|---|---|---|
|  |  |  |  |  |  |

Guideline:
- `BLOCKER`: must be fixed before production release.
- `MAJOR`: requires explicit product/business approval to defer.
- `MINOR`: can be deferred with tracking ticket.

## 5) Signoff owners
- Engineering lead:
- QA/UAT lead:
- Product owner:
- Operations/DevOps:

## 6) Final staging recommendation
- Result: `GO` / `NO-GO`
- Date:
- Release candidate commit:
- Notes:
