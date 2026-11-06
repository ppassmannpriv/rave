# app/Models

23 Eloquent models, subfolders per aggregate. Score high: 23 files, most-referenced symbols in the repo.

## STRUCTURE
`Order/OrderItem` `PaymentMethod/{PayPal,PayPalFriendsFamily}` `TimeSchedule/Shift` `Transaction/PartialTransaction`; top level: Cart, CartItem, Content*, Customer, Event, EventTicket, EventTicketCode, Order, Payment, PayPalWebhookEvent, Permission, Role, User.

## WHERE TO LOOK
| Task | File |
|------|------|
| Payment state, reference | `Transaction`, `Transaction/PartialTransaction` |
| Payment method registry | `PaymentMethod` (`CLASSMAP`, `selectableBy()`, `model()`) |
| F&F CSV columns/report dir | `PaymentMethod/PayPalFriendsFamily` (alias `paypal_ff`, `config('paypal_ff.columns.*')`) |
| Webhook dedupe | `PayPalWebhookEvent` |
| Admin check | `User::getIsAdminAttribute` (role id 1) |

## CONVENTIONS
- Explicit `$table` and `$fillable`; money columns use `MoneyCast`.
- `EventTicket` `created` hook fires `EventTicketCreatedEvent` (creates one code per stock unit).
- Subfolder models are namespaced by folder (e.g. `App\Models\Order\OrderItem`); check namespaces before importing.

## ANTI-PATTERNS
- `EventTicketCode::$fillable` includes timestamps/`deleted_at`; do not copy.
- `EventTicketCode::delete()` guard is inverted (throws when `orderItem` is null; KI-27). Pinned by tests: read `docs/architecture/01-known-issues.md` before changing.
