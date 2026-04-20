# Web Client Foundation (Phase 5)

## 1) Frontend structure

The Vue client is mounted under `/app/*` and uses Vite assets from Laravel.

```text
resources/js/
  main.js
  App.vue
  router/
    index.js
    guards.js
  stores/
    auth.js
    realtime.js
  services/
    http.js
    authService.js
    catalogService.js
    customerOrderService.js
    tailorService.js
    realtimeService.js
    errorMessage.js
  composables/
    useRealtimeBootstrap.js
  layouts/
    CustomerLayout.vue
    TailorLayout.vue
  pages/
    auth/LoginPage.vue
    common/ForbiddenPage.vue
    customer/*
    tailor/*
  components/
    common/*
    catalog/*
    orders/*
  utils/
    orderStatus.js
```

Server-side SPA host:
- `resources/views/spa.blade.php`
- `routes/web.php` route: `GET /app/{any?}`

## 2) Environment variables

Required frontend variables in `.env`:

```env
VITE_SPA_BASE=/app/
VITE_API_BASE_URL=/api
VITE_BROADCAST_AUTH_ENDPOINT=/broadcasting/auth
VITE_REVERB_KEY=makasouk-key
VITE_REVERB_HOST=127.0.0.1
VITE_REVERB_PORT=8080
VITE_REVERB_SCHEME=http
```

## 3) Auth flow

- Login request: `POST /api/auth/login`
- Token is persisted in localStorage (`makasouk_access_token`)
- Bootstrap user: `GET /api/auth/me`
- Logout: `POST /api/auth/logout`

Role behavior:
- `customer` -> routed to `customerDashboard`
- `tailor` -> routed to `tailorDashboard`
- `admin` -> redirected to `/admin-panel`

Suspended/invalid token handling:
- API errors are normalized in `services/http.js`
- On bootstrap failure, local auth state is cleared

## 4) Customer routes/pages

Client routes:
- `/app/customer`
- `/app/customer/catalog`
- `/app/customer/products/:id`
- `/app/customer/orders/create`
- `/app/customer/orders/active`
- `/app/customer/orders/history`
- `/app/customer/orders/:id`

Integrated APIs:
- `GET /api/catalog/categories`
- `GET /api/catalog/products`
- `GET /api/catalog/products/{product}`
- `POST /api/customer/orders`
- `GET /api/customer/orders-active`
- `GET /api/customer/orders-history`
- `GET /api/customer/orders/{order}`
- `PATCH /api/customer/orders/{order}/cancel`
- `POST /api/customer/orders/{order}/reviews`

## 5) Tailor routes/pages

Client routes:
- `/app/tailor`
- `/app/tailor/orders/active`
- `/app/tailor/orders/:id`
- `/app/tailor/availability`
- `/app/tailor/profile`

Integrated APIs:
- `GET /api/tailor/orders-active`
- `GET /api/tailor/orders-history`
- `POST /api/tailor/orders/{order}/accept`
- `PATCH /api/tailor/orders/{order}/status`
- `PATCH /api/tailor/orders/{order}/cancel`
- `GET /api/tailor/profile`
- `GET /api/tailor/availability`
- `PATCH /api/tailor/availability/toggle`

## 6) Realtime setup

Configured in `services/realtimeService.js` with:
- `laravel-echo`
- `pusher-js` (Reverb-compatible)

Auth:
- `POST /broadcasting/auth` with `Authorization: Bearer <token>`

Subscriptions:
- Customer -> `private-customer.{id}`
- Tailor -> `private-tailor.{id}`

Handled events:
- `order.created`
- `order.accepted`
- `order.status_updated`
- `order.cancelled_by_customer`
- `order.cancelled_by_tailor`

Realtime behavior:
- Tailor dashboard maintains incoming nearby offers from `order.created`
- Active/detail pages refresh on relevant realtime events
- Connection state and errors surface in the layout badges/banner

## 7) What is implemented now

- Vue 3 SPA shell with route guards and role separation
- Centralized API client with auth header injection
- Customer pages for catalog/order lifecycle consumption
- Tailor pages for order operations and availability/profile
- Minimal realtime consumption integrated to contract
- Basic frontend unit test (`vitest`) for status mapping utility

## 8) What remains for Phase 6

- UX polish and design system pass
- Robust pagination controls and richer empty/error UX
- Tailor-side dedicated endpoint for direct order detail fetch (to avoid detail reconstruction from list APIs)
- Global notification/toast system and stronger retry/reconnect strategies
- Broader frontend test coverage (component + flow tests)
- Optional API mocking/storybook-like tooling for parallel frontend work

## 9) Phase 6 completion addendum

Phase 6 finalized the public-facing web layer while keeping the Phase 5 SPA architecture intact.

### Public site entry and routes
- New Vue public entry: `resources/js/public/main.js`
- Public router and layout:
  - `resources/js/public/router/index.js`
  - `resources/js/public/layouts/PublicLayout.vue`
- Public pages:
  - `/`
  - `/how-it-works`
  - `/for-customers`
  - `/for-tailors`
  - `/faq`
  - `/contact`

Server rendering hosts:
- `resources/views/public-site.blade.php` for public pages
- `resources/views/spa.blade.php` for `/app/*`

### UX and reusable UI primitives
Reusable UI components introduced under `resources/js/components/ui`:
- `UiButton.vue`
- `UiCard.vue`
- `UiSectionHeader.vue`
- `UiStatBlock.vue`
- `UiAlert.vue`
- `UiFormField.vue`

Applied improvements across customer/tailor shells:
- clearer layout headers and navigation structure
- improved realtime connection indicators
- enhanced loading/error/empty states
- clearer stat blocks and section headers on dashboards/detail pages
- improved form readability on login and order creation

### Lightweight frontend test addition
- Added route registration test:
  - `resources/js/public/router/__tests__/publicRoutes.test.js`
