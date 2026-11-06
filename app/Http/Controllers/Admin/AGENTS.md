# app/Http/Controllers/Admin

16 hand-scaffolded back-office controllers (score: file count + shared pattern). The API twin lives in `../Api/V1/Admin`.

## WHERE TO LOOK
| Task | File |
|------|------|
| Dashboard | `HomeController` |
| CSV upload for F&F payments | `PaymentMethodController@uploadCsv` |
| Cron/task view | `TaskScheduleController` |
| Time schedules + shifts | `TimeScheduleController`, `TimeScheduleShiftController` |

## CONVENTIONS
- Methods: index, create, store, edit, update, show, destroy, massDestroy.
- Each method starts with `abort_if(Gate::denies('<snake>_<action>'), 403)`; actions are `access`, `create`, `edit`, `show`, `delete`.
- Routes: `Route::delete('<uri>/destroy')->name('<uri>.massDestroy')` BEFORE `Route::resource` in `routes/web.php`.
- Requests: `Store{Model}Request`, `Update{Model}Request`, `MassDestroy{Model}Request` in flat `app/Http/Requests`; `authorize()` repeats the Gate.
- Class names mix singular/plural (`EventTicketsController`, `RolesController`, `EventController`); route names are kebab (`admin.event-ticket-codes.index`).
- Views: `resources/views/admin/<camelCase>/`.

## ANTI-PATTERNS
- `$request->all()` passed to `create()`/`update()`: tighten fields when touching, do not widen `$fillable`.
- New resource needs permissions seeded/migrated (see `database/migrations/*add_permissions*`) or the Gate denies everyone.
