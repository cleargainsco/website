---
paths:
    - 'app/**'
    - 'database/**'
---

# Normalize on Write, Trust on Read (REQUIRED)

Persisted data should already be clean. **The boundary that writes a value owns
normalization.** Read sites must not re-clean it.

This is a sibling of [`trust-the-layers.md`](trust-the-layers.md): casts already
guarantee type, validation already guarantees shape, and **write-time
normalization should already guarantee cleanliness.** Trimming, whitespace
collapsing or quote-stripping at read time is duplicate work that hides a
missing boundary upstream.

```
[ External input ─→ Normalize ─→ Persist ─→ Read ─→ Use ]
                       ↑                          ↑
                   ONE place                NEVER re-clean
```

---

## The boundaries that exist in this project

| Boundary | What arrives dirty | Who owns the cleanup |
|---|---|---|
| **Vendor API** (JETNET, AvioWiki) | sentinel values the type does not express — `'unknown'` where a float belongs, `''`, `0` for an unknown count, a phone written in one of two incompatible schemes | the `*Data` object's cast or `prepareForPipeline()`; see [`services.md`](services.md) |
| **HTTP form input** | leading/trailing whitespace, `\r\n` from textareas, mixed-case email, pasted unicode | whitespace: the global `TrimStrings` middleware, before any FormRequest runs (below); everything else: the FormRequest's `prepareForValidation()`, see [`http.md`](http.md) |
| **Phone numbers, anywhere** | national vs international notation | `App\Support\E164` on the way in; see [`support.md`](support.md) |
| **Seed / reference data** | inconsistent casing, stray whitespace | the seeder, before insert; see [`seeders.md`](seeders.md) |

### HTTP whitespace is trimmed once, by the middleware

**Never trim in `prepareForValidation()`** — over HTTP it is a no-op second trim.
`Illuminate\Foundation\Http\Middleware\TrimStrings` is in Laravel's default GLOBAL
stack and `bootstrap/app.php` does not remove it, so every query-string, form and
JSON string reaches a FormRequest trimmed — and more thoroughly than `mb_trim()`,
because it calls `Str::trim()`: measured on PHP 8.4 both strip ASCII whitespace,
U+00A0, U+2000–U+200A and U+3000, only `Str::trim()` strips U+00AD, U+200B,
U+200E and U+FEFF. `ConvertEmptyStringsToNull` runs straight after, so a
whitespace-only field arrives as `null`. The exceptions are `TrimStrings`' own
and deliberate — `password`, `password_confirmation` and `current_password`, a
password being exactly what was typed — and a request must not trim those either.

What the middleware does not do is still the request's: case-folding an email,
CRLF → LF, `strip_tags()`, reshaping a structure. Phones keep their own row above.

### Where to normalize, in order of preference

1. **In the boundary code that produces the value** — the `*Data` cast, the
   FormRequest's `prepareForValidation()`, the seeder.
2. **In a dedicated `app/Support/` utility** when the same reading is reused —
   `E164` is the canonical example.
3. **In an Eloquent cast's `set()`** once a column has more than one writer —
   an `E164PhoneCast` or `NormalizedEmailCast` in `app/Casts/`. The
   cast is the one place every write passes through, so it outranks a boundary
   helper the moment a second boundary appears ([`models.md`](models.md)).

Never:

- ❌ In an Eloquent `Attribute::make(get: ...)` accessor — the *read* side. A cast's `set()` is the write side and is fine (above).
- ❌ In a Resource (`toArray()`).
- ❌ In a controller on the way out, an Inertia prop, or a query class.
- ❌ Inline at every read site (`trim($model->field)` scattered around).

If a read site calls `trim()`, `Str::squish()` or `preg_replace('/\s+/', ' ', ...)`
on a field that came out of **our** database, that is a smell — fix the write
path, not the read path.

---

## The one deliberate exception: the JETNET mirror is verbatim

**`jetnet_tails` and the other `jetnet_*` tables are NOT normalized on write,
and that is on purpose.** They hold JETNET's own spelling plus `raw_response`
for everything not lifted into a column. Cleaning happens one table further on,
in `DeriveOperatorsAction`, which reads no API.

This does not contradict the rule — it relocates the boundary. The mirror is
not the app's data, it is a *snapshot of what the vendor said*, and its write
boundary is "record faithfully". `jetnet_operators` is the app's data, and
`toRow()` is its write boundary — which is why `jetnet_operators.office` and
`mobile` hold E.164 while the tail rows hold the vendor string.

The reason is recorded in [`actions-jet-net.md`](actions-jet-net.md) and is
worth internalising: normalisation of vendor data is a heuristic you will tune.
Bake it into the mirror and every improvement costs a ~485-page re-sweep
instead of one re-run of `app:jetnet:derive-operators` off the local snapshot.

So: **normalize on write at the boundary that owns the value.** A mirror's
boundary owns fidelity; a derived table's boundary owns cleanliness.

---

## Anti-patterns

### 1. Trimming inside an Eloquent accessor

```php
// ❌ a model that re-cleans on every read
protected function name(): Attribute
{
    return Attribute::make(
        get: static fn (?string $value): string => trim((string) $value),
    );
}
```

The accessor is hiding the fact that `name` is being persisted with whitespace.
The fix is upstream, at the write: over HTTP the whitespace is already gone
(above), so a padded column came in through a writer that bypassed the
middleware — a seeder, a command, an import. Trim there. A FormRequest adds only
what the middleware does not:

```php
// ✅ normalize once, before validation — whitespace is already trimmed
protected function prepareForValidation(): void
{
    if (is_string($name = $this->input('name'))) {
        $this->merge(['name' => strip_tags($name)]);
    }
}
```

Then the accessor is deleted and reads return the verbatim column. The merge DOES
carry an `is_string()` guard — the input is still raw there, so it is boundary
input rather than doubling ([`http.md`](http.md)).

### 2. Re-checking a value that already came out of the database

```php
// ❌
if (is_string($tail->regnbr) && trim($tail->regnbr) !== '') { ... }

// ✅ the cast guarantees the type; the write path guarantees the cleanliness
if ($tail->regnbr !== null && $tail->regnbr !== '') { ... }
```

If `''` and `'   '` would both mean "no value", fix the ingest path so it
stores `null`. **Half-normalised is the state to avoid**: a column has one
shape. `JetNetPhone` stores `null` for the ~2% of numbers it cannot read
rather than a partially-cleaned string.

### 3. Letting the library's default do the normalising

`spatie/laravel-data`'s `BuiltinTypeCast('float')` turns both `'unknown'` and
`''` into `0.0` — which for a coordinate is a real position off West Africa.
A bare `?float` raises a `TypeError` on the same input. Neither is
normalisation. Use a cast that degrades a non-numeric to `null`:
`app/Services/Aviowiki/Casts/NullableFloatCast.php`.

---

## Genuine exceptions (not normalization)

- **Display formatting** — truncation with an ellipsis, locale number/date
  formatting. The stored value stays canonical.
- **Search/match preprocessing** — building a normalized key for a `LIKE`
  query. The column is untouched; the key is transient or lives in a sibling
  column.
- **External boundary on the way out** — shaping a payload for a third-party
  API with its own constraints. That is a write to a different boundary, not a
  read of our own data.

---

## Quick checklist

When you see `trim()` / `Str::squish()` / a regex whitespace collapse in code
that **reads** a model attribute, `*Data` field or query result:

1. **Where was this written?** Trace the persistence path.
2. **Is normalization missing on the write path?** If yes, move it there.
3. **Delete the read-side cleanup.** Trust the column.
4. **Add a console command if old rows are dirty** — a one-time backfill is
   fine, permanent read-time cleanup is not. Data transforms are commands, not
   migrations ([`migrations.md`](migrations.md)).

When you are about to **write** an external value:

1. Normalize *once*, in the boundary code, before it enters a `*Data` object
   or a model.
2. Persist the normalized form — or, for a vendor mirror, persist verbatim and
   normalize in the derived table.
3. Comment a non-obvious normalization briefly ("normalize CRLF → LF").

## Related rules

- [`trust-the-layers.md`](trust-the-layers.md) — don't reinvent what the layer
  below already handles
- [`services.md`](services.md) — vendor-boundary `*Data` objects and their casts
- [`support.md`](support.md) — `E164`, and why picking the wrong entry point is
  silent rather than loud
- [`jet-net.md`](jet-net.md) — the two phone schemes JETNET mixes
- [`actions-jet-net.md`](actions-jet-net.md) — verbatim mirror, normalised
  derivation
