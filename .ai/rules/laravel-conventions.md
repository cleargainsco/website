---
paths:
  - '**/*.php'
---

# Laravel Conventions

API resources, mass assignment, authorization and policies, the controller & FormRequest pattern, and reads vs writes live in the files that own them: [`resources.md`](resources.md), [`models.md`](models.md), [`policies.md`](policies.md), [`http.md`](http.md), [`domains.md`](domains.md).

## 1. Provider-Agnostic Services

`app/Services/` is for external-API clients only, one namespace per vendor
(`app/Services/JetNet/`, `app/Services/Aviowiki/`), each bound to its own
interface (`JetNetClientInterface`, `AviowikiClientInterface`) by a deferred
provider named for the vendor ([`providers.md`](providers.md)). Vendor-specific
handling stays inside the vendor's namespace; what every client shares — the
retry policy — is one trait, `App\Services\Concerns\RetriesTransientFailures`,
with a vendor quirk overridden in the vendor's own trait
([`services.md`](services.md)). Boundary types are `{Thing}Data` under
`app/Services/{Vendor}/Data/`. When two providers become interchangeable behind
one contract, map provider → implementation with an enum `match`.

## 2. Testing (REQUIRED)

The gate is [`test-gate.md`](test-gate.md), which loads in every session, PHP or not.

## 3. Config Organization

Keep third-party service credentials in `config/services.php`; Socialite and other packages expect `config('services.{provider}')`, so don't create separate config files and merge them back with `mergeConfigFrom()`. Extract a dedicated config file only when a feature area has significant non-credential configuration that would genuinely clutter `services.php`.

## 4. PHP Code Style

- `declare(strict_types=1)` at the top of **all** PHP files
- `final` on all Action, FormRequest, and Resource classes
- `final readonly class` for Actions (no state mutation after construction) — e.g. `App\Actions\LastLooks\SendAwardContractAction`

## 5. Eloquent Over Raw SQL (REQUIRED)

Always use Eloquent query builder, scopes, and relationships instead of raw SQL. Raw SQL (`DB::raw()`, `DB::select()`, `whereRaw()`, `selectRaw()`) only when Eloquent genuinely cannot express the query — e.g., PostgreSQL-specific operators (`~*`, `JSONB` path queries, window functions, `LATERAL JOIN`).

```php
DB::select('SELECT * FROM airports WHERE icao = ?', [$icao]);   // ❌ Eloquent handles this
Airport::query()->where('icao', $icao)->get();                 // ✅
```

**When raw SQL is used**, add a brief inline comment explaining why Eloquent is insufficient.

The one deliberate raw-SQL site here is `BidScope`'s `EXISTS` against
`last_looks`. It is not a shortcut: `whereHas('lastLook')` would apply
`LastLookScope`, which admits an operator to every look they qualify for,
and so would return every rival's bid — see [`scopes.md`](scopes.md).
