---
paths:
    - 'app/Rules/**'
---

# Rules

## Reusable validation rule objects

This directory holds rule OBJECTS reused across FormRequests. The FormRequest is still where every write is validated (see .ai/rules/http.md); a class earns a place here only when the same check appears in more than one request, or when the check needs constructor arguments a string rule cannot express.

Implement `Illuminate\Contracts\Validation\ValidationRule` and declare the class `final readonly`. Take what the rule needs as constructor arguments rather than reading config or the container, so the same rule can be used with different arguments in different requests. App\Rules\ValidMoney takes the currency explicitly and has no default, because a default would reintroduce the hardcoded 'USD' that .ai/rules/migrations.md forbids.

PRESENCE IS NOT A FORMAT RULE'S JOB. Return early on null and '' and let `required` / `nullable` decide whether absence was allowed. A rule that also policed presence cannot be composed with `nullable`.

Messages are read by the API caller or the person filling in the form, so write them as field errors — "The :attribute may not have more than :digits decimal places." — not as advice to a developer. The exception the underlying code throws usually is developer-facing; do not reuse its wording.

PHPStan trap: `$fail` is `Closure(string, string|null=): Illuminate\Translation\PotentiallyTranslatedString`, NOT `Closure(string): void`. Annotating it as void makes every `$fail(...)->translate([...])` call fail analysis with "Cannot call method translate() on void".

Test through `Validator::make([...], [...])->passes()` and assert the message with `errors()->first()`. That exercises the rule the way the framework runs it, including how it composes with `required` and `nullable`, which calling `validate()` directly does not.
