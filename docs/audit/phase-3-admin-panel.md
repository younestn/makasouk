# Phase 3 - Admin Panel Foundation (Filament)

## Packages added
- `filament/filament` (Laravel 11 compatible target)

## Admin panel URL
- `/admin-panel`

## Local admin credentials
- `admin@makasouk.local` / `Admin@12345`

## Resources implemented
- UserResource (role/suspension/pending filters + approve/suspend/unsuspend actions)
- CategoryResource
- ProductResource
- OrderResource (observability-first, view/list only)
- ReviewResource

## Access control strategy
- `User::canAccessPanel()` restricts panel to admin + non-suspended users.
- Filament panel registered with auth middleware.
- API RBAC remains policy-driven and unchanged for existing routes.

## Dangerous actions and safeguards
- User suspend/unsuspend requires explicit action.
- Self-suspension is disabled in UserResource action.
- Category deletion shown only when no products are attached.
- Order resource avoids unsafe manual status mutation.

## Remaining gaps before frontend/mobile integration
- Full Filament runtime install must be executed in environment with composer package access.
- Additional Filament component-level tests can be added once runtime dependencies are installed.
- Optional localization pass for Arabic panel labels/messages.
