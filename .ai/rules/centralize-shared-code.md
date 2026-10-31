---
trigger: always_on
description: Centralize shared logic and components to prevent code duplication
---

# Centralize Shared Code (REQUIRED)

When the same logic, pattern, or UI element appears in more than one place, extract it into a shared location. Three similar blocks of code is the threshold — deduplicate on the third occurrence.

## Backend (PHP)

- **Shared query logic** → Eloquent scopes, or a class under `app/Domains/Shared/Queries/`; once a screen needs declared filters and sorts, a spatie-backed query in the domain's own `Queries/` (there is no `QueryBuilders/`), with a sort or filter used by more than one screen as a class in `QuerySorts/` / `QueryFilters/` (see [`domains.md`](domains.md))
- **Shared business logic** → Action classes in `app/Actions/`
- **Shared validation** → Base FormRequest classes or custom Rule objects
- **Shared formatting/helpers** → `app/Support/` utility classes
- **Shared model behavior** → Traits in the model's namespace

## Frontend (React/TypeScript)

- **Shared UI patterns** → Components in `components/` (feature-scoped) or `components/ui/` (generic)
- **Shared data fetching** → Custom hooks in `hooks/`
- **Shared formatting/transforms** → Utility functions in `lib/`
- **Shared types** → Interfaces in `types/`
- **Shared constants** → Co-locate with the type or component that owns them

## Before Writing New Code

Check if a shared utility, component, or hook already exists for what you need. Search the codebase before creating something new. If a near-match exists, extend it rather than creating a parallel version.

```tsx
// ❌ Each component divides minor units and builds its own currency string
const price = `$${(bid.amount.minor / 100).toFixed(2)}`;

// ✅ Centralized in lib/money.ts, imported everywhere
import { formatMoney } from '@/lib/money';
```
