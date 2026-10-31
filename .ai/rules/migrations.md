---
paths:
  - 'database/migrations/**'
---

# Migrations

## Forward-only: `down()` is empty and its docblock says why

Never write rollback logic in `down()`.
Schema changes are forward-only; a production rollback is a manual, coordinated
operation, never `php artisan migrate:rollback`. Every new migration carries an
empty `down()` under this docblock, so a review tool does not flag it as an
oversight:

```php
/**
 * Down migrations are intentionally omitted.
 *
 * This project does not use rollback migrations. Data loss from dropping tables
 * in production is unacceptable, and the JETNET mirror tables hold tens of
 * thousands of synced rows that a re-sweep would take hours to rebuild.
 */
public function down(): void
{
}
```

To undo a migration, write a new forward migration that reverses it and record
the reason in its docblock. Check every lane's database first — a column another
lane's mirror depends on is not yours alone.

A migration is FROZEN once it merges to develop, even when its table looks
unshipped. Staging, every lane and possibly production have already run it, and
Laravel never re-runs a migration it has recorded, so an edit reaches fresh
databases only and the schemas silently part. Change it with a new migration;
only a branch's own unmerged migration may still be edited. The Slack create
migrations were edited in place after release `v2026.10.06-3` had shipped them.

**Status here:** the 37 migrations that predate this rule (2026-09-09) carry
the `make:migration` default — a real `down()` that drops what `up()` created.
They are not being retrofitted; the convention applies from the next migration
on. Do not read a real `down()` in an older file as licence to write one.

## Schema in migrations, data in console commands

A migration file MUST only call `Schema::create/table/rename/drop`. It MUST NOT
contain `DB::table(...)->update()`, `DB::table(...)->insert()`,
`Model::query()->update()`, chunked-write loops, or any other write that
modifies row contents. Data backfills, value remappings, identifier rewrites and
similar transforms live in `app/Console/Commands/` as Artisan commands:

- signature `app:{domain}:{verb}-{noun}` (`app:jetnet:derive-operators`,
  `app:jetnet:sync-aircraft-models`, `app:close-abandoned-runs`)
- `--commit` flag, dry-run by default; `--chunk={n}` when iterating a large table
- extend `App\Console\Commands\Command` for `logInfo`/`logWarn`/`logError` and
  `startTime`/`getStats`, and set `$logChannel`
- idempotent — safe to re-run

`DeriveOperatorsAction` is the model: a pure function of the mirror, re-runnable
off the local snapshot with no API call ([`actions-jet-net.md`](actions-jet-net.md)).

Why the split: a console command is audited (a `console_command_runs` row, plus
who ran it and with what), previewable, re-runnable and tested directly via
`$this->artisan(...)`. A migration is one-shot and leaves only a row in the
`migrations` table — and against the JETNET mirror, a data change that goes
wrong in a migration is a ~485-page re-sweep where a command is a re-run.

### The one named exception: a rename that makes a stored value uncastable

A rename that removes an enum case while rows still hold its value leaves them
UNCASTABLE, not stale: the backed-enum cast reads through `from()`, which raises a
`ValueError`, so every screen touching such a row fails. A separate console
command would be a window in which the application is broken, so the schema half
and the value remap are ONE change in the same `up()`.

`2026_09_16_014412_rename_look_suppression_to_withdrawal.php` was the example (folded
into `create_last_looks` with the other pre-launch changes on 2026-09-30; it is in git
history):
`LastLookStatus` lost `suppressed` to `withdrawn`, so one `up()` renames
`suppressed_at` / `suppressed_by_user_id` to `withdrawn_at` /
`withdrawn_by_user_id` AND moves every `status` still reading `suppressed` with
`DB::table('last_looks')->where(...)->update(...)` — `DB::table` rather than the
model, so neither `LastLookScope` nor the soft-delete scope can hide a row that
still has to move. Its docblock names the rule it departs from.

That narrow and no wider. A backfill, a derived value, an identifier rewrite and
a remap of a value the code can still cast all stay a console command.

Acceptable in a migration: `Schema::create/table/rename/drop*`, column
defaults (`->default(...)`, next section), primary keys, foreign keys, indexes,
unique constraints — plus the one value remap the exception above allows.

## Column defaults are written in the migration
A column's default is
declared in the migration, where the schema is the single place that states it.
The rule this file carried until 2026-09-09 forbade that in favour of defaults in
the model's `$attributes`; nothing ever implemented the `$attributes` half (zero
models declare one), so it is retracted. Where a default's meaning is not obvious
from the column, say it in a short comment at the column.

Two consequences stand. A raw `DB::table()->insert()` in a test must supply
every NOT NULL column that has no default — most columns here still have none.
And `currency` is never a hardcoded `'USD'` default: a booking and its LastLook
take the broker profile's currency; a bid and its award take the LastLook's.

## A choice lives apart from the row it is about

A person's or a company's choice does not go on a row that a disconnect deletes
or a reconnect rewrites. Slack alert choices once sat in `alert_types` on the
connection, so reconnecting switched every alert back on. They are rows in
`notification_mutes` now, one per alert turned OFF on one channel. No row means
on, so an alert added later reaches everybody until they say otherwise: store
opt-outs, not opt-ins, wherever a new option should default on.

## Enum-valued columns are `string()` with a backed-enum cast, never `->enum()`
Cast to PHP enums on the model (9 casts here). No database `enum()` column
exists here, and none should be added.
A DB enum makes adding a case a migration against a live table and hides the
vocabulary from PHPStan; a string column with `casts()` keeps the enum as the
single source ([`naming-conventions.md`](naming-conventions.md) Rule 9 for case
naming).

## Time columns are plain timestamp holding UTC, never timestampTz
Use `$table->timestamp(...)`. Never `timestampTz`.

The app runs on `UTC` (config/app.php), so Laravel writes a datetime literal with no offset. Postgres interprets such a literal for a `timestamptz` column in whatever timezone the DB session happens to hold — it round-trips correctly through Eloquent while storing the wrong instant, and any other client sees the error. Plain `timestamp` stores exactly what is sent.

It is also what `timestamps()` and `softDeletes()` create, so a table is not half one type and half the other.

A column holding a LOCAL date or time is the exception and must carry a `_local` suffix (e.g. `trip_legs.departure_date_local`), so that a `whereDate('departs_at', ...)` sitting next to it reads as obviously wrong. Note a local date and its UTC instant routinely name different days: 22:00 EDT on 12 Sep is 2026-09-13 02:00Z.

No timezone column on a domain table. Zones are read from `airports.timezone` through the FK.

## Do not index a low-cardinality flag on a small table
No index on a boolean or a status whose values are overwhelmingly one value — `needs_review`, `needs_category_review` and similar. Postgres will seq-scan for the common value anyway, and the rare-value query is an admin queue on a table of hundreds to low thousands of rows where a scan is free. The index costs a write on every insert and update and buys nothing.

Same for a column indexed only to find its NULLs (`airports.timezone` was indexed to find airports with no zone). That is an admin gap-finding query, not a hot path.

If one of these tables ever grows and the queue query is genuinely slow, add a PARTIAL index (`WHERE needs_review`) rather than a full one — but measure first.

FK columns are different: Postgres does NOT auto-create an index for a foreign key the way MySQL does, so `constrained()` alone leaves the join and the ON DELETE check unindexed. Index FK columns you actually join or filter on.

## Park the JETNET mirror before migrate:fresh; dont_drop does not work
`migrate:fresh` drops the `jetnet_*` mirror with everything else, and re-syncing is ~485 pages against a rate-limited credential. The flow is:

    php artisan app:jetnet:snapshot
    php artisan migrate:fresh --seed
    php artisan app:jetnet:snapshot --restore
    php artisan db:seed

The final re-seed matters: the seeders ran against an empty mirror during `--seed`, so fleets were synthetic until the restore.

DO NOT reach for Postgres' `dont_drop` connection key. It does work as far as it goes — the tables survive — but `migrate:fresh` then truncates the migrations table and re-runs everything, so the surviving table is re-created by its own `Schema::create` and the run dies on "relation already exists". Measured, not assumed. Making the mirror migrations skip an existing table would fix it and costs more than it saves: they would stop being the authority on the mirror's shape.

The snapshot works because it copies into a schema outside the connection's `search_path`, and PostgresBuilder only drops what it finds there — so `public` is dropped and rebuilt from the migrations, which stay authoritative.

Skipping the snapshot is safe, just slower: OperatorTailSeeder detects an empty mirror and builds SYNTHETIC fleets so the platform still works end to end, and says so loudly.
