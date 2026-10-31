---
paths:
  - 'app/Http/Controllers/**'
---

# Controllers

## JSON responses are unwrapped; never remove withoutWrapping()
`AppServiceProvider` calls `JsonResource::withoutWrapping()`, and every JSON endpoint answers unwrapped as a result. Return `SomeResource::collection($x)->response()` and leave it alone — do NOT hand-roll a `data` key around it. One endpoint wrapping while the rest do not is the inconsistency, not the fix.

Do not remove the global either. Measured on Laravel 13.30 / inertia-laravel 3.3.3: turning it off fails 13 Inertia tests, because Inertia resolves a `Responsable` prop through `toResponse()`, so every single-resource prop shifts a level — `organization.id` becomes `organization.data.id` in every page component.

The machine-facing API is unaffected either way, and the reason is worth knowing: `App\Concerns\ApiResponse` builds a plain array via `response()->json([...])`, and a JsonResource nested inside one serialises through `jsonSerialize()` → `toArray()`. `$wrap` is only ever applied in `toResponse()`, so `success`/`message`/`data` can never be double-wrapped.

Three audiences, three shapes, do not mix them: `api/*` gets ApiResponse's `success`/`message`/`data` (a promise to a broker's integration, PRD section 9, enforced by bootstrap/app.php's `respond()` closure); a web JSON endpoint our own frontend calls gets the resource, unwrapped; an Inertia prop gets the resource itself, and Inertia unwraps it — see .ai/rules/domains.md, and never write `->response()->getData(true)` in a controller.

## Controllers carry only the seven standard actions — reach for a new controller, not a new verb
A controller has `index`, `show`, `create`, `store`, `edit`, `update`, `destroy` and nothing else. When a screen needs something that is not one of those on THIS resource, it is a different resource: make another controller.

`storeOperator` and `candidates` were both methods on `OperatorInvitationController`, because that is the only screen reaching them. Wrong shape — a controller named for one resource grows a verb for another, and the next such need adds a third verb rather than a third controller. They became `OperatorController@store` (a registry company is its own resource; `index` and `show` belong there when the registry gets a screen) and `InvitationCandidateController@index` (a candidate is not an invitation — most are companies no invitation exists for, and never will).

The route follows the resource, not the screen that happens to use it. `POST /operators`, not `POST /invitations/operators` — nesting would have made a future registry listing `/invitations/operators`, which says something untrue about what it holds.

A single-action controller is fine and often right. Do not merge two resources to avoid one.

Known exceptions, both predating this and neither a licence to add more: `UserController::own` (renders the signed-in user's own roster at `/users`, so the only roster most people can reach is not addressed by an id they could edit) and `deactivate`/`restore` (soft-delete transitions, which are arguably `destroy` and a restore-shaped `update`). Converting them is a separate change; adding a third is not.

## A one-off message is `Toast::`, and a session flash reaches nobody
SETTLED 2026-09-18. Flash a toast through `App\Support\Toast`, which owns the wire shape and
carries the response:

```php
return Toast::success(__('Booking created.'))->back();
return Toast::error($domainException->getMessage())->back();
return Toast::success(__('Decision recorded.'))->toRoute('applications.index');
```

**NEVER `back()->with('error', ...)`, and this is not a style preference — it renders
nothing.** Inertia's middleware shares `errors` (validation) and `HandleInertiaRequests`
shares name/auth/impersonator/counts; the only flash consumer is `use-flash-toast.ts`, which
reads `flash.toast`. A plain session key is written, never read, and the user sees the page
unchanged with no reason why. Fourteen refusal paths were doing exactly that — a reviewer
approving over an unaccepted document, an operator uploading after submitting, a claim on a
revoked link — each refused correctly and silently, and six tests asserted the dead key,
pinning the silence.

THE TYPE IS `App\Enums\ToastType`, whose four cases ARE sonner's method names and the same
union as `FlashToast['type']` in `resources/js/types/ui.ts`. `Toast::of($type, $message)` is
for a type decided at runtime — an action's result saying which outcome it was
([`result-objects-pattern.md`](result-objects-pattern.md)); `SendLastLookResult` carries one,
because "nobody was eligible" is a warning rather than a success and nothing the controller
holds says so.

`back()` AND `toRoute()` ARE ON THE HELPER although Inertia supports chaining off
`Inertia::flash()`. Inertia's `ResponseFactory::back()` declares Symfony's `RedirectResponse`
though it returns Laravel's, so chaining through it widens the return type of every
controller that uses it. Note `toRoute()` where `back()` is wrong — see the next section.

`toast` IS OUR KEY, NOT INERTIA'S. `flash()` is a generic bag; the string appears nowhere in
the package. Keying on the type instead (`flash('error', $message)`) would work equally well
and was weighed — it is not taken because a top-level `flash.error` sits one letter from
Inertia's `props.errors` and means something entirely different. Either way the call site is
the same, because the shape lives in one class now. Changing it is `Toast::of()` and the hook,
nothing else.

A FIELD ERROR IS NOT A TOAST. Validation belongs on the field, through the FormRequest
([`http.md`](http.md)). A toast is for an outcome with no field to attach to.

## Never `back()` from a screen an operator can reach straight after claiming
`back()` reads the `Referer` header and falls back to `session()->previousUrl()`. In this application both can be wrong at once, and the combination is silent.

NO REFERER: `routes/onboarding.php` puts `NoReferrer` on the claim route so the token cannot leak into asset requests. `Referrer-Policy` governs the DOCUMENT, and Inertia never full-reloads after a claim — it client-side visits dashboard then onboarding — so every XHR for the life of that tab sends no Referer.

STALE SESSION: `StartSession::storeCurrentUrl()` skips `$request->ajax()`, and Inertia sends `X-Requested-With: XMLHttpRequest`. No Inertia visit ever updates the previous URL, so it stays on whatever was last loaded as a real page — the claim link.

The live bug: an operator claimed, uploaded their first document, and got "this invitation has already been used — sign in". The sign-in link bounced them back, because they were already signed in.

Use a named route (`to_route('onboarding.show')`) wherever the destination is fixed. A test only reproduces this if it sends `X-Requested-With: XMLHttpRequest` and sets a previous URL — without both, the framework behaves like an ordinary navigation and the bug vanishes. `OperatorDocumentTest` and `SubmitApplicationTest` have those tests.
