---
paths:
    - 'app/**'
---

# Transaction Management in Actions

Database transactions belong in action classes, not controllers. This ensures atomicity is encapsulated with the business logic.

## Overview

When multiple database writes must succeed or fail together, wrap them in `DB::transaction()`. This transaction should be inside the action class that performs the operation, not in the controller.

## Rule

**Database transactions MUST be in action classes, NOT in controllers.**

Controllers should:
- Inject and call action classes
- Handle try/catch for user-facing errors
- Never contain `DB::transaction()`

## Rationale

- **Single Responsibility** - Controllers handle HTTP, actions handle business logic
- **Reusability** - Actions can be called from jobs, commands, other actions
- **Testability** - Test transaction behavior by testing the action
- **Encapsulation** - Atomicity requirements are implementation details of the action

## Example

```php
// ✅ SendAwardContractAction owns its transaction; ContractDeliveryController only calls it
public function handle(Award $award): SendContractResult
{
    $sent = DB::transaction(function () use ($award): Award {
        $held = $award->refreshForUpdate();
        throw_if($held->isContractSent(), ContractCannotBeSentException::itWasAlreadySent());
        $held->fill([...])->save();

        return $held;
    });

    FanOutContractSentAlertsJob::dispatch($sent);

    return SendContractResult::sent($broker, Award::SIGNATURE_MINUTES);
}

// ❌ the controller opens the transaction around the writes itself
public function store(Award $award): RedirectResponse
{
    DB::transaction(function () use ($award): void { /* guards and writes */ });

    return Toast::success(__('Contract sent.'))->toRoute('awarded.index');
}
```

## Transaction Scope

The transaction wraps the minimum necessary operations. The reads that decide the write — the locked re-read and its
guards — go inside it with the writes, under the lock; reads that decide nothing can stay before it, and the result
object is created after it.

## Testing Transactions

Verify atomicity by testing rollback: make one write in the operation fail, then assert that none of the operation's
rows were written — no partial data.

## Exceptions

Transactions MAY be in controllers when:
- Prototyping/MVPs (move to action before merging)
- Simple single-model operations (though action is still preferred)

Even in these cases, consider whether an action class would improve the code.

## Related Rules

- `code-architecture-conventions.md` - Action classes pattern
- `sync-action-orchestration-pattern.md` - Orchestration actions
- `result-objects-pattern.md` - Result objects for action returns
