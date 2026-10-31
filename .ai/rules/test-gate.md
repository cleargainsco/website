# Test gate (REQUIRED)

Its own file with no `paths:`, so it loads for frontend work too: [`laravel-conventions.md`](laravel-conventions.md) loads only for PHP.

Always run the full gate, not just `php artisan test`, before finalizing changes:

- `composer test` — `config:clear` → `lint:check` (Pint) → `types:check`
  (PHPStan) → `test:refactor` (Rector dry-run) → `test:arch` (the
  `tests/Architecture/` suite) → `test:unit` (Pest, parallel, TIA — [`testing.md`](testing.md))
- `composer ci:check` — `npm run check` (vp: format + lint) → `npm run
  types:check` (tsc) → `composer test`
- `composer test:browser` / `:browser:headed` — the browser suite, which
  `ci:check` does NOT cover and which runs only locally, never in CI
  ([`testing.md`](testing.md))

`ci:check` is the whole gate for everything except that suite, which runs before
a release to `main`, in the three passes [`docs/RELEASE.md`](../../docs/RELEASE.md#before-it-run-the-browser-suite) lists, not on each change.
