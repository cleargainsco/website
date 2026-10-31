---
paths:
    - 'tests/**'
---

# Testing

Measured across this codebase's 79 test files on 2026-09-09.

## Pest, `tests/{Architecture,Browser,Feature,Unit}`, one `{Subject}Test.php` per subject

Pest only — no PHPUnit-style classes. Files are
`{Subject}Test.php` mirroring the source path under `tests/Unit/` or
`tests/Feature/`. `tests/Stubs/` holds fixture payloads; `tests/Support/` holds
hand-written fakes (`FakeJetNetClient`, `FakeAviowikiClient`). Architecture
invariants live in `tests/Architecture/` and boot without `RefreshDatabase`
(see `tests/Pest.php`). The five here are plain tests over the route table and
reflection, because `arch()` cannot see routes. Use Pest's `arch()` expectations
wherever they can express the invariant. Browser tests are the fourth root —
next section.

## Browser tests live in `tests/Browser`, and are run by path, never by testsuite

Journeys through a real browser, on `pestphp/pest-plugin-browser`, named for the
journey or screen rather than a source file (`SmokeTest.php`,
`Onboarding/ClaimLinkTest.php`). A feature test sees only the props a controller
returned; only these catch a page that renders blank or a component that throws
on mount.

`phpunit.xml` registers `Unit`, `Architecture` and `Feature` only, deliberately: a
`Browser` testsuite would make every bare `pest`, `php artisan test` and `composer
test` — CI's `ci` job included — need a browser installed. Run it by path instead:

- `composer test:browser` — `pest --parallel tests/Browser`, headless
- `composer test:browser:headed` — visible window, serially; `BROWSER_SLOW_MO=500`
  in front holds each click, fill, key press and navigation that many milliseconds

Chromium installs once per machine (`npx playwright install chromium`). A failed
assertion screenshots into the gitignored `tests/Browser/Screenshots/`. The suite
runs only locally, never in CI and outside `composer ci:check`
([`test-gate.md`](test-gate.md)): run it before a release, in the three passes
[`docs/RELEASE.md`](../../docs/RELEASE.md#before-it-run-the-browser-suite) lists —
a bare `composer test:browser` is not that run. `release.yml` gates on `ci`
alone, so a red browser run holds up nothing.

## `composer test:unit` runs Pest's TIA; CI still runs the whole suite

`--tia`: Pest re-runs only the tests affected since the last run and replays the rest from
the gitignored `.pest/tia` graph. CI runs everything because it has nothing to replay: a
fresh checkout holds no graph, and `coverage: none` gives it no driver to record one. If CI
ever turns coverage on, its path needs `--no-tia`. Not `--locally`: beside `--tia` it does
nothing, and it never reads `CI` or `GITHUB_ACTIONS` anyway, only Pest's own `--ci`.

The graph is per checkout, so lanes running tests at once never share one. A new Polyscope
workspace skips the first recording because `polyscope.json`'s setup copies the main
checkout's graph from `$POLYSCOPE_ROOT_PATH/.pest`; Pest then re-runs only what differs.
The step is a no-op when the main checkout has no graph yet or the workspace already has one.

Recording needs Xdebug. Load it once in Herd's `config/php/84/php.ini`, OFF, so nothing else
pays for it; the script sets `XDEBUG_MODE=coverage` for the test run alone:

```ini
zend_extension=/Applications/Herd.app/Contents/Resources/xdebug/xdebug-84-arm64.so
xdebug.mode=off
```

then `herd restart`. Not `mode=coverage` or Herd's `debug,develop` example: coverage mode
slows every Herd site, `artisan` and PHPStan by ~65%, and `start_with_request` makes every
process try to reach an IDE on port 9003. The first run records the graph (about 19
minutes); after that a run with nothing changed takes seconds. Without Xdebug, a PHP change
makes Pest warn and run the full suite rather than trust a stale graph. A run narrowed to a
path or `--filter` bypasses TIA. Browser scripts never take `--tia` (`TestScriptsTest`).

## `test()` and `it()` are both in use; group with `describe()`

There is more `test()` than `it()` here, so either is fine — the description is a
sentence either way. Group related cases under `describe('Subject', ...)`: prefer
that shape for a new file with more than a handful of cases. The booking intake
suite (LL-61) is the first to follow it throughout, so copy one of those files
rather than an older flat one.

GROUP BY SUBJECT, NOT BY OUTCOME. Several `describe`s per file, each naming a
thing the reader might be looking for — `ResolveTailTest` splits into resolving a
tail, what the broker asked for winning, and what the stage will not invent. One
`describe` wrapping a whole file adds a level of indentation and nothing else, and
a `describe('refusals')` opposite a `describe('successes')` files a test by the
assertion rather than by the behaviour, which is not how anyone looks for one.

## `RefreshDatabase` is wired once; factories over inserts

`tests/Pest.php` applies `RefreshDatabase` to every Feature and Browser test
(`->in('Feature', 'Browser')`) — do not add `uses(RefreshDatabase::class)` per
file, it is declared once. Create test-owned rows with factories. A raw
`DB::table()->insert()` is for proving a schema constraint and must supply every
NOT NULL column that has no default. Reference data comes from the seeder
(`seed(ReferenceDataSeeder::class)`), never a factory — `AircraftCategory::for()`
resolves, it does not fabricate ([`factories.md`](factories.md)).

## Vendor clients: `Http::fake()` against the real client

Vendor integrations are tested by faking HTTP (`Http::fake()`, 31 sites) and
letting the real `JetNetClient` / `AviowikiClient` run.
The two interface-bound fakes in `tests/Support/` are for the paths where an
HTTP fake is the wrong altitude — a whole sweep that must never touch the
network. Reach for `Http::fake()` first; bind a fake only when the test is about
what the app does with a response, not how it fetched it. Mockery is rare
(2 sites) — prefer a fake over a mock.

## Assertions: `assertInertia()`, `assertJson()` / `assertJsonFragment()`, `actingAs()`

Inertia pages are asserted with `assertInertia(fn (Assert $page) => ...)`
(49 sites). JSON with `assertJson()` / `assertJsonFragment()` —
`AssertableJson` is unused. Sign in with `actingAs($user)`;
`Sanctum::actingAs` is unused, so follow the existing API
tests' token setup rather than introducing it.

`Sanctum::actingAs()` does not merely go unused here — it CANNOT work. It mocks
`Sanctum::personalAccessTokenModel()`, and this application's is
`App\Models\OrganizationApiToken`, which is `final`, so Mockery raises "marked
final and its methods cannot be replaced". To act as an organization principal
in a test that does not go over HTTP, do what `actingAs()` does apart from the
token mock:

```php
Auth::guard('sanctum')->setUser($organization);
Auth::shouldUse('sanctum');
```

When the test needs real abilities, go over HTTP with a real token instead:
`$this->withToken($org->issueApiToken(...)->plainTextToken)` — see
`tests/Feature/Api/BookingIntakeTest.php`.

## Confidentiality tests assert 404, not 403

A user of one organization asking for another organization's record gets a
**404** — the response must not confirm the row exists (AC14.4). Every
tenancy-scoped model needs that test ([`domains.md`](domains.md),
[`scopes.md`](scopes.md)). A refusal about the _action_ stays 403.

## `pestphp/pest-plugin-type-coverage` is not installed

Adopting it is a dev-dependency decision for the owner.
