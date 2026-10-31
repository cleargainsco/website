---
paths:
    - 'app/Http/**'
---

# Http

## Controllers are thin: authorize → validate → delegate → respond

A controller method does four
things in this order and nothing else: authorize (see
[`controller-authorization-pattern.md`](controller-authorization-pattern.md)),
validate through a FormRequest, delegate to an Action, return a response.

```php
final class OrganizationController extends Controller
{
    public function store(StoreOrganizationRequest $request, CreateOrganizationAction $action): RedirectResponse
    {
        $this->authorize('create', Organization::class);

        $organization = $action->handle($request->validated());

        return redirect()->route('organizations.show', $organization);
    }
}
```

- **All validation** in a dedicated `FormRequest` — never inline
  `$request->validate()`.
- **Business logic** in `app/Actions/`; controllers contain none
  ([`code-architecture-conventions.md`](code-architecture-conventions.md)).
- **Reads** come from the audience's domain — a `Queries/` class
  ([`domains.md`](domains.md)); the controller renders its result as a resource
  collection and assembles nothing.
- **Error handling**: try/catch around the Action, returning
  `Toast::error($e->getMessage())->back()` — never `back()->with('error', ...)`,
  which renders nothing ([`controllers.md`](controllers.md)).

### One resource per controller — a custom verb is a resource you have not named yet

A controller carries only the seven resource actions: `index`, `show`, `create`,
`store`, `edit`, `update`, `destroy`. When a method wants any other name, the
thing it acts on is a resource in its own right — give it a controller named for
that noun and route it with a REST verb. This is the owner's standing
instruction (2026-09-09), illustrated by Dan's LL-66 refactor:

| Was                                                                              | Now                                                                                                                                                                                                                                                                                       |
| -------------------------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `OperatorInvitationController@storeOperator`                                     | `OperatorController@store` — `POST /operators`                                                                                                                                                                                                                                            |
| `OperatorInvitationController@candidates`                                        | `InvitationCandidateController@index` — `GET /invitations/candidates`                                                                                                                                                                                                                     |
| `BookingLookupController@tails` / `@operators` / `@airports` / `@aircraftModels` | `TailSearchController@index`, `OperatorSearchController@index`, `AirportSearchController@index`, `AircraftModelSearchController@index` — `GET /search/{tails,operators,airports,aircraft-models}` (#35; moved from `/bookings/lookup/*`, which named the screen rather than the resource) |

The invitation controller had grown two verbs that were really two nouns —
creating an operator, and listing candidates. Each is now a resource with a
one-method controller, and the route reads as the thing it returns.

The test: can the method be named with one of the seven? If not, extract. State
changes are the usual temptation — `deactivate`, `cancel`, `archive` — and the
answer is a nested resource (`UserDeactivationController@store`) or an `update`
carrying a status field, never a verb on the parent controller.

**Where this stands.** `develop` still carries three non-resource methods
(`own`, `deactivate`, `ping`) that predate the rule; leave them until their file
is next touched. Seven resource actions only is deliberately strict, by the
owner's call: a custom verb (`reAssignSalesRep`, `downloadPdf`, `cancel`, …)
means a new controller, not a new method on this one.

## Validate every write through a FormRequest, then read validated()

Every controller action that accepts input takes a FormRequest (app/Http/Requests/), never the base `Request`. Read data with `$request->validated()` — never `all()`, `input()`, or inline `$request->validate()`.

This is load-bearing, not style: models are unguarded (see .ai/rules/models.md), so the FormRequest is the only thing standing between user input and a mass assignment.

Known exception: app/Actions/Fortify/* implement Fortify contracts that receive `array $input`, not a Request, so they use `Validator::make($input, ...)->validate()`. Keep the rules there in sync with the FormRequests.

Enforced by tests/Architecture/ControllersTest.php.

## Trust the validator once it has run; `prepareForValidation()` runs before it

`ValidatesWhenResolvedTrait::validateResolved()` calls `prepareForValidation()`,
then `passesAuthorization()`, then the validator: it runs before both, on raw
input nothing has guaranteed.

### Rule

**Inside `prepareForValidation()`, guard the raw input before transforming it.**
`?first_name[]=x` arrives as an array and an omitted field as `null`, and
`strip_tags()` / `mb_strtolower()` / `mb_trim()` handed either throws a
`TypeError` — an array always, `null` under our `strict_types` — which is a 500
where a 422 was owed. A value that fails the guard is left unmerged and the
validator refuses it 422. That `is_string()` is boundary input (case 4 below),
not the doubling [`trust-the-layers.md`](trust-the-layers.md) forbids.

**After validation, trust it** — in `passedValidation()`, `validated()`, `safe()`,
the controller and the Action an `is_string()` IS that doubling
([`trust-the-layers.md`](trust-the-layers.md) Rule 1).

Whitespace is never this method's to trim: `TrimStrings` already did it
([`normalize-on-write.md`](normalize-on-write.md)).

### Rationale

- **The type is unknown until the rules run** - the guard turns a 500 into a 422 by declining what the rules will refuse

### Examples

In `prepareForValidation()`:
`if (is_string($v = $this->input('email'))) { $this->merge(['email' => mb_strtolower($v)]); }`
— never the unguarded `mb_strtolower($this->input('email'))`, which is a
`TypeError` on `?email[]=x` or an absent field.

### Nullable Fields - The Same Guard

`is_string()` is false for `null`, so an absent field stays absent, and
`ConvertEmptyStringsToNull` already turned an empty or whitespace-only string
into `null`. Truthiness is no substitute — `['x']` is truthy and still throws,
`'0'` is falsy and would be overwritten with `null`.

### Validation Rules Guarantee Types

These hold only AFTER validation, never inside `prepareForValidation()`:

| Rule         | Guaranteed Type | When It Applies                                      |
| ------------ | --------------- | ---------------------------------------------------- |
| `'string'`   | string          | Always, after validation                             |
| `'email'`    | string          | Always, after validation                             |
| `'integer'`  | int             | After validation                                     |
| `'numeric'`  | float or int    | After validation                                     |
| `'boolean'`  | bool            | After validation (converts 1/0, true/false, "1"/"0") |
| `'array'`    | array           | After validation                                     |
| `'required'` | Not null        | Always, after validation                             |
| `'nullable'` | string or null  | After validation                                     |

### When Manual Type Checks ARE Needed

Manual type checks are only needed when:

1. **External API responses** - Data from third-party APIs may not be typed
2. **Raw database queries** - Direct SQL queries without models
3. **Headers or environment variables** - System inputs outside validation
4. **Before validation runs** - In middleware, and in a FormRequest's `prepareForValidation()`, which runs before the rules

### Related Rules

- `.ai/rules/code-architecture-conventions.md` - General code architecture patterns
- [`normalize-on-write.md`](normalize-on-write.md) - which layer trims HTTP whitespace
- `app/Http/Requests/` - Location of FormRequest classes in this project

### Quick Reference

Guard in `prepareForValidation()`; no check on `validated()` or downstream.

## A FormRequest exists to validate input; where there is one, it carries the policy

SETTLED 2026-09-19, and this section argued BOTH sides until then — its heading said
`authorize()` returns `true` and the controller asks the policy, while its last paragraph
said the request answers it and the controller does not ask again. Two rules, one section.

The rule, in two halves:

1. **No FormRequest without input to validate.** `rules()` is what the class is for. A
   request with none is a file holding one `can()` call — `SendLastLookRequest` was exactly that,
   the only one of eighteen with no `rules()`, and it is gone. Send carries no fields: the
   window length, the reserve and the thresholds were all decided at draft, and a payload
   re-choosing the window would slip past the AC6.3 guard that refused it.
2. **Where a FormRequest exists, `authorize()` asks the policy. Where there is none,
   `$this->authorize()` is the controller's first line**
   ([`controller-authorization-pattern.md`](controller-authorization-pattern.md)).

WHY THIS AND NOT "ALWAYS THE CONTROLLER", which was the other candidate and is what most of
the tree does. Laravel's `authorize()` RETURNS TRUE WHEN NOT OVERRIDDEN, so a request that
stays silent is an open door that looks exactly like a deliberate one. Making the override
mandatory turns that silence into a rule violation rather than a judgement call, and a
request already resolves before the controller body — so the refusal order is identical
either way.

**THE 404 CARVE-OUT, AND IT IS NOT OPTIONAL.** A failed `authorize()` raises
`AuthorizationException`, which is a **403**. A check that must answer **404** therefore
CANNOT move into a request. `UserController::authorizeRoster()` is the live example —
`abort_unless($actor->can('viewAny', [User::class, $organization]), 404)` — and a 403 there
would confirm that another organization exists, which is the disclosure AC14.4 forbids.

IT TAKES THE POLICY WITH IT, and that is wider than it first reads. A FormRequest resolves
BEFORE the controller body, so a policy moved into `authorize()` answers 403 before the
controller's 404 ever runs — inverting the order and leaking exactly what the 404 was
protecting. Where a 404 must precede a 403, BOTH checks stay in the controller.
`UserController::store` and `::update` are that case and were deliberately left alone: a
Broker Admin posting to another organization's roster gets 404 today and would have got 403
([`RosterManagementTest`](../../tests/Feature/RosterManagementTest.php): "another
organization's roster does not exist as far as an admin is concerned").

AN AUDIENCE CHECK IS STILL THE ROUTE'S, never the request's. `organization.type:broker`
resolves before the FormRequest does, so the 404 lands before validation can answer 422 and
hand a field-by-field account of the schema to somebody the address is not meant to exist
for. An earlier draft had `BookingWriteRequest::authorize()` call
`RefusesNonBrokers::abortUnlessBroker()`; that trait is gone, because a check written into
each request is one every new request must remember, where the route declaration is asserted
for all of them by `tests/Architecture/RoutesTest.php`.

WHERE THE TREE STANDS. Seventeen requests. `StoreOrganizationRequest`,
`UpdateOrganizationRequest` and `UpdateOperatorFleetRequest` were converted on 2026-09-19.
Two still do not override `authorize()`, for a reason: `StoreUserRequest` and
`UpdateUserRequest` are the 404-first case above. Nothing is unguarded.

## Custom messages live in the request's `messages()` / `attributes()`

When a rule
needs a human message or a field a better name, override `messages()` /
`attributes()` on that FormRequest rather than editing `lang/*/validation.php`.

## Reading validated input: hand the Action a `Data` object, not an array

SETTLED (2026-09-11), superseding the note that stood here. Two shapes were in
play and the note left the choice open: handing the Action `$request->validated()`
— the array — or the eleven older sites in `UserController` and
`OrganizationController` that read typed scalars,
`$request->safe()->string('name')->toString()`. The answer is neither. A write
hands the Action a `{Noun}Data` built from `validated()`, in that audience's
`Domains/{Audience}/Data/`:

```php
$operator = $action->handle(NewOperatorData::from($request->validated()));           // LL-66
$booking  = $intakeBooking->handle($organization, EnvelopeData::from($request->validated()));
```

THE REASON IS PHPSTAN, AND `NewOperatorData` PUTS IT BEST: "an array shape cannot
be proven from `$request->validated()`, which is `array<string, mixed>`, so the
choice is between a Data class and a docblock that only claims to be typed."
Booking intake had that docblock, and a `(string)` cast on the idempotency key to
make the claim true at one site out of four.

`from()`, NEVER `validateAndCreate()`. The FormRequest has already validated —
including, for booking intake, the payload's own rules, which it takes from the
Data class rather than restating ([`api.md`](api.md)). Validating a second time is
the doubling [`trust-the-layers.md`](trust-the-layers.md) forbids. Rules and
authorization stay on the request; the Data object carries shape only.

WHAT NOT TO TYPE. A field the request stores VERBATIM stays an `array` on the
Data object. `EnvelopeData::$payload` is the example and the reason is measured:
a Data object round-trips an ABSENT optional back as an explicit null, so typing
`payload` as `PayloadData` and re-serialising it would write keys the caller never
sent — changing `App\Support\PayloadSignature`, so every honest retry reads as a
different submission and is refused 409. Type what you READ; leave what you STORE
as it arrived.

ONE VALUE IS THE EXCEPTION. A write whose whole input is a single scalar reads
`$request->safe()->string('reason')->toString()` and hands the Action that — a Data
class wrapping one property is ceremony, and `from()` cannot prove an array shape it
does not have. The moment a second field arrives, the Data class does.

The eleven `safe()->string()` sites that read SEVERAL values are not a sweep waiting to
happen; convert one when its controller is next touched, and never mix idioms in one
method.

## Reach the actor with `#[CurrentUser]`, not `$request->user()`

`#[CurrentUser] User $author` gives a non-nullable `User` and names what the actor is FOR;
`$request->user()` returns `?Authenticatable` and says nothing. `Api\V1\BookingController`
already does this with `#[CurrentUser(self::GUARD)] Organization $organization`, and
`BookingLastLookController::store()` is the first web controller to follow it.

The sixteen web controllers still reading `$request->user()` are deferred, not a sweep:
convert one when it is next touched.
