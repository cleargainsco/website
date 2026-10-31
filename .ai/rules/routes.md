---
paths:
  - 'routes/**'
  - routes/api.php
---

# Routes

## The organization is the container: rosters are nested under it
Every user listing in the application is one organization's — one route group (routes/organizations.php), one controller (App\Http\Controllers\UserController) and one page (resources/js/pages/users/index.tsx) for every audience. Two addresses reach it:

- `/users` (`UserController@own`) renders the signed-in user's own roster. No identifier in the URL at all, so the only roster most people can reach is not addressed by something they could edit. This is the address the nav points at.
- `/organizations/{organization}/users` is for reaching somebody else's, which only platform staff may do.

No organization type in any path. `/broker/users` was considered and rejected: a user's type is implied by who they are, so the segment says nothing `/users` does not, and it re-raises "which broker" for platform staff. The only type in a URL is the `?type=` filter on the organizations listing, which is what the Brokers and Operators nav links are.

Do not add a listing addressed by organization *type*. That is what this replaced, and the reason it had to go is concrete: many organizations answer to a type, so "create a user here" had no answer for which one they join, and creating had to be refused everywhere but the actor's own audience.

Organizations are addressed by ULID, never by primary key (`Organization::getRouteKeyName`). Record routes carry `->whereUlid('organization')` and the per-kind listings (`/organizations/brokers`, `/organizations/operators`) carry `->whereIn('type', OrganizationType::pluralsOfOnboarded())`, so the two shapes can never match the same path. Without the ULID constraint an unknown listing answers 405 rather than 404 — the address exists, only the method is wrong — which says more about the route table than it should.

Narrowing a listing is a path, not a query string: `/organizations/brokers` is a screen somebody bookmarks, not `/organizations` in a filter state. The constraint is generated from the enum, so a company type added later gets a URL without anybody widening a list here.

`/organizations/{organization}` is the company's own page and is where a row on a listing lands — people are one of the things an organization has, not the only thing, and FR2's record and vetting belong there beside them. Do not link a listing row straight to a roster.

The kind is never nested in a record's path. `/organizations/operators/{organization}/users` carries the same fact twice and the two halves can contradict each other, which is the defect the type-scoped listings had. Where a trail needs the kind — breadcrumbs, a highlighted nav entry — derive it from the organization. Bindings are scoped (`->scopeBindings()`), so `{user}` resolves through the organization's own users relation and a stranger's id is a 404 rather than a write under a borrowed URL.

Authorization is a policy answer wherever a policy can actually answer it — rosters and organizations are guarded by UserPolicy and OrganizationPolicy, not by middleware. UserPolicy::viewAny and ::create take the organization; OrganizationPolicy guards the companies list, which platform staff alone may open (organizations.manage).

A roster the actor may not see at all answers 404, not 403 (UserController::authorizeRoster). A 403 is uniform for an organization that does not exist and specific for one that does, which lets an operator enumerate the other operators by walking ids — AC14.4 makes that confidential. Refusals about the *action* stay 403.

The organizations listing holds onboarded companies only — `OrganizationType::onboarded()`, everything but Platform. LastLook has a row because every user needs an organization, not because anybody onboarded it; its staff are reached through /users.

Impersonation is `impersonate.start` / `impersonate.leave`, not nested under anything. Only LastLook staff may sign in as somebody else and doing so deliberately crosses organizations, so the policy guards it rather than the address. `impersonate.leave` carries `auth` alone on purpose — the session belongs to the impersonated user by then, and any further guard would strand them in the account they are leaving.

## No organization type appears in any path

There is one `/dashboard` for all three audiences; `DashboardController` renders the page `OrganizationType::dashboardComponent()` names. The `platform.`/`broker.`/`operator.` route groups are gone and are not coming back. (`EnsureOrganizationType` itself is back on a narrower job — see the section at the end of this file.)

Why: what a user may see is decided by who they are, and every role is already scoped to one organization type (`RoleName::organizationType()`, enforced by `User::assignRole()`), so holding `lastLooks.create` is itself proof of being at a broker. A panel prefix restated that in the URL and forced anything shared — users, settings, profile — to sit awkwardly outside it.

What was given up: the architecture suite used to assert categorically that every `broker.*` route carried `organization.type:broker`, which covered routes nobody had written. Its replacement asserts every route bound to an `App\Http\Controllers\*` action carries `auth`. That is broader but weaker on audience. Gate such a route on the permission that implies the audience, never on the path — and where `Gate::before` makes that insufficient, name the audience in middleware and add it to RoutesTest's audience list.

If a future feature area genuinely wants a panel prefix, the three route files are in git history (00b7472) — they were deleted in the same change that removed the prefixes, not lost.

## API routes go inside the middleware group; the ability stays on the route
routes/api.php declares one group carrying the two guarantees a whole surface shares — `auth:sanctum` (via ApiController::GUARD) and `api.organization:broker` — plus `prefix('v1')` and `name('api.v1.')`. Write new endpoints inside it. A route mounted outside is not merely inconsistent, it is unauthenticated.

NEVER hoist `abilities:` into the group. It is per-action by definition: a `GET /bookings` added under a group carrying `bookings.intake` would demand the intake permission to read, refusing the callers who should be allowed and letting an intake token read. Each route names its own.

A second audience means a sibling group, not an override inside this one — `api.organization` takes the type as a required argument so a route cannot decline to name one.

Enforced by tests/Architecture/: RoutesTest fails a route with no auth at all, ApiTest fails one that authenticates by token but names no ability (checked against App\Enums\Permission, not merely present) or no audience.

## Routes are declared per verb; `Route::resource` is not used
Write a `Route::get/post/patch/delete(...)` line per action — 26 of them here.
`Route::resource` is not used anywhere. Keep it that way: an explicit line per action is what makes the
ability-per-route rule in [`api.md`](api.md) and the one-resource-per-controller
rule in [`http.md`](http.md) checkable, and the architecture tests read the
route table rather than a resource declaration.

## Rate limits are inline `throttle:N,1` on the route
`->middleware('throttle:20,1')` on the route — three
tiers in use, 10, 20 and 30 a minute, plus 60 for the booking form's typeahead
searches (#35), where four pickers share one limiter per person and a debounce
still lets a fast typist issue several requests a second — and no named
`RateLimiter::for()` limiters. The only named limiters here are the ones Fortify registers itself
(`login`, `two-factor`); do not add more.

## Search endpoints are one family under `/search`

`GET /search/{subject}` → `{Subject}SearchController@index` + `{Subject}SearchQuery` + `{Subject}SearchResource`, all
taking the shared `SearchTermRequest`, which owns the two-character floor (the global `TrimStrings` middleware has
already trimmed the term — do not trim it again). Name each for what it returns, never for the screen that calls it,
and keep it out of that screen's prefix. One route group, the audience middleware on the group, one
`throttle:60,1,search` key.

## organization.type is back for the Gate::before gap only
EnsureOrganizationType and the `organization.type` alias were deleted with the panel prefixes (00b7472) and are restored. The old reasoning still stands: a role is scoped to one organization type, so holding a permission proves the audience, and no panel prefixes are coming back.

What it did not cover is `Gate::before`, which answers true for Super Admin and Platform Admin on EVERY ability. A screen gated on a broker-only permission still admits both, and no policy can refuse them. Name the audience on the route in that case, e.g. `->middleware('organization.type:broker')` on bookings.index.

It answers 404, not 403, matching UserController::authorizeRoster: audience first (the screen does not exist for you), then the policy (403, the screen exists and the answer is about the action). Middleware rather than a controller call, because a call has to be remembered per action and a forgotten one fails silently — tests/Architecture/RoutesTest.php asserts it categorically.

## Shared route constraints go on a GROUP, never repeated per route

An audience or a shared prefix is declared once on a `Route::group` and never copied onto each
route in it. `organization.type:broker` sat on ten individual routes before this; it is on the
broker group in `routes/broker.php` now, with `prefix('bookings/{booking}')` holding what four
routes used to spell out.

NO `whereUlid()` ON A ROUTE-KEY PARAMETER. Every model exposed in a URL is `HasUlids` with
`public_id` as its route key (models.md), and `HasUniqueStringIds` validates the shape before
querying — a malformed identifier is already a 404 with no database round trip. The constraint
only disambiguates ROUTE MATCHING, so add it when a literal sibling actually exists
(`bookings/export` beside `bookings/{booking}`), not by default.

THE DUPLICATION WAS NOT THE MAIN COST. A route added without the line is silently reachable
by platform staff — `Gate::before` answers true for them on every ability — and
`tests/Architecture/RoutesTest.php`'s audience list only catches a route somebody remembered
to add to that list. On the group there is nothing to forget.

Keep the test as well: it still catches a route MOVED OUT of the group, which the group
cannot. The two guard different mistakes.

This is not a panel prefix. `broker.`/`operator.`/`platform.` URL prefixes are gone and are
not coming back (above); grouping by audience MIDDLEWARE says who may reach a route without
putting the audience in the address, and a `prefix('bookings/{booking}')` group is about the
URL a nested resource already has.

## A URL registered with a vendor lives in that vendor's route file

An OAuth callback or a webhook address is entered in another company's console,
once per environment, so it must not move when a screen does. It goes in
`routes/{vendor}.php`, never under a settings prefix: Slack's are `slack/callback`
and `slack/company/callback` in `routes/slack.php`. Moving one is a deploy step,
because every app's redirect URLs change with it.
