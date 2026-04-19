# Phase 2 Runtime Validation

## Runtime issues found
1. Composer install was failing intermittently in restricted environments (packagist 403 tunnel errors).
2. Laravel runtime commands depend on successful dependency installation and package discovery.
3. `RegisterRequest` email rule with DNS validation can fail in isolated/offline dev networks.
4. `DemoDataSeeder` used a query builder bulk insert path that bypassed Eloquent cast flow for complex attributes.

## Fixes applied
- Hardened RBAC using Policies (`OrderPolicy`, `ReviewPolicy`, `UserPolicy`).
- Added middleware guardrails (`EnsureUserIsActive`, `EnsureTailorIsApproved`) and wired aliases in bootstrap.
- Refactored controllers to use `authorize()` + dedicated FormRequests + API Resources.
- Relaxed register email validation from `email:rfc,dns` to `email:rfc` for stable local/runtime behavior.
- Fixed demo seeding path by creating orders via Eloquent `create()` instead of query builder `insert()` so casts are applied safely.

## Final auth strategy
- Sanctum token auth with explicit register/login/logout/me endpoints.
- Suspended users blocked at login and by request middleware (`active`).

## Final RBAC strategy
- Centralized policies for order/review/user actions.
- Route-level middleware for account activity and tailor approval.

## Broadcasting/Reverb setup
- Private channels stay protected with `auth:sanctum` broadcast auth.
- Reverb remains configured as default local broadcast driver.

## Commands and results
- `composer install`
- `php artisan key:generate`
- `php artisan migrate:fresh --seed`
- `php artisan test`
- `php artisan queue:listen`
- `php artisan reverb:start`

> Run the above in an environment with package network access and enabled PostgreSQL/PostGIS + Redis services.

## Technical debt before admin panel phase
- Add CI workflow with real PostgreSQL/PostGIS service and Redis to guarantee green runtime checks on every PR.
- Add dedicated load checks for queue + broadcast throughput and event fanout.
