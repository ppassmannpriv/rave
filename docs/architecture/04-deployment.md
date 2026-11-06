# 04 — Deployment & CI/CD Target Design

_This document's recommendations were cross-validated against an independent architecture consultation (2026-10-16); the Kamal-based design below is its recommended path, adopted here in place of an earlier, more generic docker-compose-based draft because it gives a concrete, proven answer to "easy to deploy" at this project's actual scale, with named trade-offs rather than a menu of options._

## What's wrong with today's setup

- No CI at all (KI-12): every change ships on faith.
- Hand-maintained `docker-compose.yml.prod` builds its own image **on the server** rather than deploying a pre-tested artifact — a bad build is discovered in production.
- Nginx config hardcoded to one domain (KI-10) with a DB-init script that isn't actually wired up (KI-09).
- The scheduler depends on an external HTTP pinger with zero internal alerting (KI-03) — the top payment-availability risk in the whole system.
- CMS page routes are registered from a live DB query at boot (KI-15), which blocks baking `route:cache` into a deploy image — a prerequisite for the design below.
- No staging environment path: hardcoded config means a second environment means hand-editing files, not changing env vars.

"Easy to deploy" for a small, self-hosted, single-maintainer DIY-community project means: one `git push` produces a built, tested image; deploying it is one command; rolling back is one command; and a human finds out within minutes if something — especially the payment scheduler — stops working. It does not mean Kubernetes, multi-region HA, or a managed PaaS migration; that complexity would work against this project's actual scale.

## Target design: Kamal 2

**Recommended over**: a bigger PaaS (Coolify/Dokku — adds a stateful platform to operate, or fights the multi-service MinIO/Meilisearch/Redis stack) and over "docker-compose + hand-rolled blue/green scripts" (reinvents what Kamal already does, worse). Kamal is SSH-based, builds the image in CI, and deploys to the existing single VPS with `kamal-proxy` handling routing, automatic TLS, and connection-draining zero-downtime swaps from one YAML file.

### 1. One image, three roles
A single multi-stage `Dockerfile` (composer install, npm build, `config:cache`/`route:cache`/`view:cache` baked in — this is why KI-15 must be fixed first) produces one image. Kamal runs three roles from it, all restarted/replaced together on every deploy:
- `web` — the default command, behind `kamal-proxy`.
- `scheduler` — runs `php artisan schedule:work` (the framework's own foreground loop), replacing the KI-03 HTTP-pinger pattern. This is a real, visible, orchestrator-restarted process instead of a web request racing `max_execution_time`.
- `worker` — `php artisan queue:work --max-time=3600`, enabling real async queues (today `QUEUE_CONNECTION=sync`).

### 2. Scheduler hardening (closes KI-03 for good)
- The reconciliation task gets `withoutOverlapping()` and `onOneServer()`, backed by Redis (not the `file` cache driver, which doesn't support cross-process locks).
- Every money-touching scheduled task (reconciliation, order finishing, payment reminders) gets `pingOnSuccess()`/`pingOnFailure()` against a hosted dead-man's-switch (e.g. a free-tier Healthchecks.io-style check) — this is the one change that actually closes the "nobody notices when it silently stops" gap, because it alerts on the *absence* of a ping, covering every failure mode (dead container, stuck lock, dead Redis, dead host, cancelled pinger subscription) in one mechanism.
- Surface "last reconciliation run: time, rows, matched" on the admin CSV-upload screen — the person uploading the file is the person who most needs to see it was processed.

**Cutover sequence (zero gap, see [02-payment-system.md](./02-payment-system.md) for the full continuity rules)**: add an idempotency test (same CSV processed twice → identical state) → add the lock + Redis cache → add pings, confirm green against the *existing* pinger-driven runs → add the `scheduler` container alongside the old route (both trigger `schedule:run`; the lock serializes them, idempotency makes the second a no-op) → soak one full cycle → make the old pinger route a no-op 200 (keeps external monitoring quiet) → soak another cycle → delete the route. Rollback at every step is reverting the previous line.

### 3. CI pipeline (closes KI-12)
GitHub Actions. On every push/PR: `composer install`, `./vendor/bin/pint --test`, (once added) `larastan` at a lenient level as a cheap "does this still typecheck" signal, `php artisan test` against a **real MySQL 8 service container** (not SQLite — the reconciliation matcher's collation/decimal/`LIKE` behavior differs), and an image build to catch route/config-cache problems early. On merge to the deploy branch: push the built image to GitHub Container Registry, then `kamal deploy` in a job with a concurrency group (so two deploys can never interleave) behind GitHub Environment protection for production.

### 4. Zero-downtime deploy + migrations
- `GET /up` (Laravel 11+ ships this by default; add manually pre-11) as the health check `kamal-proxy` polls before swapping traffic — kept trivial (no DB query) so a DB blip can't block a deploy; a separate richer status page covers human debugging.
- Set a drain wait so in-flight requests finish before the old container is killed.
- Run `php artisan migrate --force --isolated` as a Kamal pre-deploy hook against the *new* image, **before** the proxy swap and never from the container entrypoint (a long migration in the entrypoint can exceed the health-check timeout and abort the deploy mid-migration; `--isolated` prevents a retried deploy from running migrations twice).
- **Expand/contract discipline, non-negotiable for `transactions`/`orders`/`payment_methods`**: a release only adds (nullable/defaulted) columns while old+new code both still work against the expanded schema; a later release removes the old column only once nothing reads it. This is what makes `kamal rollback` to the previous image always safe without a matching `migrate:rollback` — directly extending the continuity rule in [02-payment-system.md](./02-payment-system.md) §2.4.

### 5. Config parameterization (closes KI-09, KI-10)
- Kamal's `deploy.yml` is ERB-templated from env/secrets — `proxy.host`/`APP_URL` and all credentials come from GitHub Environment secrets via `.kamal/secrets`, with separate `deploy.staging.yml` / `deploy.production.yml` destinations. Nothing domain-specific stays hardcoded in the repo; `kamal-proxy` owns TLS (Let's Encrypt automatically), so the standalone `certbot` container and the hand-maintained `deployment/nginx/rave.conf` are retired.
- Fix the DB init wiring (move/fold `deployment/create-database.sh`'s logic into something MySQL's entrypoint convention actually picks up) as part of the same pass, and verify a from-scratch environment provisions cleanly.

### 6. Backups & observability
- `spatie/laravel-backup`, nightly, to the existing MinIO bucket plus one offsite copy — on the **same dead-man's-switch monitoring** as the payment scheduler, so a silently-failing backup is caught the same way a silently-failing reconciliation job is.
- Confirm Sentry (already a dependency, `SENTRY_LARAVEL_DSN` currently empty in `.env`) is actually wired and reporting.

## Accessories (MySQL, Redis, MinIO, Meilisearch)
Run as Kamal accessories with host volumes; they are not rebuilt on every app deploy and move independently on their own upgrade schedule (pin tags explicitly; Meilisearch specifically needs a dump/restore between its own version bumps — schedule this outside the Laravel upgrade ladder in [05-roadmap.md](./05-roadmap.md)).

## What this deliberately does not include

No Kubernetes, no multi-region/HA database, no managed PaaS migration — disproportionate to a small self-hosted DIY-community ticket shop, and would add exactly the kind of complexity this modernization is supposed to remove. The named trade-off of staying single-host: the zero-downtime swap mechanism is itself a single-host mechanic, so the mitigation is a tested backup plus a reproducible `kamal setup` that can rebuild the box from scratch quickly — not a second host.
