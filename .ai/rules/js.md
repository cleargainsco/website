---
paths:
  - 'resources/js/**'
---

# Js

## Regenerate Wayfinder with --with-form, or the whole frontend stops type-checking
Run `php artisan wayfinder:generate --with-form`, never the bare command.

`vite.config.ts` configures the plugin with `formVariants: true`, so the generated helpers carry a `.form()` variant that ~15 source files use (`Controller.store.form()`, every auth page, settings, users, sync-review). The bare artisan command regenerates without form variants and silently strips `.form` from every helper — `npm run types:check` then fails across files nobody touched, and the cause is nowhere near the symptom.

`resources/js/actions` and `resources/js/routes` are gitignored, so this does not show up in `git status` and cannot be fixed by checking anything out. Rerun with the flag, or let `npm run dev` / `npm run build` regenerate through the vite plugin, which always passes it.

## Format money with lib/money.ts, never a hardcoded /100
Money arrives from App\Http\Resources\MoneyResource as `{ minor: number, currency: string }` — the integer in the currency's smallest unit, plus the ISO code. No formatted string is sent, on purpose: one rendered in PHP would carry the SERVER's locale, and the viewer's is the one that matters.

Format with `formatMoney()` from `@/lib/money`, or `formatMoneyOr()` for the nullable columns. It leaves the Intl locale undefined, so a German broker gets a comma decimal separator without the server knowing anything about them.

NEVER DIVIDE `minor` BY A HARDCODED 100. The scale belongs to the currency: JPY has no minor unit and BHD has three, so `minor / 100` renders a 1,000 yen fare as ¥10. `minorUnitsPerMajor()` asks Intl for the real figure — the client-side mirror of the server using `Money::of()` rather than multiplying by a hundred.

DO ARITHMETIC ON `minor`, which is an integer and stays exact below Number.MAX_SAFE_INTEGER (~$90 trillion at cents). `toMajorUnits()` returns a float and is for display only — a chart axis, an input default. Converting first and adding afterwards is how cents go missing. `sumMoney()` adds and throws on a currency mismatch rather than returning a meaningless total.

Note resources/js/types/looks.ts predates this and carries `LastLookMoney { label, amount: string }` — a pre-formatted string from the server. That is the older pattern; new money fields should ship as MoneyResource and format on the client.

## Client reads go through lib/http and the default queryFn

`useQuery({ queryKey: [route.url(...)] })`, then read `data`. The one `queryClient` lives in `lib/http.ts`, and its
default `queryFn` fetches the key's first element as the URL, so no component writes `fetch`, the `Accept` header, the
status check or the abort signal. Turn a failure into words with `messageForFailure()`: 401 and 429 are worded once in `lib/http.ts`, and
a screen passes only the statuses it can name. A label uses the same word the request's `attributes()` gives the
field ([`requests.md`](requests.md)).

## A failed visit is handled once, by handleFailedRequests
`lib/request-failures.ts` answers every Inertia visit that fails without a validation answer, app-wide.
A screen adds `onHttpException` or `onNetworkError` only for its own state (marking an upload row failed),
never to word the failure or hide Inertia's dialog, and unlocks a control with `onFinish`. Return `false`
only where the screen already shows the failure in place: it skips the global handler, the expired-session
reload included. `useHttp()` and query reads are not covered.

## Wire shapes live in types/, imported where needed
A type describing what the SERVER sends gets its own file under `resources/js/types/` and is imported where it is needed — `import type { Money } from '@/types/money'`. It does not live in the `lib/` module that happens to use it first. `types/pagination.ts` (Paginated), `types/looks.ts` (LastLooksBoard, LastLookStat) and `types/money.ts` are all this shape, and components import them by path.

Only the four cross-cutting modules are re-exported from `types/index.ts` — auth, enums, navigation, ui. Everything else is imported by its own path, so adding a wire shape does not mean touching the barrel.

The exception is a DERIVED type that only narrows something else and has no meaning apart from its module's functions: `OnboardedKind` in `lib/organizations.ts` narrows `OrganizationType` and correctly stays there. The test is whether a component would ever import the type WITHOUT the functions. If yes, it is a wire shape and belongs in `types/`.

This has come up in review more than once. Put the type in `types/`, import it where needed.

## A control guards itself, and asks the clock
- A control offered in more than one place is one component whose props `Omit` `disabled` and `onClick`, so it alone owns the enable rule. Callers pass the action as a callback, and the label where it varies (`bid-button.tsx`).
- Ask the clock, not the status. "Still open?" is `urgencyOf(deadline, now)` from `lib/countdown.ts`, because a status waits for the sweep. A time-sensitive component ticks its own `useNow`.
- A control that cannot act is disabled, not hidden, beside whatever says why.
- Derive display state in a pure `lib/` function of the record and `now` (`contract-standing.ts`, `contract-progress.ts`); components only render it.

## Server vocabularies are enums from types/enums, styled in one lib/ map
A value the server sends from a backed PHP enum is typed with its mirror in
`types/enums.ts` and compared against a member — `look.stands.tone === LastLookTone.Urgent`,
never `=== 'urgent'`. Do not declare a string-union `type` for one; `EnumMirrorsTest` can
only hold an `enum` to its PHP original ([`resources.md`](resources.md)).

Turning such an enum into classes or a component variant happens in ONE module per
vocabulary, keyed with `Record<TheEnum, …>` so a new case fails `types:check` there and
nowhere else. The looks board's is `lib/last-look-styles.ts`:

- `toneClass` — `LastLookTone` → text colour
- `toneBadgeVariant` — `LastLookTone` → `Badge` variant
- `actionButtonVariant` — `LastLookActionVariant` → `Button` variant

Import from there; never write a local `Record` over these enums inside a component, and
never import such a map out of another component file (`toneClass` used to live in
`last-looks-stats.tsx`). Type a variant map's values from the component's own prop —
`NonNullable<ComponentProps<typeof Button>['variant']>` — so a renamed shadcn variant breaks
the map instead of silently falling back.

## Copy lives where it is shown; lang/ is only for words the server picks
A label written once goes inline in the component that shows it, so a page says what is on it.

A table of words keyed by a tab or a state goes in a words module under `lib/` (`contract-words.ts`, `awarded-words.ts`, `looks-words.ts`), with `:placeholders` filled by `fillWords()` from numbers the server sends as data (`hold_hours`, `contract_minutes`).

`lang/en/*` holds only what the SERVER words: a sentence a resource picks by a row's state, one pluralised with `trans_choice`, or one also used in an email or a validation message. Never send a whole language file to a page as a prop.

Why: a sweep that moved every static label into language files was closed unmerged (PR #296, 2026-10-09) because the components stopped telling a reader what was on the screen.
