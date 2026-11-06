# 00 — System Overview

_Last updated: 2026-10-16. This document set was produced by an architecture review of the codebase as it exists on branch `master` at commit `ce7917b`._

## What this is

`hackerman/ravetix` ("Rave") is a Laravel 9 web application for a DIY music-events/festival community: it publishes event info as a lightweight CMS, sells tickets for those events through a cart/checkout flow, and reconciles payment via a custom PayPal "Friends & Family" workflow. It is a small, single-maintainer, self-hosted project — not an enterprise system — and the modernization plan in this doc set is scoped accordingly (see [05-roadmap.md](./05-roadmap.md)).

## Stack as it exists today

| Concern | Current choice | Notes |
|---|---|---|
| Framework | Laravel `^9.19`, PHP `^8.0.2` | PHP 8.0 is already EOL (Nov 2023). `composer.json` sets `minimum-stability: dev`, which is unusual/risky for a production app. |
| Auth / UI | `laravel/ui` + Blade + Bootstrap | Classic server-rendered MVC, not Livewire/Inertia. |
| Admin CRUD | `yajra/laravel-datatables-oracle` | Server-side datatables for every admin resource. |
| Business actions | `lorisleiva/laravel-actions` (`AsAction` trait) | Single-purpose invokable classes under `app/Actions/*`, e.g. `AddTicketToCart`, `CreateOrderFromCart`, `PayTransaction`. |
| Media | `spatie/laravel-medialibrary` | Event images, content page images. |
| API | `laravel/sanctum`, a small `routes/api.php` `v1` admin API | Token-protected, admin-only today. |
| CSV | `league/csv` | Used to parse the PayPal Friends & Family export (see [02-payment-system.md](./02-payment-system.md)). |
| Search | Laravel Scout + Meilisearch | |
| Storage | MinIO (S3-compatible) via `MEDIA_DISK=s3-media` | Also used to store uploaded PayPal CSV reports. |
| DB / cache / queue | MySQL 8, Redis, `QUEUE_CONNECTION=sync` | Queue is synchronous — there is no real background worker today. |
| Error tracking | Sentry | |
| Frontend build | **None, in practice.** `webpack.mix.js` + `package.json` (`laravel-mix`) and a `vite.config.js` both exist in the repo, but `resources/views/layouts/app.blade.php` uses neither `@vite(...)` nor `mix(...)` — it hand-links static files (`asset('css/custom.css')`, `asset('js/main.js')`) that already sit in `public/css`/`public/js`, and no `mix-manifest.json` exists anywhere under `public/`. The real frontend is a dozen+ third-party libraries (Bootstrap 4.1.3, CoreUI 3.2, Font Awesome 4.7/5.2, DataTables 1.10.19, Select2 4.0.5, bootstrap-datetimepicker, Dropzone) pulled directly from public CDNs with no Subresource Integrity hashes and no local pinning — see KI-14. |
| Deployment | Hand-rolled `docker-compose.yml.prod` (app/db/nginx/certbot/redis/minio) | Nginx config hardcodes `server_name schleuse.eu`. See [04-deployment.md](./04-deployment.md). |
| CI | **None.** No `.github/` directory exists in the repository at all. | |
| Tests | `tests/Unit`, `tests/Feature`, `tests/Browser` (Dusk) exist, but effectively zero of them currently run correctly or are even registered — see [01-known-issues.md](./01-known-issues.md) and [03-testing-strategy.md](./03-testing-strategy.md). |

## Domain model

```
Event ──< EventTicket (ticket_type, price, from/to window, stock, cap) ──< EventTicketCode (issued codes)
                                                                         ⌐
Cart (device_type) ──< CartItem ──> EventTicket                          |
                                                                          |
Order (status) ──< OrderItem ──────────────────────────────────────────-┘
   │
   └──> Transaction (state, reference, amount) ──< PartialTransaction (audit trail of every reconciliation attempt)
              │
              └──> PaymentMethod (alias/name/FQN classmap) ──> PaymentMethod\PayPalFriendsFamily

ContentPage ──< ContentCategory, ContentTag   (lightweight CMS, nav-driven via NavigationService)
Permission ──< Role ──< User                  (admin RBAC)
TimeSchedule ──< TimeSchedule\Shift            (volunteer/staff shift planning — orthogonal to payments)
Customer                                       (flat contact record, separate from the auth User model)
```

Key state machines:
- **`Order.status`**: `initialized` → `processing` → `paid` / `refunded` / `closed` / `cancelled`.
- **`Transaction.state`**: `initialized` → `ordered` → `paid` / `successful` / `cancel`.

## Request flow: buying a ticket

1. `GET /events` / `GET /events/{id}` — `Site\EventController` lists/shows events.
2. `POST /cart/add` — `Site\CartController::addToCart` → `AddTicketToCart` action → `CartService` (session-id-keyed, DB-backed `Cart`/`CartItem`).
3. `GET /cart` — shows cart + the list of **active** `PaymentMethod` rows.
4. `POST /cart/order` — `Site\CartController::orderCart` → `CreateOrderFromCart` action → `OrderService::makeFromCart()`:
   - finds-or-creates a `User` by email,
   - decrements `EventTicket.stock`,
   - creates `OrderItem` + `Order`,
   - creates a `Transaction` (this is where the 10-character customer-facing reference is generated — see [02-payment-system.md](./02-payment-system.md)),
   - dispatches `OrderEventTicketGenerationEvent` (issues `EventTicketCode`s),
   - emails the customer and the site owner.
5. `GET /cart/success` — confirmation page, reads the order back out of the session.

**The payment itself is not confirmed synchronously anywhere in this flow.** Confirmation happens later, out-of-band — see [02-payment-system.md](./02-payment-system.md) for why this matters and what actually drives it today.

## How to read the rest of this doc set

- [01-known-issues.md](./01-known-issues.md) — every concrete bug/gap found during this review, with file:line references and a proposed fix, so none of it is lost or re-discovered later.
- [02-payment-system.md](./02-payment-system.md) — the payment-continuity-critical deep dive. Read this before touching anything under `app/Actions/Payment`, `app/Models/Transaction*`, `app/Models/PaymentMethod*`, or `app/Console/Commands/PayPalFriendsFamilyScanReport.php`.
- [03-testing-strategy.md](./03-testing-strategy.md) — what gets tested, in what order, with what tooling.
- [04-deployment.md](./04-deployment.md) — target CI/CD and deploy design.
- [05-roadmap.md](./05-roadmap.md) — phased workstreams, each tagged with a risk/effort tier and an assigned executor (background agent vs. careful supervised work).
