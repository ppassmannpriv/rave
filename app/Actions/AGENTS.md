# app/Actions

18 single-purpose `AsAction` classes (lorisleiva/laravel-actions): the business-logic layer. Score high: 18 files, distinct domain, 7 subdomain dirs.

## STRUCTURE
`Cart/` `Order/` `Payment/` (+`PayPal/`, `PayPalFriendsFamily/`) `EventTicket/` `EventTicketCode/`

## WHERE TO LOOK
| Task | File |
|------|------|
| Cart mutations | `Cart/{Add,Remove,Update}TicketInCart`, `ClearCart` |
| Order from cart | `Cart/CreateOrderFromCart` (thin wrapper over `OrderService`) |
| Claim ticket codes | `Order/AttachEventTicketCodeToOrderItemAction` |
| Cancel / finish order | `Order/CancelOrderAction`, `FinishOrderEmailUserAction` |
| Mark paid | `Payment/ApplyPaymentToTransaction` |
| PayPal | `Payment/PayPal/{Start,Capture,ApplyPayPalCapture,ProcessPayPalWebhookEvent}` |
| Offline PayPal F&F | `Payment/PayPalFriendsFamily/PayTransaction` |
| Reminders | `Payment/PaymentReminderAction` |
| Pre-generate codes | `EventTicket/CreateCodesForNewEventTicket` |

## CONVENTIONS
- Called from controllers, listeners, and console commands; commands are thin wrappers.
- Payment results always funnel through `ApplyPaymentToTransaction` (stores `PartialTransaction`).
- `ApplyPayPalCapture` counts only `COMPLETED` captures in the configured currency.
- F&F matching idempotency key: `'ff-' . sha1(row)` as partial `external_id`.

## ANTI-PATTERNS
- `Payment/PayTransaction.php` is an empty stub: do not extend it.
- `AttachEventTicketCodeToOrderItemAction` runs inside the order transaction: do not make it async.
