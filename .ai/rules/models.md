---
paths:
    - 'app/Models/**'
---

# Models

## Models are unguarded — never declare $fillable or #[Fillable]

Mass assignment protection is disabled globally (`Unguard => true` in config/essentials.php). Guarding happens at the request boundary instead, not on the model.

Never add `$fillable`, `$guarded`, or `#[Fillable]` to a model. A guard on one model while every other is unguarded is worse than none — it reads as a guarantee the app does not actually provide.

Because nothing is guarded, `Model::create()` / `fill()` will set whatever you hand them. Only ever pass `$request->validated()` from a FormRequest. Passing `$request->all()` into a model is a mass-assignment vulnerability here, not merely untidy.

Enforced by tests/Architecture/ModelsTest.php.

## Write with `fill()`; `forceFill()` forces past a guard that is not there
Mass assignment is disabled globally, so `forceFill()` — `static::unguarded(fn () => $this->fill(...))` — wraps an unguard inside an unguard. Measured: `Model::$unguarded` is `true` at runtime, and `fill()` sets an attribute without throwing even though `preventsSilentlyDiscardingAttributes()` is also true, because `isFillable()` short-circuits on `$unguarded` before the discard check is reached.

It is not merely redundant. `forceFill` READS as "there is protection here and I am deliberately going past it", which is the same false guarantee the section above forbids `$fillable` for. Seventy-five sites said that; none of them meant it.

Neither one excuses `$request->all()`. What keeps user input out of a column is the FormRequest — `validated()` returns only keys that have rules ([`http.md`](http.md)) — and that is unchanged by which of the two you call.

## An organization's type is immutable after creation
`Organization::$type` is set once, at creation. An `updating` guard on the model throws `OrganizationTypeImmutable` on any change.

The type is what every authorization decision about the organization's users derives from: which panel they reach (`EnsureOrganizationType`) and which roles they may hold (`RoleName::organizationType()`, enforced by `User::assignRole()`). That role guard runs at assignment and never again, so re-typing an organization re-checks nobody — a broker turned operator leaves its users holding `Broker Admin`, and so still carrying `lastLooks.award` and `bids.viewAny`, which AC14.4 forbids in an operator context.

Do not add a type field to an organization edit form. Converting an organization means creating the new one and moving its users across, so every role is re-assigned and re-checked on the way.

A query-builder `update()` bypasses model events and so bypasses this guard. Write through the model.

## Models exposed in URLs are addressed by `public_id`, not primary key
`Organization` carries a `public_id` column alongside its integer primary key: `use HasUlids` with `uniqueIds()` returning `['public_id']` (so the PK stays an auto-incrementing int that foreign keys join on) and `getRouteKeyName()` returning `'public_id'`.

The column is named for its job, not its format. It holds a ULID today; `public_id` means a later change of format is a change of value rather than a rename reaching every query, payload and generated route type. Every table that exposes a record externally uses this one name — the 8 schema-review tables (bookings, trips, bids, …) already did, and `users`/`organizations` were renamed off `ulid` to match.

Keep the integer primary key. All 55 foreign keys in this schema join on `bigint`; `public_id` carries a unique index and is read only by route-model binding and API output. Making it the PK would widen every FK column and every index over them — on Postgres `$table->ulid()` is `bpchar(26)`, roughly 3.4x a `bigint` and compared with `memcmp` rather than a machine word — while buying nothing, because the confidentiality goal is already met by never exposing the int.

Why: a sequential id in `/organizations/{organization}/users` tells a broker admin how many companies are on the platform and roughly where in the queue they were onboarded, and which companies exist is confidential (AC14.4). It also invites editing the URL. `HasUniqueStringIds` validates the shape before querying, so a malformed or numeric identifier is a 404 rather than a database error.

The primary key is not shipped to the browser either — `OrganizationResource` sends the ULID under the key `id`. A sequential id in a payload leaks exactly what one in a URL does.

Apply the same to any model that will appear in a URL for a broker or operator (LastLook, Bid). Note `HasUlids`, `HasUuids` (uuid7) and `HasVersion4Uuids` (Str::orderedUuid) all embed a creation timestamp; only `Str::uuid()` is fully random. If a model comes along whose creation time is itself confidential, override `newUniqueId()` rather than assuming the default hides it.

`User` carries one too, for the same reason.

## Group models by concern; avoid domain-word collisions when naming the group
Models are grouped into subdirectories by what they are about, and the factory namespace mirrors it exactly (`App\Models\System\ConsoleCommandRun` -> `Database\Factories\System\ConsoleCommandRunFactory`). No model declares `newFactory()`; the convention does the resolving, so moving a model means moving its factory in the same commit or the factory silently stops resolving.

`Models/JetNet/` mirrors an external source. `Models/System/` holds records the app keeps about its own machinery rather than about the business — `ConsoleCommandRun` (what ran, from a command OR a queued job) and `SyncWatermark` (how far a sync got). Core domain models (`User`, `Organization`, `Airport`) stay flat.

Do not name a group after a word the domain already uses. `Operations` was rejected because `RoleName::BrokerOperations` is a real role and "operator" is an organization type; `Platform` is taken by the platform-admin audience. `Console` was rejected too — it would be a lie, since jobs write `ConsoleCommandRun` rows and an Action writes `SyncWatermark`.

## Dates are CarbonImmutable; cast every non-string column including foreign keys
config/essentials.php sets `ImmutableDates => true`, so every date cast returns `Carbon\CarbonImmutable`. Docblocks must say `CarbonImmutable`, not `Illuminate\Support\Carbon` — the latter is a different, MUTABLE class that CarbonImmutable does not extend, so the docblock advertises `->addDay()` mutating in place when it does not.

Cast every column whose PHP type differs from what the driver returns: dates, enums, booleans, integers, decimals, JSON. That includes FOREIGN KEYS. `@property int $operator_id` is a claim, and whether a driver hands back 6 or "6" is a PDO configuration detail — SQLite and MySQL with native prepares return ints, emulated prepares return strings. Identity comparisons here are strict, so an uncast key is a `===` that answers differently on different connections.

Do NOT cast plain string columns. `'string'` is a no-op on a value that is already a string and only adds a line to maintain.

Money columns are the exception to "cast every non-string column" being the whole story: see "Money columns cast through MoneyCast" below for what the cast does and does not promise.

## Money columns cast through MoneyCast
This SUPERSEDES the former rule that money must never be cast to a Money object.

THE CAST IS KEYED ON AN ATTRIBUTE AND WRITES THE `_minor` COLUMN. `'incumbent_cost' => MoneyCast::of('currency')` persists into `incumbent_cost_minor`. Use `MoneyCast::fixed('EUR')` for a column that is always one currency — named separately rather than overloading `of()` on whether the argument looks like an ISO code. Each name then means exactly one thing:

```php
$booking->incumbent_cost = '24500.99';   // MAJOR units in
$booking->incumbent_cost;                // Money, USD 24500.99
$booking->incumbent_cost_minor;          // int 2450099 — the raw column, UNCAST
```

Because the column keeps no cast of its own, a factory or seeder writing `'incumbent_cost_minor' => 2_400_000` still writes minor units straight through. Keying the cast on the COLUMN is what forced one name to mean both, so `incumbent_cost_minor = '24500.99'` read as 24500.99 CENTS while storing 2450099.

EVERYTHING ASSIGNED TO THE ATTRIBUTE IS MAJOR UNITS — `24_500` and `'24500.99'` alike. The conversion runs through `Money::of()`, never a literal x100, because the scale belongs to the currency: JPY has no minor unit (1000 -> 1000), BHD has three (10.500 -> 10500).

Three things are refused, each with a message saying why. FLOATS, integral ones included: `Money::of(24500.99, 'USD')` returns 2450000 in Brick — it truncates behind a deprecation notice. SUB-CENT amounts, rather than rounding them, because a cast picking a rounding mode is how fractions of a cent leave a financial record. A MONEY whose currency is not the row's, which would otherwise be stored unconverted and turn $1.00 into EUR 1.00.

The cast never writes `currency`. `last_looks` has four money columns to one `currency`, so four casts writing it would race and the last fill would silently redenominate the other three. Currency comes from upstream — see .ai/rules/migrations.md.

DISPLAY FORMATTING IS NOT PERSISTENCE, and neither is input formatting. A form posting `"24,500"` is ambiguous — a comma is a thousands separator in en-US and a DECIMAL separator in de-DE, so stripping it in the cast turns EUR 24,50 into EUR 2,450. Normalize in the FormRequest's `prepareForValidation()`, which knows the endpoint's contract; the cast only knows the row's currency.

MASS-ASSIGNMENT GAP: because the column is uncast, `create(['incumbent_cost_minor' => 24_500])` writes 24500 raw ($245.00) rather than $24,500. Models are unguarded, so the FormRequest is what closes this — `validated()` only returns keys with rules. Declare `incumbent_cost`, never `incumbent_cost_minor`.

WHY THIS RATHER THAN A `/100` ATTRIBUTE, which is the obvious cheaper answer and was weighed: an `Attribute` with `get: $v / 100, set: round($v * 100)` is about eight lines and any Laravel developer reads it instantly, but it needs repeating for each of the six money columns, returns a bare float with no currency attached, and hardcodes a scale of two. Every table here carries a per-row `currency`, and other currencies are expected — so `x100` is a latent bug for JPY (no minor unit) and BHD (three). The cast is the heavier option ON PURPOSE. Do not swap it for the simpler one without that changing.

NEVER SEND A CAST MONEY ATTRIBUTE STRAIGHT TO A PAYLOAD. Money implements JsonSerializable and encodes as `{"amount":"12345.00","currency":"USD"}` — the decimal only, no integer. Use `App\Http\Resources\MoneyResource`.

NEVER PUT A MONEY ATTRIBUTE IN `$appends`, which is the specific way that happens by accident. It would inject that decimal-string shape into EVERY serialization of the model — `toArray()`, `toJson()`, event payloads, broadcast data — rather than at a boundary somebody chose. That the attribute is absent from `toArray()` today is a safety feature: what serializes is `incumbent_cost_minor`, the raw int. The Money should materialise only when asked for by name, and reach a payload only through the resource.

VALIDATE USER INPUT WITH `App\Rules\ValidMoney`, which refuses exactly what the cast refuses and refuses it first. The cast THROWS, which is right for a programming error and wrong for a payload — an uncaught InvalidArgumentException is a 500 where the caller deserves a 422 naming the field. The rule takes the currency explicitly, with no default, because it decides the permitted precision (two for USD, none for JPY, three for BHD) and because `currency` is never hardcoded here:

```php
'incumbent_cost' => ['required', new ValidMoney($this->broker->currency, min: 0)],
```

## Model events go in an Observer, registered with `#[ObservedBy]`
Lifecycle hooks live in `app/Observers/{Model}Observer.php` and the
model declares `#[ObservedBy(XObserver::class)]`. Nothing here has an observer
yet; the one `booted()` — `Organization`'s type-immutability guard above —
predates the rule. Write the next lifecycle hook as an observer.

This is about *lifecycle* hooks. Business logic that needs an input a hook
cannot see (an admin's email address) stays in the Action —
[`actions.md`](actions.md) records why the organization + admin pairing is not
an observer, and that still holds.

## Custom Eloquent casts live in `app/Casts/`
Reach for a cast whenever a column has one canonical stored form and more than
one writer — the cast's `set()` is where the normalisation happens
([`normalize-on-write.md`](normalize-on-write.md)). `MoneyObjectCast` is the first one here; the cast under
`app/Services/Aviowiki/Casts/` is a laravel-data cast, a different kind, and
stays with its vendor.

## Eager-load explicitly; strict mode is on
`ShouldBeStrict` (lazy loading throws) and `AutomaticallyEagerLoadRelationships`
are both on in `config/essentials.php`. Eager-load explicitly anyway — `->with()`
at the query, never a model-level `$with`: the automatic loader only covers a relation reached
from an already-loaded collection, and an explicit `->with()` documents what the
screen needs. Resources guard relations with `whenLoaded()` for the same reason
([`resources.md`](resources.md)).

## Money is a Brick `Money`, cast from minor units in the row's own currency
Adopted on 2026-09-09 (`feat/brick-money-cast`, pending
merge): every `*_minor` column is cast through `App\Casts\MoneyObjectCast`
with the row's `currency` column — `'net_price_minor' =>
MoneyObjectCast::class.':USD,currency'` — so a read is a `Brick\Money\Money`,
arithmetic and formatting never see a bare int, and two figures cannot be
compared without their currencies being compared first. Writes accept a `Money`
or an int of minor units, so factories, seeders and the intake keep writing
ints. Docblocks say `@property Money $net_price_minor`. A new money column is
an unsigned bigint of minor units beside a `char(3)` `currency` column, cast the
same way; never a decimal, never a float.

## Enum comparisons live behind a named model predicate, never inline
Never write `$model->status === SomeEnum::Case` at a call site — not in a controller, an action, a query, a policy, or another method on the same model. Add a public predicate to the model (`isSent()`, `isActive()`, `isApproved()`) and call that everywhere, including from other methods on the model itself.

Why: the comparison is a domain question ("is this claimable?"), and spelling it out inline copies that question to every call site. When a state splits or a case is renamed, an inline `===` is a grep across the app that misses the one in a Blade file; a predicate is one edit. It also lets a compound rule compose from named parts — `isClaimable()` is `isSent() && ! isExpired()`, which reads as the rule it encodes.

Applies to `!==` too, and to a status held on a related model: `Organization::mayBid()` delegates to `OperatorProfile::isApproved()` rather than reaching through and comparing the enum itself. Where the enum already answers the question (`OnboardingStatus::isAwaitingReview()`), the model method delegates to it instead of restating the comparison.

Existing examples: `Organization::isType()`, `Organization::isActive()`, `OperatorInvitation::isSent()`, `OperatorProfile::isApproved()`.

## Model $attributes defaults hold the enum case, not ->value
Write `protected $attributes = ['status' => SomeEnum::Case]`, not `SomeEnum::Case->value`.

Some models carry a comment claiming the case "would sit uncast in the attribute array and fail at insert". That is wrong, verified against Laravel 13.30 and the stored column. Property defaults do bypass `setAttribute`, so the instance really does sit in `$this->attributes` — but both sides handle it: `Query\Builder::cleanBindings()` maps every binding through `enum_value()` before PDO sees it, and `getEnumCastableAttributeValue()` returns an already-enum value untouched on the way back. The raw column ends up holding the backed value either way.

Prefer the case: it type-checks, it survives a rename, and it keeps the default reading like the enum the column is cast to. Do not copy the `->value` form or its comment from `Operator` or `BookingSubmission` — both predate this and are wrong on the same point.

## A model searched by typed text opts in to SearchBuilder

`#[UseEloquentBuilder(SearchBuilder::class)]` on the model; the matching rule is in [`domains.md`](domains.md).
