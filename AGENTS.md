# PROJECT KNOWLEDGE BASE

**Generated:** 2026-11-06
**Commit:** ce7917b
**Branch:** main

## OVERVIEW
Ravetix: ticket shop + admin back office for raves/events. Laravel 9, PHP 8.1, MySQL, lorisleiva/laravel-actions, Blade (CoreUI/Bootstrap 4 via CDN), Laravel Mix 6.

## STRUCTURE
```
rave/
├── app/
│   ├── Actions/      # business logic, one AsAction class per use case (see its AGENTS.md)
│   ├── Services/     # CartService, OrderService, NavigationService, PayPal/PayPalClient
│   ├── Contracts/    # Cart, Navigation interfaces; bound in ContentPageProvider
│   ├── Models/       # Eloquent, subfolders per aggregate (see its AGENTS.md)
│   ├── Casts/, ValueObjects/  # MoneyCast, Money
│   ├── Console/      # 7 commands + Kernel schedule
│   └── Http/         # Controllers split Admin/Site/Auth/Api/V1/Admin/User; Requests flat
├── routes/           # web.php (admin, cart, paypal, cron, CMS catch-all), api.php (sanctum v1)
├── resources/{views,lang}  # Blade; lang de+en
├── config/           # custom: rave.php, panel.php, paypal.php, paypal_ff.php
├── database/         # 42 migrations, 6 seeders
├── docker/8.1 + docker-compose.yml   # Sail dev stack
├── Dockerfile + docker-compose.yml.prod + deployment/  # prod stack, nginx
├── docs/architecture/  # overview, known issues (KI-xx), payment system, roadmap
└── tests/            # Unit, Feature (phpunit); Browser (Dusk, broken)
```
Ignore generated/vendored: `_ide_helper*.php`, `.phpstorm.meta.php`, `public/font/`.

## WHERE TO LOOK
| Task | Location | Notes |
|------|----------|-------|
| Add to cart / checkout | `Http/Controllers/Site/CartController`, `Actions/Cart/*` | cart id in session `cartId` |
| Order creation | `Services/OrderService::makeFromCart` | one DB tx, row locks, stock decrement |
| Mark paid | `Actions/Payment/ApplyPaymentToTransaction` | single "paid" path |
| PayPal online | `Actions/Payment/PayPal/*`, `Site/PayPalController`, `PayPalWebhookController` | webhook idempotent via `PayPalWebhookEvent` |
| PayPal friends+family | `Actions/Payment/PayPalFriendsFamily`, `Console/Commands/PayPalFriendsFamilyScanReport` | offline CSV reconciliation |
| Permissions | `Http/Middleware/AuthGates` | Gate per permission title |
| Scheduled jobs | `Console/Kernel.php` | also triggerable via cron HTTP route |
| Known bugs/design | `docs/architecture/` | KI-01..KI-31 |

## CODE MAP
| Symbol | Type | Location | Refs | Role |
|--------|------|----------|------|------|
| Transaction | model | `app/Models/Transaction.php` | 67+ (grep lines) | payment state, reference |
| Order / OrderItem | model | `app/Models/Order.php` | 72+ | purchase |
| EventTicket / EventTicketCode | model | `app/Models/` | 65+ / 43 | ticket type / one code per stock unit |
| Cart / CartItem | model | `app/Models/` | 36 | session cart |
| OrderService | service | `app/Services/OrderService.php` | - | order creation |
| ApplyPaymentToTransaction | action | `app/Actions/Payment/` | - | partial payments, PAID state |

Reference counts are grep line counts, not LSP (centrality unmeasured by LSP).

## CONVENTIONS
- Admin CRUD is hand-scaffolded per resource: controller + Store/Update/MassDestroy requests + 4 views + `massDestroy` route before `Route::resource`.
- Auth via Gates (`abort_if(Gate::denies('<snake>_<action>'), 403)`), repeated in FormRequest `authorize()`. Not Policies (only `UserPolicy`).
- Naming: URIs/routes kebab, view dirs + lang keys camelCase, permissions snake.
- i18n lives in `resources/lang/{de,en}` AND root `lang/en*`; add keys to both de and en.
- `env()` only inside `config/` (enforced by `tests/Unit/NoEnvOutsideConfigTest`).
- Money: `App\ValueObjects\Money` + `MoneyCast`; `@money` Blade directive.

## ANTI-PATTERNS (THIS PROJECT)
- NEVER put `/{path}` CMS catch-all anywhere but last in `routes/web.php`.
- NEVER run `composer update` casually: hooks regenerate the ide-helper files.
- NEVER call `env()` outside config.
- Do not use `Actions/Payment/PayTransaction.php` (empty stub); real one is `PayPalFriendsFamily/PayTransaction`.
- `schleuse.eu` is hardcoded in nginx conf and admin layout og:image.

## UNIQUE STYLES
- Order listener `OrderEventTicketGenerationListener` is synchronous: a failure rolls back the whole order.
- Ticket codes are pre-generated at ticket creation (one per stock unit) and claimed on order.
- `docker-compose.yml.prod` must be passed with `-f`; `*.off` nginx files are disabled.

## COMMANDS
```bash
composer install
npm run dev | watch | prod          # Laravel Mix
php artisan test                    # Unit+Feature; needs MySQL (SQLite fails migrations, KI-18)
vendor/bin/pint                     # lint, not wired to CI
./vendor/bin/sail up                # dev stack
php artisan schedule:run            # jobs: orders:finish, orders:payment-check, paypal_ff scan, ticket-code cleanup
```

## NOTES
- Tests/Browser (Dusk) cannot run: no `DuskTestCase`, missing `User` imports, stale route name.
- `EventTicketCode::delete()` guard is inverted (KI-27): unsold codes can't be deleted.
- `AuthGates` loads all roles/permissions every request.
- `deployment/nginx/rave.conf` s3 block may lack a semicolon; unverified.
- README is stock Laravel; real notes are in `docs/architecture/`.
