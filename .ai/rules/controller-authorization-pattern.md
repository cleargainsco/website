---
paths:
    - 'app/**/Controllers/**'
    - 'app/**/Requests/**'
---

# Controller Authorization Pattern

Authorization checks MUST be the first statement in controller methods, before any business logic.

## Rule

**Call `$this->authorize()` as the first line of every controller method that requires authorization.**

Where the method takes a FormRequest, the policy lives in its `authorize()` instead — one
layer per question ([`http.md`](http.md)). The exception is a check that must answer **404**:
`authorize()` raises a 403, so `UserController::authorizeRoster()` stays in the controller.

This ensures:
- Unauthorized requests are rejected immediately
- No resources are wasted on unauthorized operations
- Authorization is explicit and auditable

**Scope: web controllers.** API controllers under `app/Http/Controllers/Api/` do NOT call `authorize()` — their routes are guarded by middleware alone, and a controller-side check there is unreachable ([`api.md`](api.md)). Which layer guards which surface is settled in [`policies.md`](policies.md).

## Authorization Methods

### Model-Based Authorization

Use when the action targets a specific model instance:

```php
public function show(Booking $booking): Response
{
    $this->authorize('view', $booking);  // First line

    // Business logic after authorization...
}

public function confirm(Booking $booking, ConfirmOperatorBookingAction $action): RedirectResponse
{
    $this->authorize('confirm', $booking);  // First line

    // Action handling after authorization...
}
```

### Class-Based Authorization

Use when the action targets the model class (e.g., index/create):

```php
public function index(): Response
{
    $this->authorize('viewAny', Booking::class);  // First line

    // Query building after authorization...
}

public function create(): Response
{
    $this->authorize('create', Booking::class);  // First line

    // Form rendering after authorization...
}
```

### Ability-Based Authorization

Use for non-model permissions (e.g., dashboard access):

```php
public function index(): Response
{
    $this->authorize(Permission::ReportsExport->value);  // First line

    // Dashboard data loading after authorization...
}
```

## Audience before the policy on a broker-only screen

Where a screen exists for one audience only, the audience is named ON THE ROUTE
and refused with a 404 before `authorize()` ever runs:

```php
Route::get('bookings', [BookingController::class, 'index'])
    ->middleware('organization.type:broker')   // 404: not a thing that exists for them
    ->name('bookings.index');

public function index(BookingsBoardQuery $bookings): Response
{
    $this->authorize('viewAny', Booking::class);  // 403: exists, action refused
    // ...
}
```

The policy cannot do the first half: `Gate::before` answers true for Super Admin
and Platform Admin on every ability ([`policies.md`](policies.md)), so
`authorize()` alone would hand platform staff an empty board. And a 403 would
confirm the address means something for the caller, which is how one company
learns another exists — the reason `UserController::authorizeRoster` answers 404
too.

MIDDLEWARE RATHER THAN A CALL IN THE METHOD. An earlier version of this used
`App\Concerns\RefusesNonBrokers` and `$this->abortUnlessBroker($request->user())`
as the first line of the action. A call has to be remembered per action and an
action that forgets it is silently unguarded — nothing fails, the screen just
answers. Declared on the route it is asserted for every route at once by
`tests/Architecture/RoutesTest.php`. See [`routes.md`](routes.md) for why
`EnsureOrganizationType` is back after being deleted with the panel prefixes.

This is the one place a web route carries BOTH middleware and a policy, and it
is not the stacking [`policies.md`](policies.md) forbids: the two answer
DIFFERENT questions, and the refusals are graded to prove it — 404 for an
audience the screen does not exist for, 403 for a broker the screen exists for
who may not perform the action. A write runs the same pair, with the policy half
in its FormRequest ([`http.md`](http.md)).

## A reference-data search has no policy to ask, in either place

`/search/*` reads data no organization owns, so there is no permission question — not in the
controller, and not in the `SearchTermRequest` the four `{Subject}SearchController`s share.
That request declares `rules()` and returns an EXPLICIT `true` from `authorize()`, which is
the deliberate answer rather than Laravel's silent default ([`http.md`](http.md)); the
route's audience middleware and its throttle are the whole guard, and an `authorize()` every
member of that audience passes can refuse nobody. The exception ends the moment a search
reads organization-owned rows.

## Nested Resources with Scope Binding

When routes use `->scopeBindings()`, the parent-child relationship is validated automatically. Only authorize against the child:

```php
// Route: /bookings/{booking:uuid}/legs/{tripLeg}/passengers
// Route group has ->scopeBindings()

public function syncPassengers(
    Booking $booking,
    TripLeg $tripLeg,
    SyncPassengersToTripLegsAction $action
): RedirectResponse {
    $this->authorize('syncPassengers', $tripLeg);  // Authorize on child

    // $tripLeg is already scoped to $booking by route binding
    // No need to manually check $tripLeg->trip->booking_id === $booking->id
}
```

## Authorization String Must Match Policy Method

The ability string passed to `authorize()` must exactly match the policy method name:

| Controller Call | Policy Method |
|-----------------|---------------|
| `$this->authorize('viewAny', $organization)` | `UserPolicy::viewAny()` |
| `$this->authorize('impersonate', $user)` | `UserPolicy::impersonate()` |
| `$this->authorize('delete', $organization)` | `OrganizationPolicy::delete()` |

## Examples

✅ **Good:**
```php
public function update(UpdateBookingRequest $request, Booking $booking): RedirectResponse
{
    $this->authorize('update', $booking);  // Authorization FIRST

    $booking->update($request->validated());

    return back()->with('success', 'Booking updated.');
}
```

❌ **Bad:**
```php
public function update(UpdateBookingRequest $request, Booking $booking): RedirectResponse
{
    $booking->update($request->validated());  // Business logic before authorization!

    $this->authorize('update', $booking);  // Too late - data already modified

    return back()->with('success', 'Booking updated.');
}
```

❌ **Bad:**
```php
public function update(UpdateBookingRequest $request, Booking $booking): RedirectResponse
{
    if (!auth()->user()->can('update', $booking)) {  // Manual check instead of authorize()
        abort(403);
    }

    // ...
}
```

## Related Rules

- `route-scope-binding-authorization.md` - When scope binding handles authorization
- [`policies.md`](policies.md) — why a domain invariant does not belong in a policy
- `code-architecture-conventions.md` - Action classes pattern
- `permission-naming-convention.md` - Permission naming conventions
