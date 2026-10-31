---
paths:
  - 'database/**'
---

# Database

## Fixture passwords are a constant; real Super Admins never get a known one
The password every seeded/factory account signs in with is `UserFactory::PASSWORD` ('password'). Read it from there — UserSeeder prints it at the end of a seed, so a literal written in a second place will eventually tell developers a password that does not work.

Do not make it configurable from .env. These accounts only exist in the lower environments (`config('app.lower_environments')`), a known constant is the point, and the auth tests post it literally — an env-driven value would make the suite pass or fail on one developer's .env.

On a deployed environment the first Super Admin comes from `php artisan super-admin:create` (after `db:seed --class=ReferenceDataSeeder --force` seeds the roles; plain `db:seed` throws there). It stores `Str::password(32)` and emails a reset link, exactly as CreateUserAction does: nobody, including whoever runs it, ever learns the password.

The one exception is `SUPER_ADMIN_PASSWORD`, which the command uses instead of the reset flow and REFUSES IN PRODUCTION. It exists for staging, which signs in at an address that receives no mail, so the link that is otherwise the account's only way in goes nowhere. It is validated against what production demands of every user (12 characters, mixed case, numbers, symbols), not `Password::default()`, which relaxes to eight outside production. Every Super Admin after the first belongs on the platform users screen, where an existing one authorizes it (SuperAdminProtected) and the grant is attributed.

## Money in factories and seeders is minor units
Write the COLUMN, not the cast attribute: `'incumbent_cost_minor' => 2_400_000` is $24,000 and goes in raw, because the `_minor` column carries no cast of its own. That is why the money casts could be added without rescaling a single fixture.

The cast attribute next to it — `incumbent_cost`, `price_to_beat`, `net_price` — takes MAJOR units and converts. Use it when a fixture reads better in dollars; use the column when it reads better in cents. Both are correct, they just mean different numbers, so check which one you are assigning before copying a value between files.

Reading the column gives a plain int; reading the attribute gives a Money. `$lastLook->price_to_beat_minor * 0.9` is fine in a factory. Derive with Money arithmetic where the currency matters: `$lastLook->price_to_beat->multipliedBy('0.9', RoundingMode::HalfUp)`.

Raw `DB::table()->insert()` bypasses casts entirely, so fixtures written that way — tests/Feature/Schema/DatabaseConstraintsTest.php — never see the guards. All three styles sit side by side in this directory.
