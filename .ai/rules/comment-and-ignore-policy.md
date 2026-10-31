# Comment and Ignore Policy

Omit all comments except those functionally required. Minimize ignore directives as they often indicate structural problems.

## Comment Policy

**Omit all comments by default.** Only include comments that are functionally necessary for the code or tests to work.

### Allowed Comments

| Type | Examples | When Allowed |
|------|----------|--------------|
| Type annotations | `@var`, `@param`, `@return`, `@template` | When PHP/TS cannot infer types |
| Test annotations | `@test`, `@covers`, `@dataProvider`, `@depends` | Required by test framework |
| IDE/tool directives | `@property`, `@mixin`, `@method` | Required for IDE support on magic methods |
| Suppression directives | `@phpstan-ignore`, `@ts-ignore`, `eslint-disable` | Last resort only (see below) |

### Forbidden Comments

Explanatory comments ("This function does X"); TODO/FIXME (use the issue tracker); commented-out code (git has history); section dividers or decorative comments; obvious documentation ("Constructor", "Getters"); change logs or author attribution.

## Ignore Directive Policy

**Ignore directives are a last resort only.** Their presence usually indicates a structural problem that should be fixed rather than suppressed.

Before adding ANY ignore directive, you MUST try, in order: fix the type (hints, return types, generics); refactor the structure; add `@property` / `@method` / `@template` annotations; check the PHPStan/ESLint/TypeScript config; update the dependency; create type stubs; use `assert()` or a type guard. Only then ignore — and only for a third-party library whose types cannot be fixed upstream, Laravel magic that cannot be annotated (rare), or code genuinely impossible to type.

When you must: target the exact rule/error, give a brief reason WHY, and prefer inline over file-level or global ignores.

```php
/** @phpstan-ignore-next-line argument.type (Laravel facade returns mixed) */   // ✅ specific, with reason
/** @phpstan-ignore-next-line */                                                // ❌ blanket, no reason
```

In TypeScript the same: `// @ts-expect-error - <reason>`, never a bare `// @ts-ignore`.

## The owner ruled on the long docblocks: minimal, and only where the code cannot speak

In the owner's words, 2026-09-18:

> stop adding comments for everything you do. don't add comments unless absolutely
> necessary because the code isn't clear enough as to what it does. you don't have to
> explain what the code is doing, we as developers can read the code and understand it,
> only comment on things that need further explanation

**The test is whether the CODE already says it.** If a reader can get the answer by reading the method, the comment is deleted — it is a second copy of the code that can drift from it. Logic that seems to need one is refactored to be clearer, or extracted into a well-named function.

What survives is the small residue the code genuinely cannot carry:

- **A decision whose alternative looks more obvious than it is**, and would be "fixed" back by the next reader. One or two lines, not an essay: *"No UTC fallback: callers print the zone beside the time, so a guess reads as fact."* *"`min-w-0`: a grid item defaults to `min-width: auto`, which defeats the truncate."*
- **A fixture whose setup is load-bearing**, where dropping a line would leave a test passing for the wrong reason.
- **Everything in the Allowed Comments table above.**

**Banner headers are out** (`=== SHOUTING SECTION TITLE ===` inside a docblock): a comment that needs a heading is an essay, and an essay belongs in the commit message or the pull request.

**Rationale is not a comment by default.** "Why we did it this way" belongs in the commit message; put it in the code only when getting it wrong would silently break something — the UTC-fallback case above is the bar.

Write new code to this. The existing long docblocks are not a sweep waiting to happen — trim them when you are already changing the code beneath them.
