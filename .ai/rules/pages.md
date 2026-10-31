---
paths:
  - 'resources/js/pages/**'
---

# Pages

## Never wrap a page in a layout — app.tsx assigns it by page name
A page component returns a bare fragment. Do NOT wrap it in `<AppLayout>` or `<AuthLayout>`.

`resources/js/app.tsx` assigns the layout from the page name: `marketing/*` gets none (every public marketing page lives in `pages/marketing/`, so a new one needs no list entry), `auth/*` and `claim/*` get AuthLayout, `settings/*` gets `[AppLayout, SettingsLayout]`, and everything else gets AppLayout. Wrapping the component as well renders two — two headers, and an empty gutter where the second sidebar would sit. It looks like a CSS bug and is not one.

A NEW TOP-LEVEL PAGE DIRECTORY NEEDS A CASE IN THAT SWITCH, or it silently falls to the default. `claim/` is unauthenticated — the claimant has no account, because creating one is the point — so without its case it rendered the signed-in application shell, nav and all, around a page nobody is signed in to.

Pass layout props through a static `Page.layout` instead: breadcrumbs for AppLayout (`InvitationsIndex.layout = () => ({ breadcrumbs: [...] })`), title and description for AuthLayout. A callback rather than an object when the value depends on props — `ClaimShow.layout` picks its title from the invitation's state, and `Organizations.layout` builds a different trail for a narrowed listing.

## A screen with a list and a detail page is a folder: `index.tsx` and `show.tsx`
`organizations/index.tsx` and `organizations/show.tsx`, and the same inside an audience folder:
`broker/last-looks/index.tsx` is the board and `broker/last-looks/show.tsx` one look. A screen with
only one page stays a single file (`broker/dashboard.tsx`, `broker/awarded.tsx`) — a folder holding
one `index.tsx` says nothing the file name did not.

`last-look.tsx` beside `last-looks.tsx` was the shape this replaced: the plural was the only thing
telling the board from the look. The component name is what `Inertia::render()` and
`OrganizationType::listLastLookComponent()`/`showLastLookComponent()` name, and it is also the page
name Google Analytics reports (`lib/google-analytics.ts`), so renaming a page restarts its history
there.
