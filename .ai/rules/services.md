---
paths:
    - 'app/Services/**'
---

# Services

## API boundary types are Data objects, not DTOs

Typed objects decoded from an external API live in `app/Services/<Vendor>/Data/`, are named `<Thing>Data` (not `<Thing>DTO`), and extend `Spatie\LaravelData\Data`. There is no `DTO/` directory and no `*DTO` class name — `AirportDTO` was renamed to `AirportData` for exactly this reason. "DTO" remains fine as a word in prose.

**Settled.** This was carried as an open question against a plain `{Verb}{Noun}DTO` shape with no laravel-data dependency; it is not open. The package earns its place at the request boundary as well as the vendor one — `StoreBookingRequest` takes the payload's rules FROM `PayloadData::getValidationRules()` rather than restating them, so the contract is written once ([`api.md`](api.md)) — and `#[MapInputName]` is what lets a wire name differ from the property behind it. Nothing in the tree uses the other shape.

Add a per-vendor base (`JetNetData`) ONLY when several classes share a normalisation of that vendor's quirks; a lone boundary type extends `Data` directly. A normalisation specific to one class goes in that class's `prepareForPipeline()`.

Do not lean on laravel-data's own float handling for a nullable numeric field. Measured on 4.23: a bare `?float` raises a TypeError on `'unknown'` and on `''`, and the package's `BuiltinTypeCast('float')` turns both into `0.0` — which for a coordinate is a real position off West Africa. Use a cast that degrades non-numerics to null (see `Aviowiki\Casts\NullableFloatCast`).

## Retry policy: shared trait plus vendor overrides

`App\Services\Concerns\RetriesTransientFailures` is shared by every external-API client: retry a no-response error, 5xx, 429, 408, 425 — nothing else. Do not add a vendor's quirk to it.

A vendor that overloads a status code overrides `isRetryableStatus()` in its own trait instead — see `JetNet\Concerns\RetriesJetNetFailures`, which adds 403 because JETNET's edge returns it for a throttle. AvioWiki means 403 literally and must keep retrying nothing extra; a test in `AviowikiClientTest` pins that.
