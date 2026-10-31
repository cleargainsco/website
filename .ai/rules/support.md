---
paths:
    - 'app/Support/**'
---

# Support

## Phone numbers are stored in E.164, read through App\Support\E164

E.164 is the only form this app stores a number in — it is what a `tel:` link, an SMS gateway and comparing two numbers all want. Anything accepting a number from outside (a vendor mirror, an onboarding form) goes through `App\Support\E164` on the way in. Never hand-roll it, and never store what the source sent.

Two entry points, because there are exactly two kinds of input and confusing them is the whole difficulty:

- `fromInternational()` — the number carries its own calling code (leading `+` optional).
- `fromNational($number, $region)` — written the way somebody inside `$region` would dial it.

Picking wrong is silent, not loud: `203-790-7800` read as international is a VALID Alexandria number, so nothing downstream catches it. A caller that does not know which it holds must decide — see `JetNetPhone` for a source that mixes both.

`$region` in `fromNational()` is a starting point, not a verdict: a numbering plan can span countries (`807-737-0039` is Ontario, reached through 'US'), so it parses with the region and then validates the result globally. Validating against the region handed in loses every Canadian and Caribbean number in a NANP feed.

Both return null rather than throwing, and validity is strict — a merely-POSSIBLE number formats into plausible E.164 that does not connect.

The package is propaganistas/laravel-phone; use its `Rule::phone()` for form validation.

## The dev login shortcut never names the login-link class

App\Support\DevLoginLinks builds the login page's `devLogins` prop (spread, never assigned, so a page where login links are off has no such key at all). spatie/laravel-login-link registers the endpoint that signs a user in with no credentials.

Two constraints, both enforced by tests/Architecture/DevLoginLinkTest.php:

- Production never allows login links. The package is a runtime dependency (moved out of require-dev on 2026-09-15 so staging could use it while the product is in development), which makes config/login-link.php the wall: `local` always, a deployed environment only when it sets `DEV_LOGIN_LINKS_ENABLED=true`, and `production` refused whatever the flag says. The host list adds `APP_URL`'s host so the deployed environment's own domain passes the package's host check.
- Nothing under app/ may reference `Spatie\LoginLink`. DevLoginLinks reaches the endpoint through the route name (`Route::has('loginLinkLogin')`) only, so the shortcut offers nothing rather than failing if the route is absent.

config/login-link.php sets `automatically_create_missing_users => false` (users.organization_id is not nullable and roles are scoped to an organization type, so a conjured user is a broken one) and `redirect_route_name => 'dashboard'`.

## A flashed message goes through App\Support\Toast

`Toast::success(...)->back()` / `->toRoute(...)` is the only way to flash a one-off message,
and `back()->with('error', ...)` renders nothing at all. The rule, and the bug that proved it,
are in [`controllers.md`](controllers.md).
