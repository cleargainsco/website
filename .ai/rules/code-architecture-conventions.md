---
paths:
    - 'app/**'
---

# Code Architecture Conventions

Core patterns for maintainable Laravel code: DRY, validation separation, action classes, and no service classes.

## Standards

### DRY (Don't Repeat Yourself)
- Extract shared logic into utility methods, model accessors, or injected collaborators
- Avoid copy-pasting similar code across files

### Prefer Composition Over Inheritance
- Domain classes should be `final readonly` implementing interfaces, not extending abstract base classes
- Helper methods that convert data formats (e.g., cents-to-dollars) belong in model accessors, not shared base classes
- If multiple classes need the same collaborator, inject it via constructor (composition) rather than inheriting it
- **Exception:** The Strategy Pattern in `app/Handlers/` may use abstract base classes when a template method pattern is genuinely needed

### Validation in Its Own Place
- Use `FormRequest` classes for controller validation (`app/Http/Requests/`)
- Handlers/strategies can provide validation rules, but FormRequest applies them
- Never validate **input** in controllers or action classes. Shape, type, presence and
  format are the FormRequest's, and an action must not re-check what validation
  guaranteed ([`trust-the-layers.md`](trust-the-layers.md) Rule 1)

**The line is what the answer depends on.** Validation is answerable from the PAYLOAD
alone — is `window_length_minutes` one of 15, 60, 180, 360 or 1440? A **domain invariant** needs the
current state of the database — does this booking already have a live look? does this
organization still have people on it? — and its answer can change between the request and
the write.

An invariant therefore belongs in the Action and is NOT a case of this rule:

- **Only the action holds the lock.** `CreateLastLookAction` asks inside `DB::transaction`
  with the trip row held by `lockForUpdate()`. A FormRequest locks nothing, so two AEs
  opening one booking in the same second would both pass it and both insert. Moving the
  check there does not relocate the guard, it deletes it.
- **Every writer passes the action**; a FormRequest guards HTTP alone. A job, a console
  command and the API never run one ([`policies.md`](policies.md): "Permissions belong in
  policies. Domain invariants do not").

Twenty-six Action classes refuse on domain state, `UpdateBookingAction` and
`DeleteOrganizationAction` among them. Where a nicer message is worth it the request MAY
check too, never instead: `BookingFormRequest` compares the form version "first, for the
message", and `UpdateBookingAction` compares it again under the lock.

### Action Classes
- Business logic belongs in single-purpose Action classes (`app/Actions/`)
- Controllers delegate to actions, never contain business logic
- Actions are `final readonly` with a single `handle()` method (or specific verbs like `request()`, `approve()`)

### No Service Classes
- **Don't:** Create fat `*Service` classes that accumulate miscellaneous business logic
- **Do:** Use Action classes for business operations
- **Exception:** `app/Services/` is reserved for external API integrations (Slack, Aviowiki, etc.)

### Jobs and Notifications
- `app/Jobs/{Subject}/{Verb}{Noun}Job.php`, `ShouldQueue` — jobs are grouped by subject (`Jobs/JetNet`). Every job carries the `Job` suffix; a name without it is wrong.
- `app/Notifications/{Feature}/{Event}Notification.php` — a notification sits flat at the root while its feature has only one or two, and moves into a feature folder (`Notifications/Quotes`, `Notifications/Reminders`) once it has more; custom channels in `Notifications/Channels/`, message value objects in `Notifications/Messages/`, helpers for composing a message in `Notifications/Concerns/`.
- A message renders itself for every channel (`AlertMessage::toMail()`, `AlertMessage::toSlack()`), so each `to{Channel}()` on a notification builds the message and asks it to render. The rendering never sits in a trait the notifications share.
- Model lifecycle hooks are Observers in `app/Observers/` (none here yet); custom Eloquent casts are in `app/Casts/` (`MoneyObjectCast`) — see [`models.md`](models.md).

### Act on a failure where your code catches it
- When code you own catches a failure, act on it there and rethrow: `SlackWebApiChannel` catches Slack's refusal and records it through `RecordSlackRejectionAction`.
- Do not route your own failure through a framework event and an auto-discovered listener. Nothing at the throw site says the listener exists, and an exception inside it replaces the one it was handling. A `NotificationFailed` listener did this job until LL-117's review.
- A listener is for an event something else raises (`MessageLogged`, `CommandStarting` in `AppServiceProvider`).

### Strategy Pattern for Polymorphism
- Use `app/Handlers/` for strategy pattern implementations
- Handlers implement a contract interface
- Registry pattern coordinates handler lookup
- `app/Handlers/Alerts/` is the first: each channel an alert goes out by implements `App\Contracts\AlertDeliveryHandler`, the handlers are tagged under that contract in `AppServiceProvider`, and `SendAlertDeliveriesAction` runs every one — a new channel is a new handler and a line in the tag, never an edit to a job

## Examples

✅ **Good:**
```php
// Action class for business logic
final readonly class ApproveServiceAction {
    public function handle(TripLeg $tripLeg, string $type): Model { }
}

// FormRequest for validation
final class UpdateServicePreferenceRequest extends FormRequest {
    public function rules(): array { }
}

// Domain class implementing interface (composition over inheritance)
final readonly class CompanyMetricsCalculator implements SalesMetricCalculator {
    public function __construct(
        private SalesDataProvider $dataProvider,
    ) {}

    public function calculate(): array { }
}

// Handler for polymorphic behavior (exception: abstract base OK here)
final class BlackCarHandler extends AbstractServiceHandler { }
```

❌ **Bad:**
```php
// Fat service class (don't do this)
class BookingService {
    public function create() { }
    public function approve() { }
    public function sendNotification() { }
    public function generatePdf() { }
}

// Abstract base class with helper methods (don't do this)
abstract class BaseCalculator implements SalesMetricCalculator {
    protected function centsToDollars(int $cents): float { }
    protected function calculatePercentage(float $value, float $total): float { }
}
// Instead: move helpers to model accessors or inline them

// Validation in controller (don't do this)
public function store(Request $request) {
    $validated = $request->validate([...]); // Use FormRequest instead
}
```

## Directory Structure

```
app/
├── Actions/           # Single-purpose business operations, grouped by subject
├── Casts/             # Custom Eloquent casts (MoneyObjectCast)
├── Database/
│   └── Query/         # Custom query expressions, beside Illuminate\Database\Query\Expression (PositionIn)
├── Domains/           # Per audience: Enums/, Queries/ — every READ (domains.md)
├── Handlers/          # Strategy pattern implementations (Alerts/ — one per alert channel)
├── Http/
│   ├── Requests/      # Validation (FormRequest classes)
│   └── Resources/     # API resources
├── Jobs/              # Queued work, grouped by subject
├── Notifications/     # Grouped by feature once a feature has several (none here yet)
├── Observers/         # Model lifecycle hooks (none here yet)
├── Results/           # Typed result objects
├── Services/          # External API clients ONLY, one namespace per vendor
│   ├── JetNet/
│   └── Aviowiki/
└── Support/           # Plain value objects and helpers (E164, DriftSubject)
```

## Related Rules

- `sync-action-orchestration-pattern.md` - Multi-target orchestration actions
- `transaction-in-actions.md` - Transaction management in actions
- `result-objects-pattern.md` - Typed result objects for action returns
- [`domains.md`](domains.md) — reads live in domain query classes, never in Actions
- [`actions.md`](actions.md) — the Action-level decisions recorded for this codebase
