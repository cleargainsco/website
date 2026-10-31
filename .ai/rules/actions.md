---
paths:
    - 'app/Actions/**'
---

# Actions

## An organization and its first admin are paired in an Action, never an observer

`CreateOrganizationAction` creates the company and its admin (`RoleName::adminFor`, then `CreateUserAction`) in one transaction. Any future path that onboards a company — inviting an operator off the JetNet mirror — belongs beside it, calling the same pieces.

Do not move this to an `Organization::created` observer. An observer fires with no admin address to use, so a company created by a sync would get an invented one, and an invented address cannot receive the invitation that is the account's only way in. It would also put a user on every factory-made organization, which breaks `DeleteOrganizationAction`'s "an organization with people cannot be removed".

The invariant is "a company a person onboards arrives with its admin", not "every organizations row has a user".

## Operator invites create an invitation, not a pending organization

Inviting an operator off the JetNet mirror list creates an invitation record only. The `Organization` and its admin are created at acceptance, through an action beside `CreateOrganizationAction` (reuse `RoleName::adminFor`). Decided before the sync was built; do not create a pending organization at invite time.

Why not pending rows: every query touching operators would need a "not pending" clause — the listing, the LastLook recipient set, bid eligibility, roster counts — and forgetting one sends a LastLook to a company that never accepted (see the global-scope rule in .ai/rules/domains.md). A pending admin also holds the platform-unique `users.email` forever, so a never-accepted invite blocks re-inviting anyone at that address. And withdrawing an invite would mean deleting an organization with people on it, which `DeleteOrganizationAction` refuses by design.

The invitation carries: `operator_id` (the registry row — ADR 0008's collapse, so there is NO local copy of the company name or JETNET ids any more), invitee contact name, email and phone, a SHA-256 of the token, invited_by, status, sent_at, a non-null expires_at, accepted_at, revoked_at, and a nullable organization_id stamped at acceptance so the list can show Invited / Expired / Accepted and the company's origin stays traceable.

The company usually has no registry row when the admin picks it: `operators` is minted by booking intake, so the recruitment typeahead searches the JETNET MIRROR (~9,400 charter operators, also matched by the owners they fly for) and the action mints the row through `ImportOperatorFromJetNetAction`. Searching must never write.

Acceptance needs its own action, not `CreateUserAction`: that one stores a random password and emails an invitation link (AcceptUserInvitationAction) because somebody else creates the account. An operator invitee sets their own password on the claim form, so create the organization and admin with it and send no second email. `AcceptOperatorInvitationAction` does this, and also sets `email_verified_at` — the token was delivered to that address and they opened it, so asking them to confirm it would be asking twice.

Two-factor is NOT part of acceptance: it is opt-in per user (#280), chosen in security settings as an authenticator app or an emailed code, and everybody without one is prompted after signing in. Nothing forces enrolment.

## An Action takes models and one Data object — never a list of scalars

`handle()` takes the models it acts on, plus ONE Data object built from `$request->validated()`. Never a spread of scalar parameters.

Bad — the controller ends up doing conversion, null-coalescing and lookup, none of which is its job, and every added field touches three files:

    $operator = Operator::query()->where('public_id', $request->safe()->string('operator')->toString())->firstOrFail();
    $action->handle($request->user(), $operator, $request->safe()->string('email')->toString(),
        $request->safe()->has('contact_name') ? $request->safe()->string('contact_name')->toString() : null, ...);

Good:

    $action->handle($request->user(), IssueInvitationData::from($request->validated()));

Use a `Spatie\LaravelData\Data` class in `app/Domains/<Domain>/Data/`, with snake_case properties matching the request keys so `from()` maps them without per-property attributes. The validated array on its own is acceptable for something trivial, but PHPStan cannot prove an array shape from `validated()` (it is `array<string, mixed>`), so anything with more than one field wants the Data class.

VALIDATION AND AUTHORIZATION STAY ON THE FORMREQUEST, not on the Data class. `BookingIntakeData` carries validation attributes because the API validates THROUGH laravel-data — a broker's integration posts a nested payload and the shape is the contract. Data built from a screen's FormRequest must not restate rules the request already owns; two definitions of the same rule is one too many, and the request's is the one that decides what the user is told.

RESOLVING AN IDENTIFIER INTO A MODEL IS THE ACTION'S JOB, not the controller's. `IssueOperatorInvitationAction::resolveOperator()` turns either a JETNET company id or a registry ULID into an `Operator`, minting the row if needed — inside the transaction, where it belongs. A controller that does the lookup is a controller making a domain decision.

Examples: `IssueInvitationData`, `ClaimInvitationData`, `NewOperatorData`.

## A locked write: guards, one write, then dispatch

- Lock, then re-check: inside `DB::transaction` take `$held = $model->refreshForUpdate()` and test every guard on `$held`, never the caller's copy (`SendAwardContractAction`).
- Guards are one line each: `throw_if`/`throw_unless` with a named constructor on a `{Thing}Cannot{Verb}Exception extends DomainException`, whose translated message is written for its reader (`ContractCannotBeSentException`).
- Side effects run after the commit: dispatch outside the transaction, to a job that implements `ShouldQueueAfterCommit` (`FanOutContractSentAlertsJob`).
- A fan-out hands one `AlertOccasion` to `SendAlertDeliveriesAction`, and each alert handler keys its deliveries on the occasion and who they go to, so a re-run sends nothing twice (`FanOutContractSentAlertsJob::occasionKey`). An alert that goes by mail alone calls `CreateNotificationDeliveryAction` itself (`FanOutBookingReceivedAlertsJob`).
