# Bring a branch up to date by rebasing, not by merging

A feature branch takes on new work from the integration branch with a **rebase**:

```bash
git fetch origin
git rebase origin/develop   # ✅
git merge origin/develop    # ❌ on your own feature branch
```

**Never merge `develop` into your own branch.** The merge commit it creates says
nothing a reader will ever want, and it permanently interleaves someone else's
commits with yours in the branch's own history.

## Why

A sync-merge records *when* somebody happened to sync, never a decision, and costs three things:

1. **`git log` on the branch stops being the branch**: `git log origin/develop..HEAD`
   returns your commits plus a merge, so "what does this branch change" needs a
   diff against the merge base instead of a list.
2. **The second sync conflicts against the first.** A rebase resolves each
   conflict once, in the commit that owns it; a merge resolves it *in the merge
   commit*, so the next sync re-derives it — and `rerere` is deliberately
   **`false` in every lane here**.
3. **Review reads the wrong diff**: the pull request shows files the author never touched.

A rebased branch is a clean list of your own commits on top of current develop —
what a reviewer wants to read, and what the release notes are generated from.

## When NOT to rebase

- **Somebody else has the branch checked out.** Rebasing rewrites every commit
  id on it. Ask first, or merge.
- **The branch is the integration branch.** `develop` and `main` are never
  rebased — they only receive merges, from pull requests.
- **A reviewer is mid-review on a pushed branch.** Rewriting under them discards
  GitHub's "changes since you last looked" view. Finish the review round, then rebase.
- **The branch holds walkthrough takes recorded but not yet published.** Do not
  rebase, amend or reset it until they are published: publish refuses a take whose
  commit is not in the branch's history, so a rewrite means recording again. Once
  published, rebasing is safe. `walkthroughs adopt` is a one-off repair, never the default.

In those cases a merge is the correct tool. What this rule forbids is the *routine* sync-by-merge.

## Updating an already-pushed branch

A rebased branch that has been pushed needs a lease-checked overwrite of its
remote ref: `--force-with-lease`, never the bare form, because the lease refuses
if the remote moved since your last fetch — exactly the case where the bare form
destroys somebody else's commits.

**An agent CAN run the lease form.** The guard exempts `--force-with-lease`,
`--force-with-lease=<ref>`, `--force-with-lease=<ref>:<expect>`,
`--force-if-includes` and the `--no-` negations by name
([`essential/destructive-commands.md`](essential/destructive-commands.md)). The
push still needs approval like any other
([`git-commit-approval.md`](git-commit-approval.md)), so ask for it before you
rebase rather than rebasing and stopping: a branch rewritten locally with a stale
remote ref leaves a stacked pull request conflicting with its base, and such a
PR gets **no CI run at all**, silently.

**The bare `--force` IS blocked**, in hard mode, with no approval path; the owner
runs it. So is a bare `--force` written *beside* a lease, since it overrides the
lease. The guard matches the command string whether or not it would run, so keep
that form out of a heredoc, a script body or a brief — put such text in a file
with an editor tool rather than piping it through a shell.

**Push a stack base-first, once the push is approved.** Rebase each branch onto
its own updated base and lease-push it before moving up, so every pull request in
the stack gets a CI run against a base it actually merges onto.

## If a rebase goes wrong

`git rebase --abort` returns the branch exactly as it was, at any point until the
rebase finishes — which is why a rebase is worth attempting before reaching for a merge.

## Related

- [`git-commit-approval.md`](git-commit-approval.md) — never commit or push without approval
- [`pull-requests.md`](pull-requests.md) — the Jira key goes in the PR title
- [`essential/destructive-commands.md`](essential/destructive-commands.md) — which overwrite forms are blocked, and which the lease exempts
