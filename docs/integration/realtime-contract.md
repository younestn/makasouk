# Realtime Contract (Phase 4)

## Overview
This document defines the stable Realtime/Broadcasting contract for customer and tailor clients.

## Transport
- Laravel Broadcasting + Reverb
- Private channels only
- Broadcast auth endpoint: `POST /broadcasting/auth`
- Auth guard: Sanctum token

## Channel Names
- `private-customer.{customerId}`
- `private-tailor.{tailorId}`
- `private-admin.{adminId}` (reserved for backoffice use)

## Subscription Rules
- Customer can subscribe only to `private-customer.{ownId}`.
- Tailor can subscribe only to `private-tailor.{ownId}`.
- Tailor must be approved (`approved_at != null`) to subscribe to tailor channels.
- Admin can subscribe only to `private-admin.{ownId}`.
- Suspended users cannot authenticate private channels.

## Event Names
- `order.created`
- `order.accepted`
- `order.status_updated`
- `order.cancelled_by_customer`
- `order.cancelled_by_tailor`

## Shared Event Envelope
All order-related events emit the same envelope structure:

```json
{
  "event": "order.status_updated",
  "occurred_at": "2026-04-20T02:30:15.000000Z",
  "order": {},
  "meta": {}
}
```

- `event`: explicit event name.
- `occurred_at`: UTC ISO-8601 timestamp.
- `order`: normalized order payload.
- `meta`: event-specific metadata (optional for some events).

## Event Payload Contracts

### `order.created` (to tailor channels)
- Channels: all matched `private-tailor.{id}`
- `order` includes:
  - `id`, `status`, `customer_id`, `tailor_id`, `product_id`
  - `measurements`
  - `delivery.latitude`, `delivery.longitude`
  - `product.id`, `product.name`, `product.category_id`, `product.category_name`, `product.price`, `product.pricing_type`
  - `accepted_at`, `created_at`, `updated_at`
- `meta` includes:
  - `notified_tailor_ids`
  - `distances_by_tailor_id` (km)

### `order.accepted` (to notified tailors + customer)
- Channels:
  - `private-tailor.{id}` for notified candidates
  - `private-customer.{customerId}`
- `order` includes:
  - base order fields + participant summary (`customer`, `tailor`, `product` names/ids)
- `meta` includes:
  - `accepted_by_tailor_id`
  - `notified_tailor_ids`

### `order.status_updated` (to customer)
- Channel: `private-customer.{customerId}`
- `order` includes:
  - base order fields + participant summary
- No extra `meta` required.

### `order.cancelled_by_customer` (to assigned tailor)
- Channel: `private-tailor.{tailorId}` when order already assigned.
- `order` includes base order fields including `cancellation_reason`.
- `meta.cancelled_by = "customer"`

### `order.cancelled_by_tailor` (to customer)
- Channel: `private-customer.{customerId}`
- `order` includes base order fields including `cancellation_reason`.
- `meta.cancelled_by = "tailor"`

## Security Notes
- Payloads intentionally avoid sensitive internal fields (for example PostGIS geometry columns).
- Channel-level authorization is the primary access boundary; clients must never trust cross-user channel data.

## Common Auth Errors
- `401` from `/broadcasting/auth`: missing or invalid Sanctum token.
- `403` from `/broadcasting/auth`: wrong role/channel ownership, unapproved tailor, or suspended account.