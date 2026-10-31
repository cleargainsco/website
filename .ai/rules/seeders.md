---
paths:
    - 'database/seeders/**'
---

# Seeders

## A deployment seeds ReferenceDataSeeder; plain db:seed throws outside local and testing

`db:seed` is a development command. DatabaseSeeder throws a RuntimeException outside `local` and `testing` rather than seeding a subset — a half-run seed leaves whoever ran it believing the database is seeded.

A deployed environment runs exactly one seeder, by name:

    php artisan migrate --force
    php artisan db:seed --class=ReferenceDataSeeder --force

New reference data (idempotent, credential-free, sourced from an enum or config) is added to `ReferenceDataSeeder::run()`, in the commit that introduces it, so it ships with its migration. Do NOT list individual seeders in the Laravel Cloud deploy configuration: a list held there is one no review sees and no test can run, and it drifts the first time someone edits only one of the two places.

Development fixtures (users, organizations, boards) stay behind DatabaseSeeder's lower-environments allowlist (`config('app.lower_environments')`) and are never reachable from a deploy.
