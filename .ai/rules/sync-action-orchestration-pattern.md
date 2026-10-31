---
paths:
    - 'app/**'
---

# Sync Action Orchestration Pattern

Higher-level "Sync" actions orchestrate multiple smaller actions, handle single-vs-multiple target logic, and wrap operations in database transactions.

## Overview

When an operation can target a single entity or multiple entities (e.g., "apply to this leg" vs "apply to all legs"), create an orchestration action that:

- Composes smaller, focused action classes
- Determines eligible targets based on business rules
- Wraps the entire operation in a database transaction
- Returns a typed result object

## Rule

**Multi-target operations MUST use a `Sync*Action` pattern that orchestrates smaller actions and handles transaction management.**

## Rationale

- **DRY** - Logic for determining eligible targets lives in one place
- **Atomicity** - All-or-nothing database operations
- **Composition** - Small, testable action classes combined into larger operations
- **Controllers stay thin** - Complex logic moves to dedicated action classes

## Example

`{Thing}` stands for whatever is being applied; `Booking`, `Trip` and `TripLeg` are this codebase's models.

```php
// ✅ The orchestration action owns eligibility, the transaction and the result
final readonly class Sync{Thing}ToTripLegsAction
{
    public function __construct(private Add{Thing}ToTripLegAction $add) {}   // inject the smaller action

    public function handle(Booking $booking, TripLeg $tripLeg, {Thing}Data $data, bool $applyToAllLegs): Sync{Thing}Result
    {
        $legs = DB::transaction(function () use ($booking, $tripLeg, $data, $applyToAllLegs): Collection {
            $legs = $this->eligibleLegs($booking, $tripLeg, $applyToAllLegs);   // it decides the write, so it is read under the lock

            $legs->each(fn (TripLeg $leg) => $this->add->handle($leg, $data));

            return $legs;
        });

        return $applyToAllLegs ? Sync{Thing}Result::multiple($legs->count()) : Sync{Thing}Result::single();
    }

    /** @return Collection<int, TripLeg> */
    private function eligibleLegs(Booking $booking, TripLeg $tripLeg, bool $applyToAllLegs): Collection
    {
        return $applyToAllLegs
            ? $booking->trip->legs()->lockForUpdate()->get()   // filter here when a business rule excludes some legs
            : collect([$tripLeg->refreshForUpdate()]);
    }
}

// ❌ The controller picks the legs and opens the transaction itself, injecting the lower-level action
DB::transaction(fn () => ($request->boolean('apply_to_all_legs') ? $booking->trip->legs()->get() : collect([$tripLeg]))
    ->each(fn ($leg) => $add->handle($leg, $data)));
```

The controller then only calls the orchestration action and turns its result into a toast, as
`LastLookRejectionController` does: `try { $result = $action->handle(...); } catch (DomainException $e) { return Toast::error($e->getMessage())->back(); }`
then `return Toast::success($result->message)->back();`.

## Naming Conventions

| Pattern                 | Example                       | Purpose                             |
| ----------------------- | ----------------------------- | ----------------------------------- |
| `Sync*ToTripLegsAction` | `Sync{Thing}ToTripLegsAction` | Apply data to one or more trip legs |
| `Sync*Action`           | `Sync{Thing}Action`           | General sync/apply pattern          |
| `Batch*Action`          | `BatchUpdateStatusAction`     | Apply same change to multiple items |

## Key Characteristics

1. **Constructor Injection** - Inject smaller action classes
2. **Single Public Method** - `handle()` with clear parameters
3. **Eligibility Logic** - Private method to determine targets: one target when not applying to all; otherwise all related, filtered by status or another business rule where one applies. It decides the write, so it runs inside the transaction and reads with a lock
4. **Transaction Wrapper** - `DB::transaction()` around the eligibility read and the writes (`transaction-in-actions.md`, "Transaction Scope")
5. **Result Object Return** - Typed result with contextual information

## Testing Sync Actions

Test both the single-target and the all-targets case, asserting which targets changed, and that a failure part-way leaves no partial data.

## Related Rules

- `result-objects-pattern.md` - Result objects for action returns
- `code-architecture-conventions.md` - Action classes pattern
- `transaction-in-actions.md` - Transaction management in actions
- [`actions.md`](actions.md) — the Action-level decisions recorded for this codebase
