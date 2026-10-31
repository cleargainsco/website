# Git Commit and Push Approval Rule

## Summary

**Never commit or push code without explicit user approval.**

## Rule

Before running `git commit` or `git push`:

1. **Show the user what will be committed** — list the changed files and summarize the changes
2. **Wait for explicit approval** — do not proceed until the user confirms
3. **Respect "do not commit" instructions** — if the user says not to commit/push, do not

✅ "Here are the changes ready to commit: … Ready to commit and push?" — then commit on "yes".
❌ "Fixed the issue." followed by an immediate commit and push.

## Exception

The only exception is when the user explicitly says something like "fix it and push", "commit and push when
done" or "go ahead and commit".

## Applies To

All git operations (`commit`, `push`, `commit --amend`, `push --force`), all branches, all repositories in this
workspace.

## Commit shape

One commit per finished piece of work on a branch, not one per file or per fix. Its body lists each decision and why,
one bullet each. A second commit only when something must stay separately droppable.
