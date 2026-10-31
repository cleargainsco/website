---
paths:
    - 'app/**'
---

# Result Objects Pattern

Actions should return typed result objects instead of raw values, enabling contextual responses and type-safe operations.

## Overview

When actions can produce different outcomes (single vs multiple updates, success with different messages, etc.), use dedicated result classes with static factory methods to communicate the outcome back to the caller.

## Rule

**Actions returning contextual information MUST use typed result objects in `app/Results/`.**

Result objects should be:
- `final readonly` classes
- Have static factory methods for each outcome type
- Include all necessary data (message, counts, models)
- Use named constructor parameters for clarity

## Rationale

- **Type safety** - Callers know exactly what data they'll receive
- **Contextual messages** - Different outcomes produce appropriate messages
- **Testability** - Easy to assert on specific result states
- **Single responsibility** - Actions don't need to know about flash messages

## Example

```php
// ✅ app/Results/SendContractResult.php — one named constructor per outcome, each wording its message
final readonly class SendContractResult
{
    private function __construct(public string $message) {}

    public static function sent(string $brokerName, int $signatureMinutes): self
    {
        return new self(message: __('Contract sent to :broker. They have :minutes minutes to sign it.', [...]));
    }

    public static function reminded(string $brokerName): self
    {
        return new self(message: __('Reminder sent to :broker.', ['broker' => $brokerName]));
    }
}

// ✅ SendAwardContractAction::handle(Award $award): SendContractResult ends with
return SendContractResult::sent($broker, Award::SIGNATURE_MINUTES);

// ✅ ContractDeliveryController::store() only relays it
return Toast::success($result->message)->toRoute('awarded.index');

// ❌ the controller words the outcome, or the action returns a primitive that loses it
return Toast::success($sent ? 'Contract sent.' : 'Reminder sent.')->back();
public function handle(LastLook $lastLook): int { /* ... */ return $pending->count(); }
```

When the caller needs the created or updated model, carry it on the result (`public ?Award $award = null`), set by the
named constructor for the outcome that has one. `RejectAllBidsResult::rejected(int $bidsRejected)` only words its
count into the message and carries no count field; when a caller needs the number, expose it, as
`CloseLastLookWindowResult` does with `public int $bids`.

## When to Use Result Objects

Use result objects when:
- Action has multiple possible outcomes
- Controller needs contextual success messages
- Caller needs to know specifics (counts, created models)
- Testing needs to verify specific outcomes

Don't need result objects when:
- Action has single outcome (simple void action)
- No contextual information needed by caller
- Action just returns the created/updated model directly

## Related Rules

- `code-architecture-conventions.md` - Action classes pattern
- `sync-action-orchestration-pattern.md` - Multi-target orchestration actions
- `transaction-in-actions.md` - Transaction management in actions
