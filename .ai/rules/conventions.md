# How these rules are organised

One set of rules, one file per topic, here in `.ai/rules/` — the store Boost's
guideline files point at, that `boost:update` leaves alone, and that `record-rule`
writes into. Claude Code loads them through the committed symlink
`.claude/rules -> ../.ai/rules`: a file with `paths:` frontmatter loads when a
matching file is read, a file without one loads at launch. Every other tool
reaches them through the pointer in its guideline file: the rules with `paths:`
through `index.md`, which Boost regenerates from that frontmatter whenever
`record-rule` runs, and the rules without one through the list at the end of this
file, because `index.md` leaves them out — the prose lives here, `index.md` is
only the table.

A `paths:` rule loads only when Claude Code Reads a matching file in its OWN
checkout, so a reviewer or planner reading another checkout's code, or a Write
that creates a new file, must read `index.md` and open the matching rules by hand.
A file read through the shell (`cat`, `sed`, `grep`) loads nothing either, and
neither does a plan written before any file is opened. **Before creating a file
or planning a change, Read a sibling in the same folder** — that loads the
folder's rules — or open the rules `index.md` maps to the path.

**Where two shapes exist in the tree, the owning file says so, states the
current code, and marks the decision pending** — until it is decided, match the
code you are in and never introduce a third shape.

SETTLED, AND NO LONGER A QUESTION — do not reopen these:

- **Boundary types are `{Thing}Data` on laravel-data.** No `app/DTO/`, no `*DTO`
  class ([`services.md`](services.md)).
- **Every read lives in `Domains/{Audience}/Queries/`**, including the one built
  on spatie/laravel-query-builder (`Broker/Queries/BookingsBoardQuery`). No
  `QueryBuilders/` directory at any level, no `buildIndexQuery()`
  ([`domains.md`](domains.md)).
- **Public identifiers are `HasUlids` with a `public_id` column**, never
  `HasUuids` ([`models.md`](models.md)).
- **Money is a Brick `Money` cast** from minor units in the row's own currency
  ([`models.md`](models.md)).
- **Inertia forms are the starter kit's `<Form>` component**, never `useForm`
  ([`react-component-patterns.md`](react-component-patterns.md)).
- **A one-off message is `App\Support\Toast`** — `Toast::success(...)->back()`.
  `back()->with('error', ...)` renders nothing: nothing reads that key
  ([`controllers.md`](controllers.md)).
- **A write hands the Action a `{Noun}Data` built from `validated()`** — never
  `$request->safe()->string()` unless the whole input is ONE value. The older
  multi-value reads convert when their controller is next touched
  ([`http.md`](http.md)).

Still open, because both shapes really are in the tree:

| Topic | Current shape | The alternative | File |
| --- | --- | --- | --- |
| Controllers | seven resource actions only (owner's call) | the three custom verbs that predate the rule — `own`, `deactivate`, `ping` | [`http.md`](http.md) |
| Older migrations' `down()` | empty and forward-only from 2026-09-09 on | the 37 pre-rule files keep a real `down()` | [`migrations.md`](migrations.md) |

Before planning or editing, read every rule whose `paths:` cover the file you
are about to touch — Claude Code does this for you, and `index.md` lists them
for everyone else — then run `grep -rin '<keyword>' .ai/rules` for what a path
match alone misses. The rules with no `paths:` apply everywhere and are not in
`index.md`, so here they are:

- [centralize-shared-code.md](centralize-shared-code.md) — where shared PHP and React code goes
- [comment-and-ignore-policy.md](comment-and-ignore-policy.md) — no explanatory comments; ignores as last resort; minimal docblocks
- [git-commit-approval.md](git-commit-approval.md) — never commit or push without approval
- [pull-requests.md](pull-requests.md) — the Jira key goes in the PR title, and why the branch name is not enough
- [naming-conventions.md](naming-conventions.md) — casing, verbs, singular classes, traits, interfaces, tests, acronyms
- [rebase-over-merge.md](rebase-over-merge.md) — bring a branch up to date by rebasing; never merge develop into it
- [test-gate.md](test-gate.md) — the gate every change runs before it is finished, PHP or frontend
- [trust-the-layers.md](trust-the-layers.md) — no defensive doubling; let casts serialise; no trivial single-call helpers

To record a new convention, add or edit the file that owns the topic — by hand,
or with Boost's `record-rule` tool, which writes here and regenerates `index.md`.
Give an area rule `paths:` frontmatter so it loads only for matching files.
