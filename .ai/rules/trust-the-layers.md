# Trust the Layers Below You (REQUIRED)

Don't reinvent what a layer below already handles: a doubled check, encoding or helper makes the reader investigate twice. If removing it leaves the tests passing and production working, delete it.

## Rule 1: Trust validated payloads

A `FormRequest` rule of `string`, `integer`, `array`, `boolean`, etc. is a guarantee: once validation passes, **do not re-check** the type. The same holds after a Spatie `Data` object is constructed, behind a type hint, and on a cast attribute.

**Anti-pattern name: "defensive doubling".** Use this label in PR comments and review findings — "this is defensive doubling, see `trust-the-layers.md` Rule 1."

### Cast → check mapping (Eloquent attributes)

A cast guarantees the type at read time. The only legal runtime check is null vs. not-null (or empty vs. not-empty for collection-shaped values). **`is_*()` checks on cast attributes are unreachable code.**

| Cast on the model     | ❌ Wrong (defensive doubling)     | ✅ Right                                  |
| --------------------- | --------------------------------- | ----------------------------------------- |
| `'array'`             | `is_array($model->x)`             | `$model->x !== null` / `$model->x === []` |
| `'string'`            | `is_string($model->x)`            | `$model->x !== null` / `$model->x !== ''` |
| `'integer'`           | `is_int($model->x)`               | `$model->x !== null`                      |
| `'boolean'`           | `is_bool($model->x)`              | `$model->x` (it IS the boolean)           |
| `'datetime'`          | `$model->x instanceof Carbon`     | `$model->x !== null`                      |
| `'collection'`        | `$model->x instanceof Collection` | `$model->x->isNotEmpty()`                 |
| Custom enum cast      | `$model->x instanceof FooEnum`    | `$model->x !== null` (or compare to case) |
| Custom DTO cast       | `$model->x instanceof FooData`    | `$model->x !== null`                      |
| Spatie `Data` field   | `is_X($dto->field)`               | `$dto->field !== null`                    |
| Validated FormRequest | `is_X($validated['key'])`         | use `$validated['key']` directly          |

`JetNetTail` casts `raw_response` to `'array'`: `$tail->raw_response === null` ✅, never `is_array($tail->raw_response)` ❌.

### Where the defensive check IS appropriate (boundary input)

Where input is not yet validated: a custom cast decoding a raw DB column (`NullableFloatCast`), an API response before its DTO, file or stream reads, `Request` payloads in middleware before any FormRequest, `mixed` values in a `collect()->filter()` over untyped data. Mark it `// boundary input: <why>` so the next reviewer (and any grep-based lint) skips it.

## Rule 2: Let the framework serialize / cast

Eloquent's `'array'`, `'object'`, `'collection'`, `'datetime'`, enum and custom casts and Spatie Data's `Arrayable` / `Jsonable` do the encoding. **Use them**: `$tail->raw_response = $payload` ✅, never `json_encode($payload)` ❌ on a cast field, and never `json_decode()` ❌ to read one back when the cast already decodes it. `$dto->toArray()` is an array, not JSON: spread it to add metadata (`[...$dto->toArray(), 'synced_at' => $now]`).

**Three legitimate exceptions:**

1. **`Eloquent::upsert()` does not run casts on bulk inserts.** If you genuinely need `upsert()`, document the limitation in a one-line comment and define a `private const JSON_FLAGS` rather than scattering flags inline. Where the column relies on a cast or Attribute, route each row through a transient model instead and drain `getAttributes()`, saying so in the action's comment.
2. **External-API request bodies** (`JetNetClient` posting a search to JETNET): Eloquent isn't involved.
3. **Logging structured data** — `Log::info(..., $context)` already JSON-encodes; passing a pre-encoded string is wrong.

**Cast selection, simplest first:** `'array'` / `'object'` / `'collection'` (they already keep null apart from `[]`); `'json:unicode'` for unescaped multi-byte text; a custom enum / class / DTO cast for a typed object; a custom `Attribute` last, only for an invariant the built-ins cannot enforce: `JSON_THROW_ON_ERROR`, a non-null `array` getter over a `NOT NULL` column, a multi-step transformation, legacy rows lacking a field. Keeping null apart from `[]` or needing `JSON_UNESCAPED_UNICODE` is not a reason: the built-ins do both.

## Rule 3: Don't extract a single-call private helper with a trivial body

A `private function` earns its place when it names a non-obvious concept or is called 3+ times (`centralize-shared-code.md`).

**Concrete bound — when in doubt, inline:** if a private method has **≤ 3 lines of body AND ≤ 1 caller**, inline it at the call site unless the name itself carries genuine semantic value. Methods at exactly 4 lines or with exactly 2 callers are judgment calls — favor inline if the inline form reads naturally; favor extract if inline forces deep nesting or obscures intent. Keep a single-call helper whose body is 5+ lines AND whose name explains why, one guarding a test-only race whose production callers may multiply, or one whose inline body would sink into deep nesting. A one-caller helper whose body only appends `?? 0` to another call ❌: inline the `?? 0` ✅.

## Decision tree before adding defensive code or a helper

**Extracting a helper?** → How many callers? If < 3 AND body is < 5 lines → inline.

## Enforcement (review agents)

Violations of any rule above are **blocking findings** for code-review agents. When `code-review-security`, `review-convention`, `review-logic-bugs`, or `senior-architect` scans a diff:

- A violation MUST be flagged as `severity: high` (not `nit` or `polish`).
- The finding MUST cite the rule by anchor — e.g., `trust-the-layers.md#rule-1-trust-validated-payloads`, `trust-the-layers.md#rule-2-let-the-framework-serialize--cast`, `trust-the-layers.md#rule-3-dont-extract-a-single-call-private-helper-with-a-trivial-body`.
- The agent MUST NOT mark a finding as resolved unless the diff actually removes the offending code OR adds a one-line code comment explaining which exception (per the rule's documented exception list) applies.

For human reviewers: cite the rule by name in the PR comment ("violates `trust-the-layers.md` Rule 2 — use Eloquent cast, not inline `json_encode`"). The grep-friendly anchors above keep citations consistent.

When introducing genuinely defensive code that LOOKS like a violation but is correct (boundary input, framework limitation, etc.), document the reason inline so the next reviewer doesn't re-flag it:

```php
// trust-the-layers.md Rule 2 exception 1: Eloquent::upsert() does not run casts, and this JSON
// column has none to route a transient model through.
$rows[] = [..., 'payload' => json_encode($payload, self::JSON_FLAGS), ...];
```
