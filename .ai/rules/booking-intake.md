---
paths:
  - 'app/Domains/Broker/Pipelines/BookingIntake/**'
---

# Booking Intake

## The incumbent operator is a fact, not a preference
A booking's incumbent is who the broker has a contract with for that trip. Never gate resolving it on `OperatorStatus` — archived, cancelled or otherwise unwanted is OUR bookkeeping, and refusing the push or nulling `incumbent_operator_id` because of it discards true information to enforce a preference of ours.

This was tried and reverted: a guard was added to both `ResolveTail` and `ResolveIncumbentByCertificate` on the strength of `OperatorStatus::Archived` reading "Never resolved onto a new booking again". That sentence conflated two things and has been reworded — it governs recruitment and candidacy, not what a booking records.

The duplicate half of `Archived` is still a real problem, and it is a DATA problem: an exclusion recorded against the archived copy leaves the operator that actually flies the trip free to be invited to bid against it (FR5). Merge the rows; do not refuse the booking.

The legitimate null case is different and stays: a tail JETNET carries with no company at all leaves `incumbent_operator_id` null for the AE to reconcile (ADR 0008).
