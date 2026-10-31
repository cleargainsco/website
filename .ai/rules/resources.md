---
paths:
  - 'app/Http/Resources/**'
---

# Resources

## Resolve the resource from the model: `toResource()` / `toResourceCollection()`

Never hand-map a model to an array in a
controller, and do not instantiate a resource class directly — let the model
resolve its own resource through Laravel's `TransformsToResource` trait.
`App\Models\{Name}` auto-resolves to `App\Http\Resources\{Name}Resource`.

```php
// ❌ manual instantiation
'user'  => new UserResource($user)
'users' => UserResource::collection($users)

// ✅ the model resolves its own resource
'user'  => $user->toResource()
'users' => $users->toResourceCollection()

// ✅ explicit class only for a nested/partial shape
'organization' => $this->whenLoaded('organization', fn () => $this->organization->toResource(OrganizationSummaryResource::class))

// EXCEPTION — a paginator has no trait, so this is the one place ::collection() belongs.
// Hand it straight to Inertia; do NOT append ->response()->getData(true) (see below).
'users' => UserResource::collection($paginator)
```

Resources go in `app/Http/Resources/` as `final class` with an `@mixin Model`
docblock. `toResourceCollection()` lives on the concrete
`Illuminate\Pagination\LengthAwarePaginator`, not on the contract — a method
that returns a paginator declares the concrete class
([`domains.md`](domains.md)).

## Do not hand-serialise what the resource already serialises
Return values in their PHP form. The resource encodes them.

- BACKED ENUMS: `'status' => $this->status`, never `$this->status?->value`. PHP encodes a backed enum to its backing value automatically, so `->value` is a no-op that also throws away the type on the way past. OrganizationResource has always done this; SyncDriftReviewResource briefly did not.
- VALUE OBJECTS: return the object. A DTO whose public properties ARE the payload needs no `toArray()` — three public properties become three JSON keys. Writing one creates a second copy of the property list that can disagree with the first. App\Support\DriftSubject is the example.
- The consequence for a DTO used this way: its public shape IS its wire format. A fourth public property adds an API key, so anything not meant for the client belongs behind a method rather than on the constructor.

Reach for explicit conversion only when the wire shape genuinely differs from the PHP shape — a date needing `toIso8601String()`, a model exposing `public_id` under the key `id`, a relation flattened with `whenLoaded`.

Separately (from .ai/rules/domains.md, repeated because the old advice is everywhere): DO hand a resource collection straight to Inertia — `Resource::collection($paginator)`. Inertia's prop resolver calls `toResponse($request)->getData(true)` itself for anything `Responsable`, and `JsonResource` does not implement `Arrayable`, so the `Arrayable` branch never catches it. `->response()->getData(true)` in a controller is that same pair of calls written by hand, and it eagerly resolves a prop that `Inertia::defer()` wanted lazy.

## Guard relationships with `whenLoaded()`
A relationship in a
resource is `'organization' => $this->whenLoaded('organization', fn () => ...)`,
never an unconditional `$this->organization`, so a resource cannot trigger a
lazy load — which throws here ([`models.md`](models.md)) — and the payload's
shape is decided by the query's `->with()`. Scalars need no guard; `when()` is
for a field that depends on a condition, not on a relation being loaded.

## Money is the exception to "return the value object"
The "VALUE OBJECTS: return the object, the resource encodes them" rule above does NOT hold for money. `Brick\Money\Money` implements JsonSerializable and encodes itself as `{"amount":"12345.00","currency":"USD"}` — the major-unit decimal as a string, integer minor units dropped. Returning a cast attribute directly hands the browser a figure it can only reach through `parseFloat`, which is the hazard the `unsignedBigInteger` column exists to prevent, in the one place it looks correct.

KEY THE PAYLOAD ON THE ATTRIBUTE, NEVER THE COLUMN. `incumbent_cost_minor` as a key gives you `incumbent_cost_minor.minor`, which stutters and leaks a storage detail. The column name has no business in a payload:

```php
'incumbent_cost' => MoneyResource::make($this->incumbent_cost),
'auto_award' => MoneyResource::optional($this->auto_award_price),   // nullable columns
// => "incumbent_cost": {"minor": 2450099, "currency": "USD"}
```

`::optional()` exists because `make(null)` cannot express it — `make()` returns `static` and PHP forbids widening that to `?static` in an override.

TWO FIELDS, AND THEY ARE COMPLETE. `minor` (int) plus `currency` (ISO code) is the whole amount, losslessly. It is called `minor` rather than Stripe's `amount` because `amount` does not say what unit it is in, and unit ambiguity is the failure this whole design exists to prevent. Formatting is the FRONTEND's job — a server-rendered string bakes in the server's locale rather than the viewer's:

```ts
import { formatMoney } from '@/lib/money';

formatMoney(look.price_to_beat)   // '$24,500.99' in the VIEWER's locale
```

DO NOT DIVIDE BY 100 ON THE CLIENT. The scale belongs to the currency — JPY has no minor unit, so a hardcoded hundred renders ¥1,000 as ¥10. `formatMoney()` derives it from Intl. Arithmetic uses `minor`, exact in JS below Number.MAX_SAFE_INTEGER (~$90 trillion at cents).

TESTING TRAP: a nested resource resolves during JSON encoding, not in `toArray()`. `(new SomeResource($model))->toArray($request)` — the style tests/Feature/Models/BookingTest.php uses — leaves a MoneyResource OBJECT where the keys belong and proves nothing. Assert against `->response()->getData(true)`.

## A departure time is the airport's, never the reader's — derive it server-side
Never ship `departs_at` to a screen that will render it. It is a UTC instant, and `new Date(...)` in a browser is the VIEWER's clock — a 22:00 KTEB departure on the 12th is 02:00Z on the 13th, so the board shows a different day depending on where the reader sits, next to a cell reading KTEB. The trip_legs migration spells this out; it is the common case for a US desk, not an edge one.

Convert through the origin airport's timezone (TripLeg::departsAtLocal(), which uses the offset in force on the departure date, not today's) and send the worded label plus an `is_time_known` flag. `departs_at` null is a real state ("sometime on the 14th") — render `departure_date_local` and never invent a midnight.

The general test: does the value belong to the VIEWER or to the THING? Money belongs to the viewer, so it crosses as minor units + currency and is formatted client-side. A route string belongs to neither — derive it (Trip::routeLabel()). A departure belongs to the airport — derive it. Do not ship raw legs so a page can assemble what the server already knows.

## Queries and enums return data; a Resource builds the shape the page reads
A query returns models, collections or paginators, and an enum returns its cases. Neither builds the array a page consumes — `{value, label}` option lists included — and neither does a controller mapping inline (`->map(fn ($x) => ['value' => ..., 'label' => ...])`). Decided 2026-09-15 on LL-71.

The shape is a JsonResource in app/Http/Resources, applied in the controller through `toResourceCollection()`:
- `$options->charterOperators()->toResourceCollection(JetNetOperatorOptionResource::class)`
- `collect(BookingSource::cases())->toResourceCollection(EnumOptionResource::class)`

An enum sent as options implements `App\Contracts\Labelled` and goes through the shared `EnumOptionResource`; do not write a per-enum option resource. `JsonResource::withoutWrapping()` is on, so a non-paginated collection reaches Inertia as a plain array, not under `data`.

The `LastLooksBoard*` queries are the standing exception, and the only one. `App\Domains\Shared\Queries\LastLooksBoardQuery` and its audience subclasses `BrokerLastLooksBoardQuery` and `OperatorLastLooksBoardQuery` return the looks board's whole payload — summary, tiles, tabs and, per panel, its note, footer, empty state, columns and rows down to each row's tag, tone, money, standing and actions — and `LastLookController` hands `board()` to the page as it stands, no resource in between. They stay tabbed-panel projections ([`domains.md`](domains.md) said they would), confirmed by the owner on 2026-09-16 when the broker's four real panels — drafts, live, expired, withdrawn — started reading `last_looks` rows; its awarded panel and four stat tiles are still invented, and the operator's board is fixture throughout. The reason the broker class records: one look is read by two audiences and the broker's half carries what the operator must never see, so a projection per audience makes that confidential half something you have to write rather than remember to strip.

Not a licence for the next query: it names these three classes, and any other query still returns models, collections or paginators with a Resource building the shape.

## A fixed vocabulary on the wire is a backed enum, never a string literal
SETTLED 2026-10-02. When a payload field can only ever hold one of a few values the page
switches on, it is a backed enum in PHP and a mirror in `resources/js/types/enums.ts` — not
`'urgent'` written into a resource, and not a string-union `type` on the frontend.

The looks board's two are the example:

| Field | PHP | TypeScript |
| --- | --- | --- |
| `tone`, `tag_tone`, a delta's and a stat tile's `tone` | `App\Enums\LastLookTone` | `LastLookTone` |
| an action's `variant` | `App\Enums\LastLookActionVariant` | `LastLookActionVariant` |

- Return the CASE — `'tone' => LastLookTone::Muted` — and let the resource encode it (see
  "Do not hand-serialise" above). The phpstan types in `App\Contracts\LastLooksBoard` name
  the enums, so a string slipped back into a row fails `types:check`.
- Where a state decides the value, the enum describing that state answers it —
  `LiveLookStanding::tone()` — rather than a ternary in the resource.
- `tests/Architecture/EnumMirrorsTest.php` holds every enum in `enums.ts` to its PHP enum case
  for case, so adding a case means adding it on both sides; `js.md` covers the frontend half.

Next in line, not yet converted: an action's `key` (`compare`, `decide`, `withdraw`, `send`,
`contract`, `bid`), which PHP still writes as strings against the frontend's
`LastLookActionKey` union. Convert it when that code is next touched.

## Copy a resource sends is read from `lang/`, grouped by screen
SETTLED 2026-10-02. A label, note, empty state or button text that a resource puts in a
payload is a key in `lang/en/{screen}.php`, never a literal in the resource. The looks board's
is `lang/en/last-looks.php` (`board`, `stats`, `panels.{tab}`, `draft`, `live.{state}`,
`awarded`, `settled.{state}`); the sync review's is `lang/en/sync-review.php`. One file per
screen, grouped by what on the screen the words belong to, so changing what a reader is told
is an edit to one file and never a hunt through PHP.

- KEY BY THE ENUM THAT CHOOSES THE LINE. Where a state picks the wording, the lang group is
  keyed by that enum's values and the enum reads it — `LiveLookStanding::note()` asks for
  `last-looks.live.{$this->value}.note`. The logic stays in PHP; the words stay in the file.
- PLURALS AND PLACEHOLDERS LIVE IN THE STRING: `trans_choice('last-looks.window.hours',
  $hours)`, `__('last-looks.board.summary', ['live' => $live, 'drafts' => $drafts])`. Never
  assemble a sentence from fragments in PHP; a second locale cannot reorder what was
  concatenated.
- STYLE STAYS IN PHP, words do not. A contract tag's colour is a `match` returning a
  `LastLookTone`; its label is `last-looks.awarded.contract_status.{status}`.
- `__()` IS TYPED `array|string`, BENEVOLENTLY. Returned on its own it passes as a string;
  mixed with `null` in a ternary, a `match` arm or `??` it does not, and PHPStan refuses the
  return. Return each branch separately (`AwardedLastLookResource::note()`,
  `SyncDriftReviewResource::categoryLabel()`) or collect lines and `implode()` them
  (`LiveLookStanding::note()`) — never cast it or add an ignore.
- A comment that guards a sentence ("no figure here: …") moves into the lang file beside it.

NOT A SWEEP of the ~250 `__('A plain sentence')` calls elsewhere, mostly exception messages:
those read fine where they are and stay, unless a second locale is actually planned. The rule
is for copy a resource builds from logic. The operator board's React page still words itself
(`pages/operator/last-looks/index.tsx` says why in its header); the broker board's copy stays
server-side, as decided above.
