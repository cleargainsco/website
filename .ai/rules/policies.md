---
paths:
  - 'app/Policies/**'
---

# Policies

## One authorization layer per route — and which layer that is here

Do NOT stack authorization. A route is guarded either by
middleware or by a policy, never both; if middleware answers, the controller
does not also call `$this->authorize()`.

Which layer applies is a settled decision here, and it differs by surface:

- **Web routes → the policy, called from the controller.** There is no panel
  middleware to lean on: `organization.type:*` and the `platform.`/`broker.`/
  `operator.` route groups were removed on purpose (see [`routes.md`](routes.md)
  — "No organization type appears in any path"). So a web controller calls
  `$this->authorize()` first
  ([`controller-authorization-pattern.md`](controller-authorization-pattern.md))
  and the policy answers the permission question. Gate a new audience-specific
  route on the permission that implies the audience, never on the path.
- **API routes → middleware only, never the controller.** `auth:sanctum` +
  `api.organization:` + a per-route `abilities:` answer everything; a
  controller-side check was removed because it was unreachable
  ([`api.md`](api.md)).

`Gate::before` in `AuthServiceProvider` answers true for every ability a
**Super Admin or Platform Admin** is asked about — so policy methods return
`false` by default and grant only the named permission; platform staff never
reach them. That has a consequence the next section is about.

## Gate::before makes policies advisory for platform staff — put invariants elsewhere
AuthServiceProvider's `Gate::before` answers true for every ability a Super Admin **or Platform Admin** is asked about. Both roles therefore skip policies entirely, and they are the only people who reach the platform-side screens — so a rule written into a policy method there is skipped by everyone it was written for.

Permissions belong in policies. Domain invariants do not. `OrganizationPolicy::delete` answers only `organizations.manage`; "an organization with people on it cannot be deleted" lives in `DeleteOrganizationAction`, which every writer goes through, and on `Organization::hasPeople()`.

When a screen needs to show whether an action is possible, compute both halves explicitly — `$user->can('delete', $organization) && ! $organization->hasPeople()`. Asking the Gate alone returns true for platform staff regardless.

The same pattern already appears elsewhere: `UserController::deactivate` repeats its self-deactivation check, `CreateUserAction` repeats SuperAdminProtected, and `ImpersonationController::store` repeats two of UserPolicy::impersonate's rules. Test such a rule against a Super Admin and a Platform Admin specifically, or the test proves nothing.
