# Immutable Quote REST API

This module does not replace or duplicate Adobe Commerce B2B's Negotiable Quote API. Creation
(`POST /V1/negotiableQuote/request`), listing/detail (My Quotes), checkout, and item management
all remain native `Magento_NegotiableQuote` endpoints. This module adds exactly one capability:
an immutable lock on top of an existing Negotiable Quote, changeable only through this API.

All endpoints require an Adobe Commerce admin token or OAuth integration token with the matching `Logiscenter_ImmutableQuote` ACL. Send `Authorization: Bearer <token>` and JSON content type. The default limit is 100 requests per hour per identity and operation; breaches return Magento's localized client error with a retry instruction.

| Method | Route | ACL | Purpose |
|---|---|---|---|
| GET | `/V1/logiscenter/negotiable-quotes/:quoteId/immutable` | view | Read the lock state of a Negotiable Quote |
| PUT | `/V1/logiscenter/negotiable-quotes/:quoteId/immutable` | set_immutable | Lock or unlock a Negotiable Quote (API only) |

## Examples

Lock an existing negotiable quote:

```
PUT /V1/logiscenter/negotiable-quotes/128/immutable
```

```json
{
  "isImmutable": true
}
```

Response:

```json
{
  "quote_id": 128,
  "is_immutable": true,
  "immutable_locked_at": "2026-09-23 14:05:00",
  "immutable_locked_by": 7
}
```

Unlock the same quote by sending `{ "isImmutable": false }`. `immutable_locked_at`/`immutable_locked_by` are cleared automatically.

Typical errors: quote is not a Negotiable Quote (404), unauthorized ACL (403), and throttling (client error with retry guidance). Magento serializes these in its standard Web API error envelope.

Every attempt to mutate a locked quote's items, quantities, addresses, shipping method or coupons
through the native cart/checkout APIs is refused by `QuoteMutationGuard` and recorded in the audit
trail, regardless of which client made the request. The guard sits on the customer/API-facing
service layer only (`Quote\Item\Repository`, `CouponManagement`, `BillingAddressManagement`,
`ShippingInformationManagement`, `ShippingMethodManagement`, `QuoteRepository::save`) — never on the
raw `Quote` model, which Magento also uses internally to rebuild in-memory quote snapshots for
**My Quotes** history and grid rendering.

Within that service layer, only a genuine change is blocked: adding a new item or changing an
existing item's quantity, and an address/shipping-method payload that actually differs from what
was negotiated. Checkout's own idempotent resubmission of the already-set address, shipping method
and item price/tax refresh (triggered by "Proceed to Checkout" and "Send for review") is left
alone, so both actions keep working on a locked quote. Item/coupon removal is always blocked; there
is no legitimate resubmission case for those.

`is_immutable` is also exposed as an extension attribute on `CartInterface` (`CartExtensionAttributePlugin`),
so it is present on any quote/cart payload returned by the standard `/V1/carts/:cartId` endpoints,
and as `window.checkoutConfig.logiscenterIsImmutableQuote` for checkout JS. The native **My Quotes**
grid (storefront) shows an "Immutable" column reflecting the same flag.