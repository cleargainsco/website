---
paths:
    - 'app/Models/Scopes/**'
---

# Scopes

## Tenancy is a global scope asking OrganizationPrincipal, never a per-query filter

LastLook, Trip, TripLeg and Bid carry global scopes (`#[ScopedBy]`). Never add `->where('operator_id', ...)` "to be safe": the scope is either working (the clause is noise) or broken (the clause hides it from the test that would catch it). A query whose organization is genuinely its ARGUMENT is the exception, not a safety copy — `UsersQuery` qualifies because `User` carries no global scope at all, so its `where` is the only boundary there is. `BookingsBoardQuery` was written that way and was corrected: `Booking` DOES carry `BookingScope`, so the argument was a second copy, and the board's tests said so — "these would pass with no scope at all". This scope applying nothing for a null principal is not a reason to add one back; those paths serve no response, and the principal that reaches a response without being constrained is platform staff, refused at the route ([`domains.md`](domains.md)).

The principal is resolved through App\Contracts\OrganizationPrincipal, implemented by User (organization_id) and Organization (itself, on the sanctum guard). Use `Auth::guard()->user()`, not `Auth::user()` — Larastan narrows the facade to the web guard's provider model and erases the Organization case. A null principal (console, queue, seeder) is UNCONSTRAINED on purpose; those paths serve no response.

Two audiences reach a LastLook differently and this is the whole difficulty: the broker is named on `broker_id`, an operator on no column at all. Scoping on the shared-looking column alone returns nothing for every operator — the bug that makes people delete the scope.

BidScope uses a RAW EXISTS against `last_looks`, never `whereHas('lastLook')`. whereHas applies the related model's scopes, and LastLookScope admits an operator to every look they qualify for — so the idiomatic-looking version returns every rival bid on that auction, which is exactly the AC14.4 disclosure the class exists to prevent. TripLegScope does the opposite (`whereHas('trip')`) deliberately: a leg's visibility IS its trip's, a bid's is not its LastLook's.

## An operator reaches a LastLook by qualifying, not by having been invited

SETTLED 2026-09-18 with product, replacing the rule that read "an operator only through `last_look_invitations`". Three branches admit, and one excludes over all of them:

| Branch                               | Admits                | For how long                       |
| ------------------------------------ | --------------------- | ---------------------------------- |
| `last_looks.broker_id`               | the desk that owns it | always                             |
| an EXISTS on `bids`                  | an operator who bid   | always, window shut and fleet gone |
| status `open` AND a qualifying fleet | any eligible operator | only while the window is open      |

`last_look_invitations` GRANTS NOTHING. It is the record of who was alerted at send — an audit of what happened, read by the broker's screens and by nothing that decides access. An operator alerted at send who has since stood the fleet down sees nothing; one who added a qualifying tail an hour into the window sees it and may bid.

ELIGIBILITY IS COMPUTED PER QUERY, against `last_looks.eligibility_category_id` — the requested category frozen onto the row at send. The alternative was three joins out through `trips` to the requested model, re-deriving on every read what the send already decided; the column is also what makes "what the trip asks for" immune to a later edit of the booking.

The conjunction is the same one `EligibleOperatorsQuery` sends the alerts on (AC2.1, AC4.4) — active tail, category at or above the requested rank in the same family, organization active, profile approved — asked of ONE organization rather than of all of them. Those two must not drift: an operator alerted but refused at the door is a support ticket, and the reverse is a disclosure.

THE EXCLUSION IS AN `AND` OVER THE WHOLE THING, never a fourth OR branch. `last_look_exclusions` is keyed on the `operators` REGISTRY row and matches on either that row's `organization_id` or the exclusion's own, so an incumbent who was not on the platform at send is still held out when they onboard mid-window. As an OR branch, an incumbent who had bid — or who was alerted before the booking named them — would read straight past it.

DISCOVERY IS LIVE LOOKS ONLY. Losing a qualifying tail loses sight of every open look not bid on, and a closed look is nobody's history but the bidder's.

A BID WHOSE OPERATOR HAS SINCE LOST ELIGIBILITY IS DISQUALIFIED, AND THIS IS WHERE. The bid keeps its ACCESS through the EXISTS branch above — that is what leaves it on the operator's own board — and loses its CLAIM, which is a different question and is asked of the two branches a bid does not survive: a qualifying fleet, and not being held out. Those two are `LastLookVisibility::eligible()`, one predicate with no second copy: the comparison screen selects it per bid and marks the row, `AwardLastLookAction` refuses such a bid under the lock, `EvaluateAutoAwardAction` skips it rather than closing the window on it, and `IneligibleBidsQuery` is the one read all of them ask. When the look then closes — by award or by rejecting every bid — the outcome is recorded: `BidOutcome::Disqualified` for those, `NotAwarded` for every other bid still pending.
