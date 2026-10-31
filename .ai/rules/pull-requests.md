# Pull Requests

## The Jira key goes in the TITLE, not only in the branch name

A pull request that has a ticket names it in the title — `LL-62: Add the broker bookings board` ✅, never the
GitHub default built from the branch name (`Feature/ll 61 booking domain models`) ❌. Where the title carries a
conventional-commit prefix, the key follows the prefix, matching the commit convention exactly:
`chore: LL-70 adopt the Laravel Rector sets`.

**A PR with no ticket carries no key, and none is invented.** Plenty of work here is real and ticketless. A
fabricated key is worse than an absent one: it points a reader at a ticket that describes something else.

### Why the title, when the branch already says it

**The release notes are generated from PR titles**: `.github/workflows/release.yml` publishes with
`gh release create --generate-notes`, which lists the titles of the pull requests in the range. A key in the
branch name reaches nothing there, so the title is the only place the ticket survives into the release record.
It also survives a branch rename and a squash-merge. A title is written, not defaulted.

## The title is a sentence, and the internal identifiers stay out

Past the key, the title says what the change does, in words a reader outside the work will understand — not
`bookings board stuff` and not a restatement of the branch name.

Spec slugs and other internal identifiers do NOT belong in a title or a commit message
([`core/task-reference-format.md`](core/task-reference-format.md)); the Jira key is the deliberate exception,
because it is the one identifier that means something to somebody who has never opened this repository.

## Related

- [`git-commit-approval.md`](git-commit-approval.md) — never commit or push without approval
- [`core/task-reference-format.md`](core/task-reference-format.md) — why spec slugs stay out of posted content
