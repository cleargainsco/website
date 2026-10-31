---
paths:
  - 'app/**'
---

# App

## Start every model query with ::query()
Write `User::query()->create([...])`, `Organization::query()->firstOrCreate([...])` — never the bare static form `User::create(...)`.

The bare form goes through `Model::__callStatic`, so static analysis and editors cannot resolve the method and report it as undefined. `::query()` returns a real `Builder`, and every builder method resolves from there.

Applies wherever a model is queried — actions, commands, seeders, tests — not only under app/Models.

Since 2026-09-09 (`chore/rector-laravel-sets`, pending merge) Rector enforces this — `LARAVEL_ELOQUENT_MAGIC_METHOD_TO_QUERY_BUILDER` in `rector.php` — so `composer test` rewrites or fails on the bare form. The rule stands; the tooling applies it.

## Reach for a collection chain before a raw foreach
Prefer `collect()` / Eloquent collections and chained methods — `map`, `filter`, `flatMap`, `reduce`, `groupBy`, `keyBy`, `pluck`, `sum` — over a raw `foreach`. Applies to new and changed code; the existing `foreach`es are not a sweep waiting to happen.

`flatMap` is the one people miss. It is the tool when ONE item produces ZERO OR MORE results — DetectSyncDriftAction's models each yield a rename and/or a category change. Before it, that was a generator, and `yield` was buying nothing there: the collections were already fully loaded and nothing consumed them lazily.

Two traps:

- `each()` is NOT the collection form of a mapping loop. It exists for side effects and throws its closure's return value away, so collecting through it needs a `use (&$acc)` accumulator — a `foreach` with extra ceremony. Use it only when the result really is discarded.
- You cannot `yield` inside a closure. The CLOSURE becomes the generator, not the method, so `each()`/`map()` and a generator are mutually exclusive.

A chain that wants an accumulator, or a loop incrementing counters as it goes, is the signal to map each item to a value and count afterwards. That is not style: DetectSyncDriftAction had two loops incrementing the same three counters, one of them forgot `eligibilityAffecting`, and the sweep reported 1 where the queue it had just filled showed 3. `->map(...)` then `->where('opened', true)->count()` has nowhere for that to hide.

Where a generator still earns its place: genuinely streaming data — `Model::cursor()`, a large file line by line — where holding the whole set would blow memory. Not a few hundred rows already in a collection.

Prefer a small named object over a positional tuple as the thing being mapped. A five-element array grew a sixth element in one branch of the code and not the other; `Collection<int, DriftFinding>` also sidesteps PHPStan's invariance complaints about array shapes with intersection types inside them.

## Build dates with the Date facade, never CarbonImmutable:: directly
Use `Illuminate\Support\Facades\Date` to construct a date — `Date::parse($v)`, `Date::now()` — never `CarbonImmutable::parse()` or `Carbon::parse()`. `config/essentials.php` sets `ImmutableDates::class => true` (nunomaduro/essentials), so `Date::` already returns `CarbonImmutable` at runtime; naming the concrete class hardcodes a decision the config owns, and a project that later flips it would have to be swept.

TYPE THE RESULT `Carbon\CarbonInterface`, not `CarbonImmutable`. The facade's signature says `Illuminate\Support\Carbon`, so PHPStan rejects a `CarbonImmutable` parameter fed from `Date::parse()`. `ResolvedLeg`, `BookingIntakeException::departureDayDisagrees()` and `ValidLegSequence` were widened to `CarbonInterface` for exactly this. Do not reach for `->toImmutable()` to keep the narrow type — that is ceremony for a guarantee the config already gives.

`@property CarbonImmutable $x` on a MODEL stays: that is what an Eloquent datetime cast actually returns, and it is an annotation rather than a construction. An assertion about the config itself (`toBeInstanceOf(CarbonImmutable::class)`) is also fine. New and changed code only — the older fixtures in tests/Feature/Actions and tests/Feature/Schema are not a sweep waiting to happen.

## Reach through the relationship, never a hand-written foreign-key where
Read and write an organization's own rows through its relationship — `$organization->tails()`, `$organization->contacts()`, `$organization->users()` — not `Model::query()->where('organization_id', $organization->getKey())`.

TWO REASONS, and the second is the load-bearing one:

ONE STATEMENT OF OWNERSHIP. The relationship says which rows belong to whom, in one place, and names the foreign key once. A hand-written `where` is a second copy that has to be right at every call site — and `operator_tails.organization_id` was called `operator_id` until recently, so those copies were also spelling a column name that lied.

IT IS A SECOND COPY OF THE TENANCY BOUNDARY. Organization-owned models carry a global scope (`OperatorTailScope`, `BidScope`). `Model::query()->where('organization_id', ...)` applies the scope AND the hand-written clause, so the clause adds nothing but the impression that it is what keeps tenants apart. `.ai/rules/domains.md` is explicit: tenancy is the model's global scope, never a `where` added to be safe — it is only as good as the last person who remembered it.

WHEN A RELATIONSHIP DOES NOT EXIST YET, add it rather than reaching around it. `Organization::tails()` was missing while `OperatorTail::operator()` existed, which is why `UpdateOperatorFleetAction` and `OperatorFleetQuery` hand-rolled the key.

ALSO: `whereIn` on an empty array compiles to `0 = 1`, so `if ($ids !== [])` guards around a bulk update protect against a query that was already harmless. Drop them.

The exception is a query whose organization is genuinely its argument and whose model carries NO scope — `UsersQuery` is the one that qualifies, and `domains.md` explains why `User` deliberately has none.

Reading past a global scope names it, `withoutGlobalScope(LastLookScope::class)`, never a bare `withoutGlobalScopes()`, which also drops `SoftDeletingScope`.
