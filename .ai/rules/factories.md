---
paths:
    - 'database/factories/**'
---

# Factories

## Derive denormalised and paired columns in factories; never fake them side by side

`trip_legs.departs_at` is a UTC instant and `departure_date_local` is the calendar day AT THE ORIGIN. For any evening departure west of Greenwich they name different days (22:00 on the 12th at KTEB is 02:00Z on the 13th). Faking both independently breaks the invariant for most US evening departures, and AC6.3 — "the window closes at least 8h before a departure whose time is known, and a date-only leg must not have passed" — is evaluated against them, so the auction's central guard gets tested against data already wrong. TripLegFactory derives the date from the instant through `airports.timezone`.

Derive it in the DEFINITION closure, not in a state: `origin_airport_id` may still be an unresolved Factory when a state runs, and Laravel resolves definition attributes in array order.

Same rule for denormalised keys: LastLookFactory takes `broker_id` from its trip's BOOKING, reaching through `trip_id` — `trips` carries no broker column of its own (LL-61 removed it, because a copy of the booking's broker is a second truth that can disagree with it). A LastLook whose broker_id differs from its booking's is visible to one company and about another's trip. TripFactory used to derive the same column and no longer sets one at all.

Reference data is RESOLVED, never fabricated. `AircraftCategory::for($enum)` is firstOrCreate keyed on the slug; a category factory would collide on `unique(slug)` and `unique(family, rank)` the second time two tests each asked for "a category".

Do not name a factory method `sequence()` — that is Laravel's own variadic Factory::sequence(). TripLegFactory uses `atSequence()`.
