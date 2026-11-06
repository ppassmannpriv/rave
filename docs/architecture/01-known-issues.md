# 01 — Known Issues Inventory

_Every concrete bug or structural gap found during this review, each with a file reference, why it matters, and a proposed fix. This is the ground truth the roadmap in [05-roadmap.md](./05-roadmap.md) is built from — items are referenced there by their ID (`KI-xx`)._

## Payment-critical

### KI-01 — "Regular PayPal" does not exist
**File**: `app/Actions/Payment/PayTransaction.php`
```php
public function handle(Transaction $transaction): void
{
}
```
The method body is empty. There is no PayPal SDK in `composer.json`/`composer.lock`, no webhook/IPN controller or route anywhere in the codebase, and no migration seeds a plain `paypal` row into `payment_methods` (only `paypal_ff` is seeded, in `database/migrations/2022_09_25_032840_create_pay_pal_f_f_payment_method.php`). **Standard PayPal checkout is not a working feature today — it needs to be built.** Treating it as "existing functionality to refactor" would be planning against a fiction; it is tracked as new work in the roadmap.
**Fix**: build it (see [02-payment-system.md](./02-payment-system.md) §"Building standard PayPal").
**STATUS — BUILT, NOT LIVE (Phase 5.4)**: standard PayPal now exists as a `paypal` payment method (seeded `active = false`, `admin_only = true`), implemented as a thin `Http`-facade client (`App\Services\PayPal\PayPalClient`) on the Orders v2 REST API, a server-side redirect flow (`StartPayPalPayment`, return/cancel routes, `CapturePayPalOrder`), and a signature-verified, idempotent webhook (`POST /webhooks/paypal`, events persisted in `paypal_webhook_events`). Both payment methods now share one "mark paid" implementation, `App\Actions\Payment\ApplyPaymentToTransaction`. Tested only against faked PayPal HTTP; it has NOT been run against the PayPal sandbox (that needs the separate Business account's credentials, see Phase 5.5). The empty `App\Actions\Payment\PayTransaction` stub is still present and unused.

### KI-02 — Underpayment is never even matched; it leaves zero trace (confirmed by test)
**File**: `app/Actions/Payment/PayPalFriendsFamily/PayTransaction.php`, `handle()`
**Sharper than first documented, confirmed by a passing characterization test** (`tests/Unit/Actions/Payment/PayPalFriendsFamily/PayTransactionTest.php::test_underpayment_is_never_matched_and_leaves_no_trace_pending_KI02_fix`): the lookup query itself - `Transaction::where(...)->where('amount', '<=', $transactionAmount)->...` - requires the **stored** amount to be less-than-or-equal-to what was **just paid**. A genuine underpayment (stored 25.00, paid 10.00) can never satisfy `25.00 <= 10.00`, so the transaction is never found at all. Execution hits `if ($transaction === null) { return; }` before the `PartialTransaction::create(...)` call is ever reached - meaning an underpayment leaves **no audit row, no state change, and no signal anywhere in the system**, not even the partial record originally assumed here.
**Fix**: the lookup needs a separate underpayment-aware path (e.g. also match on `reference` alone when no amount-qualifying transaction is found, then explicitly branch on `$transactionAmount < $transaction->amount`), recording the partial amount, optionally moving `Order.status` to a new `partially_paid` state (or reusing `processing` with a comment, matching the existing overpay convention), and notifying staff.
**STATUS — FIXED (Phase 5.1)**: the lookup now matches by `reference` + open state only (no amount filter), so an underpayment is found, a `PartialTransaction` audit row is created, and the transaction gets a visible "X still outstanding" comment. The transaction stays open (state unchanged) so a follow-up top-up payment with the same reference completes it against the cumulative total paid so far. Verified by `tests/Unit/Actions/Payment/PayPalFriendsFamily/PayTransactionTest.php::test_underpayment_is_recorded_with_a_visible_remaining_balance` and `::test_a_second_top_up_payment_completes_a_previously_underpaid_transaction` (10/10 tests passing).

### KI-03 — The scheduler's only trigger is an external HTTP pinger
**File**: `routes/web.php`
```php
Route::get('/' . env('CRON_ROUTE') , function(){
    Artisan::call('schedule:run');
    return 'OK';
});
```
`QUEUE_CONNECTION=sync` and there is no system crontab or supervisor entry running `schedule:run`. Every scheduled job — `PayPalFriendsFamilyScanReport` (the only working payment reconciliation), `FinishOrders`, `PaymentReminderCheck`, `CheckStock`, `CleanUpOrphanedEventTicketCodes` — only executes when some external uptime-monitor/cron-ping service hits this one URL. If that external pinger is ever disabled, misconfigured, or the URL changes, **every one of these jobs silently stops, including the only functioning payment-reconciliation path**, with no alert anywhere in the system. This is the single biggest "must always be available" risk in the entire codebase.
**Fix**: replace with a real scheduler (supervisor + system cron inside the app container, or a managed cron) plus failure alerting (see [04-deployment.md](./04-deployment.md)).

### KI-17 (cross-reference) — see below for the money-comparison bug found while writing KI-02's test

### KI-04 — Hardcoded German CSV column names
**File**: `app/Models/PaymentMethod/PayPalFriendsFamily.php`
```php
public const NOTE_COLUMN = 'Hinweis';
public const USER_EMAIL_COLUMN = 'Absender E-Mail-Adresse';
public const AMOUNT_COLUMN = 'Brutto';
```
The entire reconciliation pipeline depends on PayPal's German-locale CSV export column headers never changing. If PayPal changes the export format, renames a column, or the admin's PayPal account locale ever changes to English, every row fails `validateTransactionData()` and reconciliation silently degrades to the fuzzy email-matching fallback (or stops matching at all).
**Fix**: make column names configurable (env or config file) and add a startup/ingest check that fails loudly (not silently) when expected columns are missing, instead of only throwing a per-row `ValidationException` that gets caught and logged per-row.
**STATUS — FIXED (Phase 5.2)**: column names now come from `config/paypal_ff.php` (env `PAYPAL_FF_COLUMN_AMOUNT`, `PAYPAL_FF_COLUMN_NOTE`, `PAYPAL_FF_COLUMN_USER_EMAIL`; defaults are the current German headers, so behavior is unchanged without env overrides), read through `PayPalFriendsFamily::amountColumn()/noteColumn()/userEmailColumn()`. `payment_methods:paypal_ff:scan-report` now checks the CSV header before processing: if the amount or note column is missing it logs an error, exits 1, and leaves the file in place (previously every row failed individually and the file was still moved to `processed/`). Covered by `tests/Unit/Console/Commands/PayPalFriendsFamilyScanReportTest.php` and `PayTransactionTest::test_column_names_are_read_from_config`.

## Correctness bugs (non-payment)

### KI-05 — Sold-out error message is never actually shown
**File**: `app/Http/Controllers/Site/CartController.php::addToCart`
```php
return \Redirect::to('/cart')->with($soldOutException->getMessage());
```
`Redirect::with()` expects `with($key, $value)` or `with(array $data)`. Passing a bare string sets a session key equal to the message text with a `true`/null value — the Blade view has no way to read this back as a flash message. Customers who try to buy a sold-out ticket get silently redirected with no visible error.
**Fix**: `->with('error', $soldOutException->getMessage())` and confirm the cart view actually renders `session('error')`.
**STATUS — half-fixed**: the controller-side fix (`->with('error', ...)`) has been applied and verified (`php -l` clean). A follow-up check of the Blade views found the cart view does **not** currently `@include` any flash-message partial that reads `session('error')` — so the message is now correctly flashed to the session but still won't render on the page until the cart Blade view (or its layout) is updated to display it. Tracked as a small remaining Phase 1 item in [05-roadmap.md](./05-roadmap.md).

### KI-06 — `PaymentTest` (Dusk) fatals if ever run
**File**: `tests/Browser/PaymentTest.php`
```php
namespace Tests\Browser;
...
$admin = App\Models\User::find(1);
```
No `use App\Models\User;` import exists in the file. Inside `namespace Tests\Browser`, the bare reference `App\Models\User` resolves to `Tests\Browser\App\Models\User`, which does not exist — this fatals with a class-not-found error the moment it executes.
**Fix**: add `use App\Models\User;`.

### KI-07 — `PasswordControllerTest` methods are never discovered or run
**File**: `tests/Feature/Http/Controllers/User/Account/PasswordControllerTest.php`
The test methods (`user_can_update_own_profile`, `user_cannot_update_foreign_profile`) have no `test_` prefix and no `@test` annotation, so PHPUnit never discovers them — the suite reports green while this file contributes zero actual coverage. Both method bodies also call the global `factory(User::class)` helper (4 call sites total), which was removed from Laravel's model-factory API in Laravel 8+ (replaced by `User::factory()`); if the methods were ever discovered, they would fatal immediately.
**Fix**: rename both methods with a `test_` prefix, and replace all 4 `factory(User::class)->create(...)` call sites with `User::factory()->create(...)`.
**STATUS — blocked on a filesystem permission, flagged for the user**: this file is owned `root:root` (mode 644) on the NTFS-mounted repo path (`/mnt/c/Users/piete/projects/rave`, accessed via WSL). Both a delegated agent and direct edit attempts got `EACCES`/`Operation not permitted` trying to write to it; `sudo -n chown` also failed because passwordless sudo isn't configured. **Action needed from you**: either `sudo chown $(whoami) tests/Feature/Http/Controllers/User/Account/PasswordControllerTest.php` yourself (or fix it from the Windows side — check the file's Windows security/ownership properties), then this exact, already-specified fix can be applied in under a minute.

### KI-08 — `tests/Unit/ExampleTest.php` is a placeholder with CRLF endings
**File**: `tests/Unit/ExampleTest.php`
CRLF line endings (a Windows-checkout artifact) and a single `assertTrue(true)` — contributes no coverage and should be removed once real unit tests exist, not kept as a false "we have tests" signal.

### KI-15 — CMS page routes are registered via a live DB query at route-load time, blocking `route:cache`
**Files**: `routes/web.php` (the `foreach ($navigationService->getPages() as $page)` block), `app/Services/NavigationService.php::getPages()`
`NavigationService::getPages()` runs `ContentPage::where('enabled', '=', true)->whereNot('index', '=', true)->get()` — a live Eloquent query — and `routes/web.php` calls it directly at top level (not inside a deferred closure), so every CMS page's route is registered from whatever was in the `content_pages` table at the moment routes were loaded. This means `php artisan route:cache` either can't run at all during a DB-less Docker image build, or bakes in a stale page list that silently goes out of date the next time an admin edits a content page's `enabled`/`index`/`path` fields, until someone remembers to clear the route cache. This directly blocks the zero-downtime, build-once-deploy-everywhere pattern needed for [04-deployment.md](./04-deployment.md).
**Fix**: replace per-page route registration with one catch-all route (`Route::get('/{path}', ...)->where('path', '.*')`) that resolves the matching `ContentPage` at request time instead of at boot/cache time — a self-contained, low-risk change independent of the payment system.
**STATUS — done**: the per-page loop is replaced with a single catch-all route registered last (`php -l` clean on both changed files), and `SiteController::page()` now preserves the original `enabled`/`index` filtering (previously only applied by the removed loop) before falling back to the same `/site-error` redirect the rest of the app already uses for a 404.

**STATUS follow-up (2026-11-06)**: the `/` index route still chose between `index` and `noIndex` with a live `getIndexPage()` query at route-load time, so it had the same `route:cache` problem. It is now one `/` route; `SiteController::index()` falls back to `noIndex` at request time. Verified: `php artisan route:cache` succeeds with the database unreachable, and `tests/Feature/ExampleTest` (now `RefreshDatabase`) passes against an empty MySQL database.

### KI-16 — No real money type; a developer's own TODO flags this
**File**: `app/Providers/AppServiceProvider.php::boot()`
```php
// @TODO: replace this with a proper phpmoney solution, as soon as you get to real payment providers.
\Blade::directive('money', function ($money) {
    return env('CURRENCY_SYMBOL') . "<?php echo number_format($money, 2); ?>";
});
```
Monetary values (`Transaction.amount`, `EventTicket.price`, `OrderItem.single_price`/`row_price`, etc.) are stored as `decimal(15,2)` columns but handled as plain PHP strings/floats everywhere in application code, with no `$casts` declaration anywhere (confirmed: no `protected $casts` on `Transaction`) and no dedicated money value object. The original developer already flagged this as needing real payment providers to force the fix — see KI-17 for a concrete bug this already causes.
**STATUS — infrastructure built, not yet wired in (by design)**: `App\ValueObjects\Money` (exact integer-cents arithmetic, immune to the float-rounding bug and the KI-17/KI-18 cross-representation mismatches by construction) and `App\Casts\MoneyCast` now exist, each with passing unit tests (`tests/Unit/ValueObjects/MoneyTest.php`, `tests/Unit/Casts/MoneyCastTest.php`) proving the exact KI-17 scenario (`"25,5"` and `"25.50"` compare equal) and the exact KI-18 scenario (int/float/string DB values all normalize identically). Deliberately **not yet applied** to any model's `$casts` or to the `@money` Blade directive — wiring it in changes `PayTransaction::handle`'s comparison behavior, which per this document's own continuity rule (§2.1/§2.2 in [02-payment-system.md](./02-payment-system.md)) must ship together with, not separately from, the KI-17 fix itself (Phase 5.1 in [05-roadmap.md](./05-roadmap.md)).
**Fix**: introduce a `Money`/`Cents`-style value object (or at minimum a `decimal:2` cast plus consistently working in integer cents) before or alongside building standard PayPal (Phase 3 in [05-roadmap.md](./05-roadmap.md)), and route the Blade `@money` directive through it.

### KI-17 — Strict `===` comparison of money values is type-fragile (CONFIRMED by a passing test, not just hypothesized)
**File**: `app/Actions/Payment/PayPalFriendsFamily/PayTransaction.php`
```php
private function formatTransactionAmount($transactionAmount)
{
    return str_replace(',', '.', $transactionAmount); // always returns a PHP string
}
...
if ($transactionAmount === $transaction->amount) { // strict comparison: type AND value must match
```
`formatTransactionAmount()` only swaps the decimal separator and always returns a string. `$transaction->amount` comes straight off the `decimal(15,2)` column with no `$casts` entry, so its PHP type depends entirely on what the active DB driver's PDO layer returns for a NUMERIC/DECIMAL column - which is **not guaranteed to be a string**.

**Confirmed empirically** (`tests/Unit/Actions/Payment/PayPalFriendsFamily/PayTransactionTest.php::test_strict_equality_money_comparison_is_type_fragile_pending_KI17_fix`, run against this repo's own code): under SQLite, a `decimal(15,2)` column storing a whole-number value (`25.00`) round-trips through Eloquent as a PHP **integer** (`25`), not a string - confirmed with a standalone probe script (`DB::table(...)->first()->amount` returns `gettype() === 'integer'`). So `"25.00" === 25` is false purely because the types differ, and a textbook, exact-amount payment is misclassified as an overpayment.

This specific type-mismatch manifestation is SQLite-specific (MySQL's PDO driver, this app's actual production database, normally preserves DECIMAL columns as PHP strings). The ORIGINAL hypothesis for this issue - that two numerically-equal strings with a different number of decimal places (e.g. `"25.5"` vs a DB value of `"25.50"`) would also fail `===` on MySQL - is still plausible and worth testing for, but is **unconfirmed** pending CI running this same test file against real MySQL (see [03-testing-strategy.md](./03-testing-strategy.md)). Either way, the root defect is the same: the comparison never normalizes both operands to a common type/scale before checking equality.
**Fix**: cast both sides to a normalized numeric representation (ideally integer cents via the KI-16 money type) before comparing, never compare raw decimal values with `===`. The test above already pins today's buggy behavior; flip its assertions once the fix lands (Phase 5.1 in [05-roadmap.md](./05-roadmap.md)).
**STATUS — FIXED (Phase 5.1)**: `App\ValueObjects\Money` is now wired into `Transaction`/`PartialTransaction` via `App\Casts\MoneyCast`, and `PayTransaction::handle` compares `Money` values by exact integer cents instead of raw `===`. This fixes both the confirmed SQLite type-mismatch and the originally-hypothesized MySQL decimal-formatting mismatch in one change, since the cast normalizes any driver's representation (string/int/float) identically. Verified: the exact-match test now asserts the correct `paid`/`paid` outcome, plus a new regression-guard test (`test_exact_match_still_works_when_csv_amount_has_fewer_decimal_places_ki17_regression`) proves a `"25,5"` payment still matches a stored `"25.50"` exactly.

### KI-18 — Dev/test environment gaps: migrations aren't SQLite-compatible, and this sandbox has no Docker/MySQL at all
**Files**: `database/migrations/2022_09_25_054041_change_orders.php` (and potentially others using `->change()` with a dropped foreign key)
Attempting to run this project's full migration history against SQLite fails outright: `SQLite doesn't support dropping foreign keys (you would need to re-create the table)` - a `BadMethodCallException` thrown directly by Laravel's schema builder, not a subtle behavioral difference. This means `RefreshDatabase`-based tests **cannot run against SQLite at all** for this codebase, not even as a fast local smoke check - full stop confirmation of [03-testing-strategy.md](./03-testing-strategy.md)'s existing "use real MySQL in CI" guidance, and sharper than originally stated (it's not just that SQLite might hide bugs - it can't run the schema at all). Separately, this specific dev sandbox has no Docker and no MySQL/MariaDB server installed, so local verification of DB-backed tests (including this review's own characterization tests) currently has no path to running against the real schema/engine at all - only a hand-built, schema-minimal SQLite harness for isolated unit-level logic checks (as used in the KI-02/KI-17 tests above), which is NOT a substitute for the real CI run.
**Fix**: no code fix needed on the SQLite-incompatibility point (the project is correctly MySQL-only, and the testing strategy already accounts for that) - the actionable item is making sure CI ([05-roadmap.md](./05-roadmap.md) Phase 1.7 / [04-deployment.md](./04-deployment.md)) actually stands up a real MySQL service container, and that local dev onboarding docs are clear that Docker Desktop (with WSL integration enabled, for Windows/WSL developers) is required to run this app or its full test suite at all.

## Deployment / infra

### KI-09 — `deployment/mysql/init-db.sql` is empty; the real init script is unwired
**Files**: `deployment/mysql/init-db.sql` (empty), `deployment/create-database.sh` (has the actual `CREATE DATABASE`/`CREATE USER` logic), `docker-compose.yml.prod` (mounts `./deployment/mysql:/docker-entrypoint-initdb.d`)
MySQL's official image only auto-runs `.sh`/`.sql`/`.sql.gz` files it finds directly inside `/docker-entrypoint-initdb.d`. `create-database.sh` lives in `deployment/`, not `deployment/mysql/`, so it is never picked up on first boot — the mounted directory only contains the empty `.sql` file. A fresh deployment's database/user provisioning step is effectively a no-op today.
**Fix**: move (or symlink) `create-database.sh` into `deployment/mysql/`, or fold its logic into `init-db.sql` directly, and verify a from-scratch `docker-compose up` actually provisions the DB and user.
**STATUS — done, with one more bug found in the moved script**: `create-database.sh` has been moved to `deployment/mysql/create-database.sh` and made executable. While verifying this move, a second, pre-existing (not newly introduced) bug was found in the script's own SQL: `GRANT ALL PRIVILEGES ON \`$MYSQL_DATABASE%\`.* TO ...` puts the `%` wildcard **inside** the backtick-quoted identifier, concatenating it onto the database name (e.g. `` `rave%` ``) rather than using it as an actual SQL wildcard — this is not valid MySQL grant syntax for a wildcard-matched schema and should be either removed (grant on the exact database name only: `` `$MYSQL_DATABASE`.* ``) or correctly expressed. Not yet fixed — tracked here since it was only discovered as a side effect of the KI-09 file-placement fix.

### KI-10 — Nginx config is hardcoded to one domain
**File**: `deployment/nginx/rave.conf`
`server_name schleuse.eu;` and literal `ssl_certificate .../live/schleuse.eu/...` paths are hardcoded. This blocks spinning up a staging environment or moving domains without manually editing this file.
**Fix**: parameterize via an env-substituted template (e.g. `envsubst` over an `.conf.template` at container start) — see [04-deployment.md](./04-deployment.md).

### KI-11 — Three stale, unused `docker/<version>/` folders (one is still live)
**Dirs**: `docker/7.4`, `docker/8.0`, `docker/8.2` are unreferenced by anything (confirmed via grep across `docker-compose.yml`, `docker-compose.yml.prod`, and the root `Dockerfile`) — pure leftovers from an abandoned partial Laravel Sail-style multi-PHP-version setup. **`docker/8.1` is NOT dead** — it is the actual build context for the local dev `docker-compose.yml` (Sail-style): `context: ./docker/8.1`. The production `docker-compose.yml.prod` instead builds the root `Dockerfile` directly (`php:8.1-fpm`), so there are two independent, divergent PHP 8.1 build definitions (local Sail-style vs. prod) — confusing but not currently broken.
**Fix**: delete `docker/7.4`, `docker/8.0`, `docker/8.2` only. Leave `docker/8.1` in place (or reconcile it with the root `Dockerfile` as a separate, deliberate workstream — not a blind deletion).
**STATUS — done**: deleted and verified gone; `docker/8.1` correctly left in place.

### KI-12 — No CI pipeline of any kind
There is no `.github/` directory, no other CI config file, and nothing runs tests, linting, or a build check on push/PR today. Every regression currently depends entirely on manual testing before a manual deploy.
**Fix**: see [04-deployment.md](./04-deployment.md).

### KI-13 — `minimum-stability: dev` in `composer.json`, PHP `^8.0.2` is EOL
Both increase the risk of pulling in an unstable dependency version or running on an unsupported PHP runtime with no further security patches.
**Fix**: part of the upgrade path in [05-roadmap.md](./05-roadmap.md); set `minimum-stability` to `stable` as part of the same pass that bumps the PHP/Laravel versions.
**STATUS — done (the `minimum-stability` half)**: changed to `stable` and audited — no package constraint in `composer.json` targets a pre-release version, so this was low-risk. The PHP/Laravel version-EOL half is still open, tracked in [05-roadmap.md](./05-roadmap.md) Phase 4.

### KI-14 — No frontend build pipeline is actually wired in; a dozen+ libraries load unpinned from public CDNs
**File**: `resources/views/layouts/app.blade.php` (lines ~13-25)
Neither `webpack.mix.js`/`laravel-mix` nor `vite.config.js` is referenced anywhere under `resources/views` (confirmed: no `@vite(...)`, no `mix(...)`, and no `mix-manifest.json` exists under `public/`). The layout instead hand-links static files already sitting in `public/css`/`public/js` via `asset(...)`, and loads Bootstrap 4.1.3, CoreUI 3.2, two different Font Awesome versions (4.7.0 and 5.2.0 simultaneously), DataTables 1.10.19, Select2 4.0.5, bootstrap-datetimepicker, and Dropzone directly from `stackpath.bootstrapcdn.com`, `unpkg.com`, and `cdnjs.cloudflare.com`, with no Subresource Integrity (SRI) hashes and no self-hosted fallback. A CDN outage takes down page styling/behavior sitewide; a compromised CDN asset would execute unmodified in every visitor's browser; the duplicated Font Awesome versions are pure waste. Both `webpack.mix.js` and `vite.config.js` are dead config with no effect today.
**Fix**: pick one real bundler (see [05-roadmap.md](./05-roadmap.md) "modernize frontend asset pipeline"), vendor the CDN libraries into it (or at minimum add SRI hashes and self-hosted fallbacks as an interim step), and delete whichever of `webpack.mix.js`/`vite.config.js` is not adopted.
**STATUS — mostly done**: SRI hashes added and independently re-verified byte-for-byte correct (recomputed sha384/sha512 against the live CDN files) for Bootstrap 4.1.3, Font Awesome 4.7.0, Select2 4.0.5, bootstrap-datetimepicker 4.17.47, and Dropzone 5.5.1; the duplicate Font Awesome 4.7.0 `<link>` removed; both unused `webpack.mix.js`/`vite.config.js` deleted. Still open: CoreUI 3.2 (hash lookup timed out) and DataTables 1.10.19 (not attempted) have no SRI yet; Font Awesome 5.2.0 was kept alongside 4.7.0 because both are genuinely still used (23 call sites use v5 classes, 3 use v4) — de-duplicating requires migrating those 3 icons first.

## Found by the Phase 3 tests (2026-10-17)

### KI-19 — A 6-day-old order is reminded and cancelled in the same run
**File**: `app/Console/Commands/PaymentReminderCheck.php`
The reminder window (`created_at >= now - 6 days`) and the cancel window (`created_at <= now - 6 days`) are both inclusive, so an order created exactly six days ago to the second receives a payment reminder **and** a cancellation mail in one run. Pinned by `tests/Unit/Console/Commands/PaymentReminderCheckTest.php::test_order_exactly_six_days_old_is_both_reminded_and_cancelled_current_behaviour`. Only reachable if the scheduler ticks on that exact second, so low impact. **Fix**: make one of the two comparisons strict, then flip that test.

### KI-20 — Soft-deleted, never-cancelled orders keep their ticket codes reserved forever
**File**: `app/Actions/EventTicketCode/CleanUpOrphans.php`
The order lookup uses `withTrashed()`, so a soft-deleted order whose status is not `cancelled` still counts as present and its ticket codes are never released. Pinned by `tests/Unit/Actions/EventTicketCode/CleanUpOrphansTest.php::test_code_of_soft_deleted_non_cancelled_order_is_untouched_current_behaviour`. Needs a product decision (should deleting an order free its tickets?) before changing.

### KI-21 — Two different restocking rules
`CancelOrderAction` restocks by the order item's `qty`, while `CleanUpOrphans` adds 1 per detached ticket code. They agree only while an item's quantity equals its number of codes. Found by reading the code while writing the 3.4 tests; not covered by a test yet.
Update 2026-10-17: attaching codes creates exactly `qty` codes per item, so the two rules agree in practice; no change made.

### KI-22 — Ticket `stock` is never decremented by an order, but is incremented by cancellations (verified by reading the code)
**Files**: `app/Services/OrderService.php:42`, `app/Actions/Order/CancelOrderAction.php`, `app/Actions/EventTicketCode/CleanUpOrphans.php`
`makeFromCart` does `$eventTicket->stock - $cartItem->stock`, but `CartItem` has no `stock` attribute, so it subtracts null and the stock never changes. Cancelling an order then does `stock += qty` (and `CleanUpOrphans` does `stock++`), so `stock` only ever drifts upward. Availability itself is decided by sold ticket codes against `cap` (`EventTicket::isAvailable`), so today `stock` only matters for the `stock === 0` check in `OrderService`. Pinned by `tests/Feature/Cart/CartOrderLifecycleTest.php::test_ordering_does_not_decrement_the_ticket_stock_current_behaviour`. Needs a product decision first: is `stock` or `cap` the source of truth? (Also see KI-21.)
**STATUS — FIXED (2026-10-17), owner chose `stock` as the source of truth**: a ticket is available only inside its sale window with `stock > 0` and unissued ticket codes left (codes stay a physical limit); adding to the cart needs `stock >= qty`; `OrderService` decrements `stock` by `qty` per item under a row lock inside one DB transaction (a sold-out item or missing code rolls the whole order back); cancellation restocks `qty`. **Deploy step**: existing production `stock` values have drifted upward, so after deploying run `php artisan tickets:reconcile-stock` (dry run, prints a table) and then `--apply`. It suggests `cap` minus codes already handed out; if you deliberately release fewer tickets than `cap`, adjust those in the admin afterwards.

### KI-23 — Any cart item id can be ordered from any session (found by reading, no dedicated test)
**Files**: `app/Http/Requests/Site/OrderCartRequest.php`, `app/Services/OrderService.php`
`cart_item` is only validated with `exists:cart_items,id` and loaded with `CartItem::find`; nothing checks that it belongs to the visitor's own session cart (`CartService::remove` does check this for removal). Someone who guesses an id can create an order from another visitor's cart item. **Fix**: resolve the item through `CartService::getCart()->cartItems()`.
**Update 2026-10-17**: the `cart_item` field no longer exists. Orders are built from the visitor's own session cart, so another visitor's item cannot be included at all (`test_cart_items_of_another_visitors_cart_are_never_part_of_my_order`).
**STATUS — FIXED (2026-10-17)**: `OrderCartRequest` now only accepts a `cart_item` that belongs to the visitor's own session cart (`App\Contracts\Cart::content()->cartItems()`). Regression test: `CartOrderLifecycleTest::test_a_cart_item_from_another_visitors_cart_cannot_be_ordered`.

### KI-24 — Adding a second, different ticket to a non-empty cart crashes (verified by reading, not run)
**File**: `app/Services/CartService.php::add`
When the cart already has items, the lookup is by the same `event_ticket_id`; for a different ticket it returns null and the next line assigns `qty` on null, which is a PHP Error (HTTP 500). The checkout seems designed for one ticket type per order (`cart_item` is a single id), but it should refuse cleanly instead of crashing.
**STATUS — FIXED (2026-10-17), refuse behaviour chosen**: the cart view posts only the last `cart_item` hidden input, so the checkout really orders a single item; adding a second row would have silently dropped it from the order. `CartService::add` now throws `DifferentTicketInCartException` and the controller redirects to `/cart` with an `error` flash (visible since roadmap 0.3). Re-adding the same ticket still updates its quantity. Alternatives if you prefer them: replace the item, or build real multi-item orders (needs the form and `OrderService` changed).
**Superseded (2026-10-17), owner chose real multi-item orders**: adding a different ticket now adds a second cart row; one order is created with one order item per row, price = exact sum, transaction amount = that sum; the cart is emptied once the order exists. The earlier refusal exception was removed. Tests: `test_adding_a_different_ticket_to_a_non_empty_cart_is_refused_with_an_error`, `test_adding_the_same_ticket_again_updates_its_quantity`.

### KI-25 — Smaller findings from the cart tests
- `CartController::orderCart` reads `env('MAIL_FROM_ADDRESS')` directly, which returns null once `php artisan config:cache` is used (the planned deploy). Use `config('mail.from.address')`.
  **STATUS — FIXED (2026-10-17)** and widened: the same problem existed for `CRON_ROUTE` (the scheduler trigger URL in `routes/web.php`), `PASSWORD_PROTECTION` (the site password gate, which would silently switch off), `SYSADMIN` (failure-mail recipient in `Kernel`) and `CURRENCY_SYMBOL` (`@money` directive). They now read from `config/rave.php` (same env names, same values when config is not cached); the shop notification uses `config('mail.from.address')`. `tests/Unit/NoEnvOutsideConfigTest.php` fails if `env()` is used anywhere in `app/`, `routes/` or `bootstrap/`, so `config:cache` is safe for the planned deploy.
- `Order` is created without an explicit `status` and relies on the database column default.
- `App\Actions\Cart\ClearCart` and `UpdateTicketInCart` read an undefined `$cart` (unused stubs).

### KI-26 — `EventTicket::delete()` checks the wrong column (found by reading, not fixed)
**File**: `app/Models/EventTicket.php::delete`
It looks for `OrderItem::where('cart_item_id', '=', $this->id)`, comparing a cart item id with the ticket id, so the "this ticket has been ordered" guard blocks or allows deletion essentially at random. It should compare `event_ticket_id`.
**STATUS — FIXED (2026-10-17)**: the guard now checks `event_ticket_id`. Tests: `EventsAndTicketsCrudTest::test_deleting_a_ticket_that_has_order_items_is_refused` and `test_an_order_item_of_another_ticket_does_not_block_deleting_a_ticket_even_if_its_cart_item_id_matches`. Mass destroy still bypasses the guard (KI-27).

## Found by the admin CRUD tests (3.6, 2026-10-17) — pinned by `_current_behaviour` tests, not fixed

### KI-27 — Ticket and ticket-code deletion guards
- Mass destroy of event tickets does not run the "already ordered" guard of `EventTicket::delete()`, so ordered tickets can be bulk-deleted.
- `EventTicketCode::delete()` has its guard inverted: unsold codes cannot be deleted, sold codes can.
- Creating a ticket code in the admin form fails (database error) because the form does not post `event_ticket_id`, which is NOT NULL.

### KI-28 — Time schedules use the wrong permissions and are hard-deleted
`StoreTimeScheduleRequest` / `UpdateTimeScheduleRequest` check `event_create` / `event_edit` instead of `time_schedule_create` / `time_schedule_edit`: holders of the time-schedule permissions get 403, holders of the event permissions can write schedules. Time schedules and shifts have a `deleted_at` column but their models do not use `SoftDeletes`, so deletes are permanent.

### KI-29 — Time schedule shifts are largely broken
`Shift::createRepeating()` only checks the seconds part of the duration, so a normal 20:00 to 22:00 shift throws (HTTP 500); repeated shifts advance by whole hours only and can overlap; `repeat` is not required (undefined index) and `repeat=0` creates nothing. Single shift destroy and show always return 500 (the nested route's `{time_schedule}` parameter lands in the wrong argument); mass destroy works.

### KI-30 — Content page validation gaps
`UpdateContentPageRequest` has the `unique:content_pages,path` rule commented out, so renaming a page to another page's path is a 500 from the database unique index. Store/update only check category and tag ids are integers (no `exists`), so unknown ids reach `sync()` (likely a 500 on MySQL foreign keys; not reproducible on the SQLite test schema).

### KI-31 — Privilege escalation by design
Anyone holding `user_edit` or `role_edit` can give any role or permission, including to themselves; there is no further check. (Users without `user_edit` cannot promote themselves: covered by tests.) Needs a decision on how strict the admin model should be.
