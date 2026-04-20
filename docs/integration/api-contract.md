# API Contract (Phase 4)

## Auth Flow for Client Apps
1. Register or login.
2. Store returned Sanctum token.
3. Send `Authorization: Bearer <token>` on all API and broadcasting-auth requests.
4. Use `/api/auth/me` to hydrate current user + tailor profile metadata.

## Core Endpoints

### Auth
- `POST /api/auth/register`
- `POST /api/auth/login`
- `GET /api/auth/me`
- `POST /api/auth/logout`

### Catalog (authenticated + active users)
- `GET /api/catalog/categories`
- `GET /api/catalog/products`
- `GET /api/catalog/products/{product}`

### Customer
- `POST /api/customer/orders`
- `GET /api/customer/orders/{order}`
- `GET /api/customer/orders-active`
- `GET /api/customer/orders-history`
- `PATCH /api/customer/orders/{order}/cancel`
- `POST /api/customer/orders/{order}/reviews`

### Tailor (approved tailor only)
- `GET /api/tailor/profile`
- `GET /api/tailor/availability`
- `PATCH /api/tailor/availability/toggle`
- `GET /api/tailor/orders-active`
- `GET /api/tailor/orders-history`
- `POST /api/tailor/orders/{order}/accept`
- `PATCH /api/tailor/orders/{order}/status`
- `PATCH /api/tailor/orders/{order}/cancel`

## Response Shape Standards

### Single-entity responses
```json
{
  "message": "Optional action message",
  "data": {}
}
```

### Paginated list responses
```json
{
  "data": [],
  "links": {},
  "meta": {}
}
```

## Order Resource Contract
`OrderResource` now returns:
- IDs: `id`, `customer_id`, `tailor_id`, `product_id`
- `status`
- `measurements`
- `delivery.latitude`, `delivery.longitude`
- `cancellation_reason`
- `timestamps.accepted_at`, `timestamps.created_at`, `timestamps.updated_at`
- optional loaded relations: `product`, `customer`, `tailor`, `review`
- lifecycle hints:
  - `lifecycle.allowed_next_statuses_for_tailor`
  - `lifecycle.customer_can_cancel`
  - `lifecycle.tailor_can_cancel`
  - `lifecycle.is_terminal`

## Order Lifecycle Contract

### Main statuses
- `searching_for_tailor`
- `no_tailors_available`
- `accepted`
- `processing`
- `ready_for_delivery`
- `completed`
- `cancelled_by_customer`
- `cancelled_by_tailor`
- `cancelled`

### Tailor status transitions
- `accepted -> processing`
- `processing -> ready_for_delivery`
- `ready_for_delivery -> completed`

### Customer cancellation allowed in
- `searching_for_tailor`, `accepted`, `processing`

### Tailor cancellation allowed in
- `accepted`, `processing`, `ready_for_delivery`

### No-tailor-available behavior
If no nearby online tailor matches category/radius:
- order status becomes `no_tailors_available`
- API returns `201` with `data` order payload and matching meta
- no tailor broadcast is emitted

## Example: Create Order (Customer)
Request:
```json
{
  "product_id": 12,
  "measurements": { "height": 170, "waist": 80 },
  "customer_location": { "latitude": 33.5731, "longitude": -7.5898 }
}
```

Response:
```json
{
  "message": "Order created and broadcast to nearby tailors.",
  "data": {
    "id": 44,
    "status": "searching_for_tailor"
  },
  "meta": {
    "matching_status": "searching_for_tailor",
    "matched_tailors_count": 3
  }
}
```

## Common Error Cases
- `401`: missing/invalid token.
- `403`: role not allowed, suspended user, or unapproved tailor route access.
- `404`: requesting resource that does not exist or inactive catalog product detail.
- `409`: duplicate review or already-accepted race condition.
- `422`: invalid payload or invalid status transition/cancellation state.

## What Remains for UI Implementation
- Web/mobile teams still need to implement:
  - screen-level routing and state management
  - websocket client integration and reconnection strategy
  - local caching/offline UX
  - presentation-specific localization/content rules