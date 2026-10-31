---
paths:
    - 'app/Http/Controllers/**'
---

# Route Scope Binding Authorization

## Overview

When routes use Laravel's `->scopeBindings()`, relationship authorization is handled automatically by the framework. Controllers should NOT add redundant manual authorization checks.

## Rule

**Do NOT add manual `abort_if()` checks for relationship ownership when `->scopeBindings()` is applied to the route group.**

Laravel's scope binding automatically ensures that child models belong to their parent models through defined Eloquent relationships. If the relationship doesn't exist, Laravel returns a 404 automatically.

## Rationale

- Reduces code duplication
- Keeps controllers focused on business logic
- Leverages Laravel's built-in security features
- Maintains consistency across the codebase

## Examples

### Route Definition with Scope Binding

```php
// routes/web.php or routes/itinerary.php
Route::prefix('bookings/{booking:uuid}/legs/{tripLeg}')
    ->scopeBindings()  // ← This handles authorization automatically
    ->group(function () {
        Route::patch('services/{type}', [ServiceController::class, 'update']);
    });
```

### Bad Example - Redundant Authorization

```php
// ❌ DON'T DO THIS - The check is redundant when scopeBindings() is used
public function update(Booking $booking, TripLeg $tripLeg): RedirectResponse
{
    $tripLeg->loadMissing('trip');
    abort_if($tripLeg->trip->booking_id !== $booking->id, 403);  // Redundant!

    // ... business logic
}
```

### Good Example - Trust Scope Binding

```php
// ✅ DO THIS - Trust that scopeBindings() has already validated the relationship
public function update(Booking $booking, TripLeg $tripLeg): RedirectResponse
{
    // scopeBindings() already ensures $tripLeg belongs to $booking
    // via the Booking::tripLegs() relationship

    // ... business logic
}
```

## How Scope Binding Works

1. Route defines nested parameters: `/{booking:uuid}/legs/{tripLeg}`
2. `->scopeBindings()` is applied to the route group
3. Laravel looks for a relationship on `Booking` that returns `TripLeg` models
4. Found: `Booking::tripLegs()` (via `hasManyThrough`)
5. Laravel automatically scopes the query: `WHERE trip_leg.id = ? AND trip.booking_id = ?`
6. If no match found, Laravel returns 404 before the controller is called

## Required Model Relationships

For scope binding to work, the parent model must have a relationship to the child model:

```php
// app/Models/Booking.php
public function tripLegs(): HasManyThrough
{
    return $this->hasManyThrough(TripLeg::class, Trip::class);
}
```

Supported relationship types:

- `hasMany`
- `hasOne`
- `hasManyThrough`
- `hasOneThrough`
- `belongsToMany`
- `morphMany`
- `morphOne`

## When Manual Checks ARE Needed

Manual authorization is only needed when:

1. **Route doesn't use `scopeBindings()`** - Some legacy routes may not have it
2. **No direct relationship exists** - The models aren't connected via Eloquent relationships
3. **Complex authorization logic** - Beyond simple ownership (e.g., role-based access)
4. **Cross-resource access** - Checking access to a resource not in the URL path

## Verification

To verify a route uses scope binding, check the route definition:

```bash
php artisan route:list --path=bookings
```

Or check the route file for `->scopeBindings()` on the parent group.

## Related Rules

- `controller-authorization-pattern.md` - Authorization check patterns
- [`policies.md`](policies.md) — why a domain invariant does not belong in a policy
- `permission-naming-convention.md` - Permission naming conventions
