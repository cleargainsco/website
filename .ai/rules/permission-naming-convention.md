---
paths:
    - 'app/Enums/**'
    - 'app/Policies/**'
---

# Permission Naming Convention

Permissions use dot notation with a matching PascalCase enum case in
`app/Enums/Permission.php`.

> There is **one** Permission enum, shared by session callers and API tokens.
> Do not add an `api.`-prefixed sibling — the last section explains why
> `Organization::canHold()` depends on the two drawing from one vocabulary.

## Rule

**All permissions MUST follow the established format:**

1. **`{resource}.{action}`** — every case in the enum today is two-part.
2. **`{resource}.{parent}.{feature}`** — a three-part form is permitted for a
   nested feature. **None exists yet.** If you add the first one, say so here.
3. **Enum case naming:** PascalCase combining all parts without dots.

## The permissions that exist

Measured from `app/Enums/Permission.php`, 2026-09-09, plus those the LL-62 stack
adds (`bookings.viewAny` on #37, `bookings.create` and `bookings.update` on #42,
`lastLooks.withdraw` on #77):

| Enum case                | Value                     |
| ------------------------ | ------------------------- |
| `BookingsIntake`         | `bookings.intake`         |
| `BookingsViewAny`        | `bookings.viewAny`        |
| `BookingsCreate`         | `bookings.create`         |
| `BookingsUpdate`         | `bookings.update`         |
| `BookingsAssignSalesRep` | `bookings.assignSalesRep` |
| `LastLooksViewAny`       | `lastLooks.viewAny`       |
| `LastLooksCreate`        | `lastLooks.create`        |
| `LastLooksSend`          | `lastLooks.send`          |
| `LastLooksWithdraw`      | `lastLooks.withdraw`      |
| `LastLooksAward`         | `lastLooks.award`         |
| `LastLooksViewInvited`   | `lastLooks.viewInvited`   |
| `BidsViewAny`            | `bids.viewAny`            |
| `BidsViewOwn`            | `bids.viewOwn`            |
| `BidsSubmit`             | `bids.submit`             |
| `OperatorsViewAny`       | `operators.viewAny`       |
| `OperatorsApprove`       | `operators.approve`       |
| `OperatorTailsApprove`   | `operatorTails.approve`   |
| `FleetManage`            | `fleet.manage`            |
| `ReportsExport`          | `reports.export`          |
| `OrganizationsManage`    | `organizations.manage`    |
| `UsersManage`            | `users.manage`            |

Note the resource segment is **camelCase where it is two words**
(`lastLooks`, not `last_looks` or `lastlooks`), and the action segment is the
method name a policy would use (`viewAny`, not `view_any`).

## Standard actions

Use the Laravel policy vocabulary where the action is CRUD-shaped:

| Action    | Meaning                 |
| --------- | ----------------------- |
| `viewAny` | List/index access       |
| `view`    | Single record access    |
| `create`  | Create new records      |
| `update`  | Modify existing records |
| `delete`  | Soft delete (archive)   |
| `restore` | Restore soft-deleted    |

## Domain-specific actions

Where the operation is not CRUD, the action segment names the domain verb —
`lastLooks.send`, `lastLooks.award`, `bids.submit`, `operators.approve`,
`bookings.intake`. Two rules govern when to mint one:

- **Permissions name domain ACTIONS, not transports.** Split when you would
  plausibly grant one and withhold the other; never because one arrives over a
  bearer token and the other over a session (see the last section).
- **A distinction that is about _whose rows_, not _what kind of action_, is
  not a permission.** That is the global scope's job — see
  [`scopes.md`](scopes.md) and [`domains.md`](domains.md). `bids.viewAny`
  versus `bids.viewOwn` is the deliberate exception: those really are two
  different capabilities, not one capability over two row sets.

## Enum case naming

Convert the value to PascalCase, dropping the dots:

| Permission value       | Enum case             |
| ---------------------- | --------------------- |
| `lastLooks.viewAny`    | `LastLooksViewAny`    |
| `bids.viewOwn`         | `BidsViewOwn`         |
| `organizations.manage` | `OrganizationsManage` |

❌ **Bad:**

```php
case Bookings_Intake = 'bookings_intake';   // underscore, not dot
case bookingsIntake  = 'bookings.intake';   // case must be PascalCase
case Intake          = 'bookings.intake';   // missing resource prefix in the case
case ApiBookingsView = 'api.bookings.view'; // transport in the name
```

## Adding a new permission

1. Add the case to `app/Enums/Permission.php`.
2. Add it to at least one role group. The groups are static methods on
   `Permission` itself — `brokerAdminPermissions()`,
   `brokerSalesRepPermissions()`, `brokerOperationsPermissions()`,
   `operatorAdminPermissions()`, `operatorCharterSalesPermissions()` — and
   `RoleName::permissions()` only maps a role onto one of them. Edit
   `Permission.php`; `RoleName.php` needs no change unless you are adding a
   role. **This is not optional for an API permission:**
   `Organization::canHold()` refuses to issue a token for an ability no role at
   that audience holds, so a permission on no role is unobtainable (see the
   last section).
3. Re-seed: `php artisan db:seed --class=PermissionSeeder`. Not optional —
   see the next section for what skipping it looks like.
4. Add the policy method if it is model-based — but read
   [`policies.md`](policies.md) first: `Gate::before` answers true for Super
   Admin and Platform Admin, so a policy is advisory for exactly the people who
   reach the platform screens, and a domain invariant written there is skipped.
5. Mirror the case in `resources/js/types/enums.ts`, in the same change. There
   is no generator, and a missing one fails silently as a permission the
   frontend thinks nobody holds.

## Skip the re-seed and you get a 403 nowhere near the cause

Adding a case to the enum is not enough to use it. Spatie stores permissions as
DATABASE ROWS, so until `PermissionSeeder` runs, `$user->can('your.new.permission')`
is false and every policy asking for it refuses.

The symptom is a bare 403 on a route you just built, with correct-looking code
everywhere you would think to look — the policy, the FormRequest and the role set
all read fine, because the gap is in the database. Tests do not catch it: they
seed a fresh database each run, which is exactly why it reaches you and not CI.
Sign out and back in afterwards if the session cached the old set.

The seeder is idempotent and syncs both ways — a permission added to the enum
appears, one removed disappears along with its role assignments.

Deploys are already covered: `ReferenceDataSeeder` calls `RoleSeeder` then
`PermissionSeeder`, and that is what deployed environments run. This is a
local-database-lag problem only.

## One Permission enum for both token and session callers

There is no separate API permission enum, and there must not be. `Organization::canHold()` refuses to issue a token for an ability no role at that audience holds — that is what makes "an operator can never be issued an intake token" true by construction, and it only works because token abilities and role permissions are drawn from the same vocabulary. Split the enum and the invariant has nothing to compare against.

Permissions name domain ACTIONS, not transports. Split when you would plausibly grant one and withhold the other; never because one arrives over a bearer token and the other over a session. `bookings.intake` is separate from the desk's manual create and correction — `bookings.create` and `bookings.update` (#42), both held by Broker Admin — for that reason and not because it is the API one: per ADR 0003 an API booking is signed by construction while a manual one relies on desk discipline, so they are separately grantable.

THE DESK'S OWN TWO PASS THE SAME TEST, and were one `bookings.manage` until LL-62's review. A create adds a row; a correction REPLACES THE ROUTE WHOLESALE over work somebody else may have done — which is why that path alone carries a version fingerprint, a row lock and a refusal while a look is open. A desk that lets every AE enter a booking and keeps corrections to seniors is an ordinary arrangement, and one grant could not express it. Note what the split cost: an enum case, a role set, the `resources/js/types/enums.ts` mirror, a re-seed, and the two `<Can>` gates on the board. Doing it after the permission had shipped would have added a migration to that list, which is the argument for splitting at the moment of minting rather than later. `lastLooks.withdraw` came off `lastLooks.send` the same way on #77, before either had shipped, so each control on the Looks board asks for its own grant. A `GET /v1/bookings` gets `bookings.viewAny` here, matching `lastLooks.viewAny` — not an `api.` prefixed name.

Known tension: because canHold() requires the permission to sit on some human role at that audience, a new API permission must also be added to a role set (e.g. brokerAdminPermissions()) or issuance throws RoleOrganizationTypeMismatch and the token is unobtainable. There is currently no way to express a machine-only permission; canHold() is where that would change.

## Related rules

- [`policies.md`](policies.md) — why an invariant does not belong in a policy
- [`scopes.md`](scopes.md) — whose rows, answered by a global scope
- `app/Policies/UserPolicy.php` — the live example: the permission question
  only, with the audience check alongside it
