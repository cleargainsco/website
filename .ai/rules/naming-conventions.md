# Naming Conventions (REQUIRED)

This rule owns casing, verbs and anti-patterns; class suffixes and layering belong to `code-architecture-conventions.md`, `domains.md` and `services.md`.

## TL;DR

| Element | Convention | Example |
| --- | --- | --- |
| Class | PascalCase, singular noun | `User`, `BookingController`, `CreateOrganizationAction` |
| Method | camelCase, verb-first, specific | `isContractPending()`, `refreshForUpdate()`, not `process()` |
| Property / variable | camelCase, descriptive, singular vs plural | `$activeUser`, `$activeUsers` |
| Constant | `SCREAMING_SNAKE_CASE` | `CHUNK`, `SIGNATURE_MINUTES` |
| Trait | `*able`, `Has*`, `Is*`, or `{Verb}s{Noun}` | `Notifiable`, `HasEnumLabel`, `RetriesTransientFailures` |
| Interface | `{Noun}Interface` for a vendor client contract; bare noun-phrase for a role | `JetNetClientInterface`, `OrganizationPrincipal` |
| Enum | PascalCase, **singular** | `OrganizationStatus`, `AwardContractStatus` |
| Enum cases | PascalCase | `OrganizationType::Broker`, not `BROKER` |
| DB table | snake_case, **plural** | `last_looks`, `operator_tails` |
| DB column | snake_case | `submitted_at`, `organization_id` |
| Foreign key column | `{singular_model}_id` | `user_id`, `organization_id` |
| Pivot table | snake_case, models in **alphabetical** order, singular | none yet — `operator_tails` and `last_look_invitations` are models, not pivots |
| Migration file | `YYYY_MM_DD_HHMMSS_action_table_name.php` | `2026_09_01_000700_create_aircraft_models_table.php` |
| Factory | `{Model}Factory` | `BookingFactory` |
| Resource | `{Model}Resource` / `{Model}Collection` | `BookingBoardResource`, `BookingFormResource` |
| FormRequest | `{Verb}{Model}Request` | `StoreBookingRequest`, `UpdateBookingRequest` |
| Exception | `{WhatFailed}Exception`, always suffixed | `BookingIntakeException`, `LastLookCannotBeSentException` |
| DTO | `{Noun}Data` for a laravel-data object; `{Noun}` for a plain value object under `app/Support/` | `AirportData`, `JetNetData`, `TailData`, `DriftSubject` |
| Test file | `{ClassUnderTest}Test.php` | `BookingsBoardTest.php` |
| Test block (Pest) | `it('does X when Y')` — sentence form | `it('suggests another operator the name describes')` |
| Blade view | snake_case | rare — Inertia/React project |
| React component file | PascalCase (component) or kebab-case (page) — match folder | `acceptable-use.tsx` |

## Rule 1: The `handle()` method

Use `handle()` **only** for a framework contract (`Job`, `Listener`, `Middleware`, `Command`) or a single-purpose Action: every class under `app/Actions/`, at any depth, has one `handle()` and a class name carrying the verb (`CreateOrganizationAction`).

**Do NOT add `handle()` to any other class.** Use a specific verb; generic ones (`process`, `execute`, `perform`, `run`, `doStuff`, `manage`, `apply`) on a multi-method class tell the reader nothing: `AviowikiClient::searchAirports()` ✅, never `process()` ❌. A `*Query` or aggregator method is named for what it returns (`forUser()`), or `fetch()` / `compute()` for one stateful entry point.

## Rule 2: Methods carry verbs; classes carry nouns (or noun + suffix)

`BookingController::update()` ✅, never a noun such as `booking()` ❌.

- No `getName()` / `setName()` on a model: `$model->name` is both.
- On a non-behavioral class a read-only accessor is the noun (`openCount()`, not `getOpenCount()`); `get` means a genuine fetch (HTTP, DB, cache).
- No negation in method names: `isPublished()`, not `isNotDraft()`; compose at the call site.

## Rule 3: Classes are singular nouns

`UserController`, never `UsersController`: the URL pluralizes, the class does not — models, controllers, Actions, FormRequests and Resources alike. Every job is `{Verb}{Noun}Job` (`SyncTailsJob`). A plural is allowed only on an explicit `Collection` suffix, almost always a Resource Collection.

## Rule 4: Trait names describe the behavior added

Whichever reads naturally inline: `*able` when the class becomes capable of an action (`Notifiable`), `Has*` when it gains a relationship, attribute or feature, `Is*` when it gains a state predicate or flag, `{Verb}s{Noun}` when it gains something it does, as Laravel names its own (`InteractsWithQueue`, `SerializesModels`). The last is the commonest form here: `RetriesTransientFailures`, `MarksWhileUnchanged`, `LinksToIndexOrShow`. `HasEnumLabel` ✅, never the bare noun `EnumLabel` ❌. Project-wide traits live in `app/Concerns/`, model-specific ones in `app/Models/{Model}/Concerns/`.

## Rule 5: Interface names — suffix for a client, noun-phrase for a role

`{Vendor}ClientInterface` when it exists so a test can swap the implementation (`JetNetClientInterface`, bound by its deferred provider). A bare noun-phrase in `app/Contracts/` for a ROLE unrelated classes play, read as a predicate about the implementor (`OrganizationPrincipal`, implemented by `User` and `Organization`). Never a third shape.

Use one: when a parameter, property or return type accepts two unrelated models, declare the role they share in `app/Contracts/` and type against it. A model union such as `User|Organization`, or an `instanceof` branch on which model arrived, means the contract is missing. Put the methods the caller needs on the contract for that role: `OrganizationPrincipal` is tenancy, so a notification recipient is a contract of its own.

## Rule 5b: Exceptions carry the `Exception` suffix

`{WhatFailed}Exception`, in `app/Exceptions/` or, for a refusal only one audience can make, `app/Domains/{Audience}/Exceptions/`. A bare `DuplicateBooking` in a `use` list or a `catch` reads as a model or an enum. The four legacy names (`OrganizationTypeImmutable`, `RoleOrganizationTypeMismatch`, `SuperAdminProtected`, `AviowikiUnavailable`) are not a sweep waiting to happen: rename one when the work is already in that file.

## Rule 6: Variables — singular vs plural carries meaning

`$users = User::all()` ✅; `$user = User::all()` ❌ looks single and is a Collection. Name a mixed collection for its type or role: `$activeUsers`, `$bookingIds`.

## Rule 7: Database — snake_case, plural tables, alphabetical pivots

Beyond the TL;DR: a pivot model is PascalCase, singular, both model names; a boolean column is `is_*`, `has_*` or a `*_at` timestamp that IS the boolean (`revoked_at`, never `is_revoked_at`); timestamps are `*_at`, in UTC; a migration names its change in past-tense imperative (`create_X_table`, `add_Y_to_X_table`, `change_X_to_Y`, `drop_X_from_Y`).

One concept is one column name in every table that records it: a Slack destination that refused a send is `refused_at` on both `slack_connections` and `organization_slack_channels`, never `stale_at` on one and `unavailable_at` on the other.

## Rule 8: Tests

Tests live under `tests/Unit/` or `tests/Feature/`, mirroring the source path. Pest blocks are `it()` or `test()` — both in use, `test()` the majority — with a sentence description, never a `test_` prefix; group related blocks under `describe('Subject', ...)`, especially past a handful ([`testing.md`](testing.md)). Factories (`Booking::factory()`) and fakes (`Queue::fake()`), not hand-rolled fixtures.

## Rule 9: Acronyms and abbreviations

Acronyms are words: `HttpClient`, `JetNetTail`, `$httpClient`; never `HTTPClient` or `JETNETClient`. Allowed lowercase abbreviations: `id`, `url`, `uri`, `api`, `db`, `dto`, `dt`; anything else is the full word (`database`, not a `db_*` column beyond a connection identifier). So no class ends in `DTO`: a boundary type is `{Noun}Data` ([`services.md`](services.md)); "DTO" is fine in prose.

## Rule 10: No filler, no negation flips, no Hungarian

```php
$theUser = User::find($id);      // ❌ filler
string $strName = '';            // ❌ Hungarian: the type hint carries the type
if (! $user->isNotActive()) {}   // ❌ a double negative
$user = User::find($id);         // ✅
string $name = '';               // ✅
if ($user->isActive()) {}        // ✅
```

## Enforcement (review agents)

Naming-rule violations are **`severity: high`** findings — same tier as `trust-the-layers.md` violations.

- Cite the rule by anchor: `naming-conventions.md#rule-1-the-handle-method`, `#rule-3-classes-are-singular-nouns`, etc.
- A violation flagged by an agent MUST NOT be marked resolved unless the diff actually renames the offending element OR adds an inline comment justifying the exception (rare — reserved for framework-imposed names).

For human review: cite by anchor in PR comments. Naming changes that ripple across a public API (used by other modules / DBs) warrant a small dedicated PR; naming changes scoped to a single file should travel with the work that exposes them.

## See also

[`laravel-conventions.md`](laravel-conventions.md) · [Webdevetc](https://webdevetc.com/blog/laravel-naming-conventions/), the general-Laravel baseline this rule overrides.
