---
paths:
    - 'resources/js/**'
---

# React Component Patterns

Best practices for React components in this Inertia.js + React codebase.

## Rule

**React components MUST follow these patterns for maintainability, reusability, and testability.**

## Presentational Components (No API Logic)

Reusable components should NOT handle API calls. Use callback patterns instead.

```tsx
// ❌ Don't handle API calls in reusable components
export function ConfirmDialog({ route, method }) {
    const { delete: destroy, patch } = useForm()
    const handleConfirm = () => {
        const action = method === 'delete' ? destroy : patch
        action(route, { ... })
    }
}

// ✅ Use callback pattern - parent handles the action
export function ConfirmDialog({ onConfirm, isLoading }) {
    return (
        <AlertDialog>
            <AlertDialogAction onClick={onConfirm} disabled={isLoading}>
                Confirm
            </AlertDialogAction>
        </AlertDialog>
    )
}

// Parent component handles API logic
const { delete: destroy, processing } = useForm()
const handleArchive = () => {
    destroy(route('bookings.destroy', booking.uuid), {
        onSuccess: () => setOpen(false)
    })
}
```

## Minimal Component State

Use single state with conditional props instead of multiple states.

```tsx
// ❌ Don't create multiple states when one suffices
const [archiveDialogOpen, setArchiveDialogOpen] = useState(false)
const [restoreDialogOpen, setRestoreDialogOpen] = useState(false)

<ConfirmDialog open={archiveDialogOpen} ... />
<ConfirmDialog open={restoreDialogOpen} ... />

// ✅ Use single state with conditional props
const [dialogOpen, setDialogOpen] = useState(false)

<ConfirmDialog
    open={dialogOpen}
    title={isArchived ? 'Restore?' : 'Archive?'}
    onConfirm={isArchived ? handleRestore : handleArchive}
/>
```

## Component Reusability

Create generic components that work for any use case.

```tsx
// ❌ Don't couple components to specific use cases
export function ArchiveBookingDialog() { ... }
export function RestoreBookingDialog() { ... }

// ✅ Create generic components
export function ConfirmDialog({
    title,
    description,
    onConfirm,
    isLoading
}) { ... }
// Can be used for archive, restore, delete, or any confirmation
```

## Rationale

- **Presentational components:** Reusable anywhere (not just Inertia), easier to test
- **Minimal state:** Simpler logic, fewer bugs, impossible states prevented
- **Generic components:** Replace multiple similar components with one flexible solution

## React Compiler Note

This project has React Compiler enabled. Do NOT use `useMemo`, `useCallback`, or `React.memo` - the compiler handles memoization automatically. See CLAUDE.md for details.

## Related Rules

- `form-submit-enter-key.md` - Form submission patterns

## Forms use the `<Form>` component, not the `useForm` hook

SETTLED by the tree, and this section used to claim the opposite. `useForm` appears in
NO file here; `<Form>` is in 22, and every form written since has used it, so there is
nothing left to decide. `conventions.md` records the same thing.

WHAT THAT MEANS FOR A CONTROLLED FIELD. `<Form>` posts the DOM, so an input the browser
owns needs no state at all — which is most of them. A field whose value a library rewrites
as you type is the exception: `PhoneInput` reformats and owns the `+<code>` prefix, so it
takes `value`/`onChange` and a `useState` beside the form.

That posts the DISPLAY text rather than the canonical value, and the fix is not `useForm`
— it is the write boundary doing its job. `StoreOperatorInvitationRequest` E.164s the phone
in `prepareForValidation()`, so `+1 213 373 4253` is stored as `+12133734253` whatever the
field happened to render ([`normalize-on-write.md`](normalize-on-write.md)).

## A form with fields carries `noValidate`; the server is the only validator

Every `<Form>` that has something to type into takes `noValidate` — the plain HTML
attribute, which Inertia passes through to the real `<form>`. A form that is only a
submit button does not, because it has nothing to validate either way.

Server validation is not optional: it is the security boundary, so browser validation
can only ever be a SECOND, WEAKER copy of rules that already exist authoritatively.
Two validators is the defensive doubling [`trust-the-layers.md`](trust-the-layers.md)
forbids, wearing a UI.

THE FAILURE MODE IS SILENT, which is why this is blanket rather than per-form. A rule
of "use it where HTML cannot express the constraint" has to be re-judged every time a
rule changes — add `required_without_all` to a form that was simple, and the browser
quietly starts blocking on some other field's bubble while never showing the real
error. Nothing breaks; the form just gets worse. `StoreOperatorInvitationRequest` is
the live example: a company is named three ways of which exactly one may be present,
and no HTML attribute says that.

It also buys one error presentation. Without it a user meets an OS-styled bubble on one
field and an `InputError` on another, in the same form.

KEEP `required` AND `type="email"` ON THE INPUTS. Unenforced now, they still carry what
they are actually for — a screen reader announcing the field, and a mobile keyboard with
an `@` on it.

Where the round trip genuinely grates on a simple form, the answer is Precognition
(`useHttp` supports it), not browser validation: same server rules, validated live, one
source of truth.

## The compiler rule is already violated inside shadcn output — do not add to it

`useMemo` / `useCallback` / `React.memo` appear in 7 files here, almost all
inside `components/ui/*` (shadcn-generated) and the starter-kit hooks. Leave generated files alone; write nothing new with them.
