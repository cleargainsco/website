---
paths:
    - 'app/Domains/**'
---

# Domains

`app/Domains/{Audience}/` is where an audience's own code lives — `Broker/`,
`Operator/`, `Platform/`, and `Shared/` for what more than one audience reads or
what is not really specific to any one of them — a hybrid, judged case by case
(see "Audience first, but an audience's folder holds only what is specific to it"
below).
The audience is the context, and the query layer nests inside those folders.
Inside a domain:

| Subdirectory                   | Holds                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      |
| ------------------------------ | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| `Enums/`                       | vocabulary that belongs to that audience — `LastLookStatus`, `BidOutcome`; an enum both audiences share is `app/Enums/`                                                                                                                                                                                                                                                                                                                                                                                                                    |
| `Queries/`                     | EVERY read, whatever it is built on — a queue, a board, a lookup: `SyncDriftReviewsQuery`, `LastLooksBoardQuery` and its audience subclasses, and `Broker/Queries/BookingsBoardQuery`, whose filters and sorts are _declared_ on spatie/laravel-query-builder (the bookings board, #37, which brought the package with it — below). There is no `QueryBuilders/`: the package is how a read answers, not a kind of class, and two directories that both mean "reads" only drift                                                            |
| `QueryFilters/`, `QuerySorts/` | a filter or sort that needs more than a column, as a class — created when the first one arrives                                                                                                                                                                                                                                                                                                                                                                                                                                            |
| `Data/{Subject}/`              | that audience's own boundary types on laravel-data, ONE SUBDIRECTORY PER WIRE CONTRACT — `Broker/Data/BookingIntake/` holds `PayloadData`, `LegData` and `IncumbentData`. The namespace carries the scoping so the part types keep short names: the import line reads `Data\BookingIntake\LegData` without every class having to repeat the prefix. A VENDOR boundary type stays with its vendor under `app/Services/{Vendor}/Data/` ([`services.md`](services.md)); these describe what a BROKER sends us, which no vendor namespace owns |
| `Exceptions/`                  | a refusal only that audience can make — `Broker/Exceptions/BookingIntakeException` and the three beside it. An exception the whole app raises stays in `app/Exceptions/`                                                                                                                                                                                                                                                                                                                                                                   |
| `Pipelines/{Subject}/`         | THE ONE EXCEPTION to "writes never live here", and it is narrow — see below                                                                                                                                                                                                                                                                                                                                                                                                                                                                |

Writes never live here: every write is an `app/Actions/` class
([`code-architecture-conventions.md`](code-architecture-conventions.md)). Reads
live here and nowhere else — a top-level `app/QueryBuilders/` was tried on
2026-09-09 and reverted the same day: the owner wants things in the domains
unless it really does not make sense. spatie/laravel-query-builder was trialled
the same day and closed unmerged (#48) because no screen then needed more than a
typed `search` argument; the bookings board (#37) was the first that did, and
the package arrived with it, for that one class.

## The one write that lives in a domain: a `Pipelines/` stage, per ADR 0009

`app/Domains/Broker/Pipelines/BookingIntake/` holds `Illuminate\Pipeline` stages, and
`PersistBooking` — the last of them — opens the transaction and writes `bookings`, `trips`
and `trip_legs`. That is a deliberate exception to the sentence above, recorded in
[ADR 0009](../../docs/adr/0009-booking-intake-runs-through-a-pipeline.md), and the entry
point is still an Action: `IntakeBookingAction` validates, builds the context and sends it
through the stage list.

THE TRIGGER IS TWO INPUT SURFACES RE-COMPOSING THE SAME STEPS, not "a multi-step write".
A broker's push and an AE's manual entry digest the same trip into the same four tables and
need DIFFERENT steps — the manual path's typeahead has already resolved the airport and the
AE picked the operator from a list — so the stage list is the value the two paths differ by.
A multi-step write with ONE caller stays a composed action, which is what
`CreateOrganizationAction` and `ResolveAirportAction` are.

A second domain wanting one gets its own `Domains/{Audience}/Pipelines/`, never a shared
top-level home. `Illuminate\Pipeline` appears nowhere else in the codebase, which is the
reason the ADR exists: without it the next reader takes `Pipelines/` for somebody who did
not know the house pattern and folds it back into one action.

A PIPELINE'S OWN VALUES LIVE WITH ITS STAGES, not in `Data/`. `ResolvedLeg`, `ResolvedTail`
and `ResolvedAircraft` sit in `Pipelines/BookingIntake/` beside the only classes that use
them. They were in `Data/` and the cost was legible: `LegData` (what a broker sends) and
`ResolvedLeg` (what a stage made of it) sat two files apart, and neither name said which was
the wire shape. `Data/` is the CONTRACT; a value that never leaves the pipeline is not part
of one.

## Reads live in domain queries, writes stay in app/Actions

`app/Domains/<Domain>/Queries/` holds that audience's read logic: listings,
lookups, anything that answers a question without changing a row. Controllers
assemble neither — they pick a query, render its result, and delegate writes to
`app/Actions/`. A query class never saves, deletes, notifies, or opens a
transaction.

Where two or three domains would duplicate a query, put the shared mechanics in
an abstract base under `app/Domains/Shared/Queries/` and have each domain
subclass declare only what makes its answer different. Keep the per-domain
subclass even when it is one method today — it is the seam a domain-only rule
attaches to later without touching the others.

The exception is a query whose audience is a request parameter rather than a
screen. `UsersQuery` serves every audience's roster, and subclasses would have
had nothing to declare: the organization arrives as an argument, so there was
no seam, only three empty files. Reach for one class when the audience is
data; keep the subclasses when it is a different screen.

A query or enum here returns data, never the array a page reads — `{value, label}`
option lists included. The shape is a Resource the controller applies
([`resources.md`](resources.md)).

A paginated resource collection goes to Inertia as-is — never
`->response()->getData(true)` ([`resources.md`](resources.md)).

## Audience first, but an audience's folder holds only what is specific to it

A hybrid, decided case by case rather than by a mechanical test.

START FROM THE AUDIENCE. Code that one audience's screen needs goes in that
audience's folder by default, and most of the time that settles it:
`BookingsBoardQuery` is `Broker/`, `OperatorFleetQuery` is `Operator/`.

THEN ASK WHETHER ANYTHING IN IT IS ACTUALLY THAT AUDIENCE'S. A class that would
read and answer exactly the same for any other audience is not broker (or operator,
or platform) code, however many of that audience's screens call it, and it goes in
`Shared/`. Reads over reference data are the usual case: a table no organization
owns, such as `airports`, the JetNet mirror or `aircraft_models`.
The four new-booking pickers' queries (`AirportSearchQuery`, `TailSearchQuery`,
`OperatorSearchQuery`, `AircraftModelSearchQuery`) are called only by the broker's
form and live in `Shared/Queries/` for that reason. They sat in `Broker/Queries/`
until LL-62's review moved them, because filed by caller the folder claimed they
were broker-specific. The last three were one `BookingLookupQuery` then, named for
the form rather than what it searched; one query per picker now matches the one
controller and one resource each picker already had.

What makes a class the audience's:

- it takes an organization, or reads an organization-owned model.
  `OperatorFleetQuery` reads the JetNet mirror too, but through the operator's
  `tails()`, so its answer is that operator's;
- it carries a rule only that audience has, such as a status only a broker sees
  or a constraint only an operator is held to.

Adding either to a `Shared/` class moves it. The search queries' own notes
forbid a booking-derived field without the broker constraint, and adding one
makes the class `Broker/` code.

WHEN IT IS GENUINELY UNCLEAR, keep the audience placement and say why in the pull
request; a second audience's need usually settles it. Existing classes are
re-judged when a change touches them, not swept on this section's account.

PLACEMENT IS NOT ACCESS. `Shared/` grants nobody anything. Who may reach a read is
the route's audience middleware and the policy behind it: `search/*` still
answers 404 to a non-broker through `organization.type:broker`, wherever the query
lives.

## Typed text is matched through SearchBuilder

Match what somebody typed with `whereStartsWith()` / `whereContains()` (and the `or` forms) on
`App\Models\Builders\SearchBuilder`; the model opts in with `#[UseEloquentBuilder(SearchBuilder::class)]`. Never
build a LIKE pattern by hand — no `whereLike('x', "%{$term}%")`, no `whereRaw('lower(x) like ?')`, no `ilike`.
`%` and `_` are wildcards: a typed `%%` once matched every row on four screens. The builder escapes them and folds
case on every driver, but only Postgres honours that escape: on sqlite, the suite's default connection, an escaped `%`
matches nothing rather than everything — the safe direction, as `SearchBuilder`'s docblock records.

## A read over mirrored reference data asks the vendor first

Ask the vendor, and read our table only when the vendor cannot be reached. Never merge the two: every stored row is
a copy, so a merge duplicates results and crowds the cap (`AirportSearchQuery`). Carry a vendor record as an unsaved
model so one type reaches the Resource. A search catches the outage and reports it; a caller that must resolve a
record treats the outage as a refusal, never as "not found". A keystroke writes nothing — a row is stored when the
record is chosen and saved.

## When a screen needs declared filters and sorts, it gets a query builder on spatie/laravel-query-builder

Adopted with the bookings board (#37). A single search box is not the
trigger — a plain query with a typed parameter covers that
(`OperatorInvitationsQuery::candidates(string $search)` on LL-66). The trigger
is a screen whose filters and sorts are _declared_: several of them, driven by
the URL, that the package should validate. Each such screen gets one class in its
audience's own `Queries/` — `BookingsBoardQuery`, `final readonly`, `Request`
injected — built internally on `QueryBuilder::for($query, $this->request)` and
exposing a FINISHED ANSWER plus the accessors the screen echoes back:
`paginate()`, `sort()` and `appliedFilters()`, named as nouns rather than
`getSort()` ([`naming-conventions.md`](naming-conventions.md) Rule 2). There is no
`QueryBuilders/` directory and no `buildIndexQuery()` handing the controller a
builder to chain onto — see "Query classes return paginators" below. Page size is
the query's too, through `PerPagePreference`; the controller reads back what
actually ran off the paginator. The controller renders and assembles nothing:

```php
$page = $bookings->paginate();

'bookings' => $page->toResourceCollection(BookingBoardResource::class),
'filters' => $bookings->appliedFilters(),
'sort' => $bookings->sort(),
'perPage' => $page->perPage(),
```

- **Sorts and filters are declared here, never assembled in the controller.**
  `allowedSorts(...)` and `allowedFilters(...)` are **variadic** — spread
  them. An unknown `sort` or `filter[...]` in the request is a 400 from the
  package, which is the point: the screen's contract is the class, not whatever
  the URL happens to carry. A sort or filter that needs more than a column is a
  class in the domain's `QuerySorts/` or `QueryFilters/` implementing the
  package's `Sort` / `Filter`.
- **Tie-break every order.** `defaultSort('-created_at')`, then
  `$builder->orderByDesc('bookings.id')` as a **statement** after the sorts — a
  forwarded call is typed as the inner Eloquent builder, and the tie-breaker must
  follow a requested sort too. Two bookings pushed in the same second share a
  `created_at`; without it they swap places between pages. Eager loads go in the
  same statement, for the same typing reason, and the method declares
  `@return QueryBuilder<Booking>` — the package's builder is generic.
- **`toResourceCollection()` needs the concrete paginator.** It lives on
  `Illuminate\Pagination\LengthAwarePaginator`, not on the contract. `paginate()`
  forwarded through the package's builder is already typed as the concrete class,
  so the query's own `paginate()` declares that concrete class as its return type
  and the controller chains straight on its result.
- **Tenancy is the model's global scope**, never a `where('organization_id', ...)`
  added "to be safe" — that would be a second copy of the boundary, as good as
  the last person who remembered it ([`scopes.md`](scopes.md)).
  `BookingsBoardQuery` was written the other way and was corrected: it took the
  broker as an argument AND `Booking` carries `BookingScope`, and the board's own
  tests admitted what that cost — "these would pass with no scope at all". The
  defence was that `OrganizationScope` applies nothing for a null principal, so a
  job reusing the query would read every broker's board; it cannot, because the
  class takes a `Request`. The one principal the scope steps aside for on a real
  response is platform staff, and that is the route's job
  (`organization.type:broker`), not a query's.
- **A query whose organization is genuinely its argument** is the exception, and
  `UsersQuery` is the one that qualifies: `User` deliberately carries NO global
  scope (three mechanisms must read across organizations — see below), so its
  `where` is the only boundary there is rather than a second copy of one.

## Organization-owned models carry a global scope, not a where clause per query

When a model belongs to an organization (LastLook, Bid, and everything else FR1-FR13 adds), enforce the tenancy boundary with a global scope on the model, keyed on the signed-in user's organization_id. Do not scope it with a `where('organization_id', ...)` inside each domain query.

Why: a per-query filter is only as good as the last person who remembered it, and confidentiality here is the product (AC14.4 — an operator must never see another operator's bid, a count or a rank). A global scope fails closed for every call site at once, including the ones nobody has written yet. This is the query-layer half of NFR4; permissions answer what kind of action, never whose rows.

Users are the exception that proves it, and deliberately have no global scope. Three mechanisms must read the table across organizations: `EloquentUserProvider` builds `User::query()` for the login credential lookup, `ImpersonateManager` resolves its target through the same provider, and platform staff administer every organization's roster (AC2.4). A scope with three opt-outs on the app's highest-stakes paths fails open the moment one is forgotten.

`UsersQuery` carries that boundary instead: it takes the organization as an argument and is asked only after `UserPolicy::viewAny` has answered whether the actor may look. `UserPolicy` answers the same question the same way for writes, and the two must not drift: a list showing a row the policy refuses to edit is a screen full of buttons that 403.

Pair the scope with a feature test that a user of one organization gets a 404 — not a 403 — for another organization's record, so the response does not confirm it exists.

## Query classes return paginators, and spatie builders live in Queries/ too

There is no QueryBuilders/ directory. A read built on spatie/laravel-query-builder is still a read: BookingsBoardQuery sits in app/Domains/Broker/Queries/ beside UsersQuery. Two directories that both mean "reads" only drift.

A query returns a finished answer, not a builder for the controller to chain onto — `paginate()` and `withQueryString()` happen inside, matching UsersQuery, OrganizationsQuery and SyncDriftReviewsQuery. The controller picks a query and renders its result. Type the return as the CONCRETE Illuminate\Pagination\LengthAwarePaginator when the caller needs toResourceCollection() or getCollection(); neither is on the contract.

Page size is part of the query, not the controller. BookingsBoardQuery declares `REMEMBERS_PAGE_SIZE` and drives App\Domains\Shared\Queries\PerPagePreference itself. The "a query never saves" rule is about hidden writes — a declared, named one is fine. Read the applied size off the paginator (`->perPage()`) rather than resolving it a second time.

## Do not extract a board-query base until the second real one exists

BookingsBoardQuery is the only request-driven, spatie-filtered, paginated query in the app. The LastLooksBoard* classes are fixtures (no DB, no pagination, no Request) and stay tabbed-panel projections when FR6-FR9 make them real; UsersQuery/OrganizationsQuery/SyncDriftReviewsQuery take ARGUMENTS, not a Request, which is what lets them serve several callers. Do not pull a base class out of one example — see this file's UsersQuery note about subclasses that had nothing to declare.

The abstract-base rule above is about ONE THING READ BY TWO AUDIENCES (LastLooksBoardQuery), not about boards sharing pagination. Bookings have one audience by design: BookingScope has no operator half, so there will never be an OperatorBookingsBoardQuery.

TRIGGER: a second genuinely request-driven paginated board. Then extract perPage(), sort(), withQueryString() + the id tie-break — and above all derive appliedFilters() FROM allowedFilters(). Hand-written today, that echo can silently drift from the allowlist: add a filter, forget the echo, and the control reads as off while the filter is applied. That correctness win is the reason to reach for a base class; saving 40 lines is not.
