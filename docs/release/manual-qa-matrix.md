# Manual QA Matrix (Phase 9)

## 1) Scope and audience
This checklist is intended for staging/UAT testers, including non-developers.

## 2) Preconditions
- Deployment target is staging (not production).
- Database is freshly migrated and seeded: `php artisan migrate:fresh --seed`.
- Queue worker and Reverb server are running during tests.
- Tester has these credentials:
  - Admin: `admin@makasouk.local` / `Admin@12345`
  - Customer: `customer@makasouk.local` / `Password@123`
  - Tailor: `tailor@makasouk.local` / `Password@123`

## 3) Severity model
- `BLOCKER`: release must stop.
- `MAJOR`: release can proceed only with business signoff and tracked workaround.
- `MINOR`: non-blocking cosmetic/content issue.

## 4) Public website checks

| ID | Severity if Fails | Steps | Expected Result |
|---|---|---|---|
| P-01 | BLOCKER | Open `/` | Home page renders with working CTA buttons |
| P-02 | MAJOR | Open `/how-it-works` | Page loads with no broken layout |
| P-03 | MAJOR | Open `/for-customers` | Page loads and links are usable |
| P-04 | MAJOR | Open `/for-tailors` | Page loads and links are usable |
| P-05 | MAJOR | Open `/faq` and `/contact` | Both pages load without runtime errors |

## 5) Auth and route-guard checks

| ID | Severity if Fails | Steps | Expected Result |
|---|---|---|---|
| A-01 | BLOCKER | Open `/app/login` | Login form renders (email/password present) |
| A-02 | BLOCKER | Login as customer | Redirect to `/app/customer` |
| A-03 | BLOCKER | While customer is logged in, open `/app/tailor/orders/active` | Access denied (forbidden route behavior) |
| A-04 | BLOCKER | Login as tailor | Redirect to `/app/tailor` |
| A-05 | BLOCKER | Open `/admin-panel/login` | Admin login page responds and loads |

## 6) Customer UAT checks

| ID | Severity if Fails | Steps | Expected Result |
|---|---|---|---|
| C-01 | BLOCKER | Open `/app/customer/catalog` | Product list renders with pagination |
| C-02 | MAJOR | Open any product details page | Product details render correctly |
| C-03 | BLOCKER | Create an order from `/app/customer/orders/create` | Successful submit redirects to order details page |
| C-04 | MAJOR | Open `/app/customer/orders/active` | Active orders list renders without errors |
| C-05 | MAJOR | Open `/app/customer/orders/history` | History page renders with stable paging |

## 7) Tailor UAT checks

| ID | Severity if Fails | Steps | Expected Result |
|---|---|---|---|
| T-01 | BLOCKER | Open `/app/tailor/orders/active` | Active orders list renders |
| T-02 | BLOCKER | Open active order details | Details page renders and actions are visible when valid |
| T-03 | MAJOR | Open `/app/tailor/availability` and toggle | Status updates successfully |
| T-04 | MAJOR | Open `/app/tailor/profile` | Profile summary renders without API/runtime errors |

## 8) Admin UAT checks

| ID | Severity if Fails | Steps | Expected Result |
|---|---|---|---|
| AD-01 | BLOCKER | Login to `/admin-panel` as admin | Dashboard opens |
| AD-02 | MAJOR | Open Users/Categories/Products/Orders/Reviews | Resources load with no 500 errors |
| AD-03 | BLOCKER | Verify non-admin cannot use admin panel | Access denied for non-admin users |

## 9) Realtime and localization checks

| ID | Severity if Fails | Steps | Expected Result |
|---|---|---|---|
| R-01 | MAJOR | Keep customer/tailor app open for 3-5 minutes | No repeated reconnect/error banner loops |
| R-02 | MAJOR | Perform order create/accept/status update flow | Active order screens refresh without manual reload in core cases |
| L-01 | MINOR | Toggle locale EN/AR in public and app shells | Language changes and RTL direction applies without layout break |

## 10) Post-deploy smoke and operational checks
- Confirm routes respond: `/`, `/app/login`, `/admin-panel/login`.
- Confirm worker is healthy: no rapid restarts, no failed job growth.
- Confirm Reverb is healthy: server running, no auth flood errors in logs.
- Confirm no repeated 500s in `storage/logs/laravel.log`.

## 11) Signoff record template
- Build: PASS / FAIL
- Frontend tests: PASS / FAIL
- Backend tests: PASS / FAIL
- Playwright E2E: PASS / FAIL
- Manual QA: PASS / FAIL
- Blockers found: YES / NO
- Final recommendation: release / staging only / hold
