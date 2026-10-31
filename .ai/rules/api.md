---
paths:
  - 'app/Http/Controllers/Api/**'
---

# Api

## API authorization lives in route middleware, never in the controller
What a token may do is answered by `abilities:` in routes/api.php; who holds it by `api.organization:`. Controllers do not re-check. A controller-side `authorizeAbility()` was removed because it was unreachable on every mounted route — the middleware refuses first, so the only way to exercise it was to invent a middleware-less route in a test, and it covered only the controllers that remembered to call it.

The guarantee is enforced instead by tests/Architecture/ApiTest.php: every route carrying `auth:sanctum` must also carry an `abilities:` naming a value declared in App\Enums\Permission, and must name its audience with `api.organization:`. That covers routes nobody has written yet and controllers that never extend ApiController.

Action parameters typed `#[CurrentUser(self::GUARD)] Organization` are NOT nullable. The middleware guarantees a non-null broker organization, so a null there means a route mounted without its middleware — PHP refusing it as a 500 in development is the intended fail-loud, same bargain ApiController strikes over an inherited `$this->authorize()` (which is the wrong tool here: it resolves through the Gate for a User with spatie roles, where the principal is an Organization with none).

## A FormRequest mirrors its controller's namespace
`App\Http\Controllers\Api\V1\BookingController` takes
`App\Http\Requests\Api\V1\StoreBookingRequest`, the way
`Controllers\Settings\ProfileController` already takes `Requests\Settings\ProfileUpdateRequest`.
It sat flat at `Requests\StoreBookingRequest` until LL-61, which is the odd one out
rather than the convention.

Two reasons beyond tidiness. There are already TWO `BookingController`s — the web one and the
API one — so a flat request directory has to invent distinguishing names for what are the same
nouns on different surfaces. And the API surface is versioned in the path: ADR 0003 makes the
payload contract a COORDINATED BREAK rather than a new version a caller opts into, so if a V2
ever exists its request belongs beside its controller rather than beside V1's.
