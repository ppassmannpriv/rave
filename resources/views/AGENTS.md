# resources/views

105 Blade files; `admin/` holds 63 (score: file count, subdir count). Layout and i18n conventions differ from stock Laravel.

## STRUCTURE
`admin/<camelResource>/{index,create,edit,show}` (+`partials/`, `relationships/`), `layouts/{admin,app,checkout}`, `mail/` (8), `partials/` (checkout, site, common), `auth/`, `cart/`, `events/`, `site/`, `user/`, `password-protection/`, `errors/`.

## WHERE TO LOOK
| Task | Location |
|------|----------|
| Admin shell/menu | `layouts/admin.blade.php`, `partials/menu.blade.php` |
| Checkout success pieces | `partials/checkout/success/*` |
| Order emails | `mail/*` (shared `mail/partials/{css,head,footer}`) |
| Maintenance page | `errors/503.blade.php` |

## CONVENTIONS
- Admin layout loads Bootstrap 4.1.3, CoreUI 3, DataTables, select2 from CDNs; only `public/css/custom.css` is local.
- Strings via `trans('cruds.<key>.fields.<field>')` with `_helper` companions; add keys to BOTH `resources/lang/de` and `en`.
- Money rendered with `@money` directive (`AppServiceProvider`).

## ANTI-PATTERNS
- `layouts/admin.blade.php` hardcodes the `schleuse.eu` og:image.
- No Vite/Mix asset pipeline in use for admin: do not assume `mix()` output exists.
