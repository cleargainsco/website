---
paths:
    - 'resources/**/*.tsx'
---

# Code Guidelines - Form Submit Enter Key

> React forms must properly handle Enter key submission using semantic HTML form elements and submit buttons.

## Purpose

Forms should work like standard HTML forms where pressing Enter submits the form. This ensures accessibility, keyboard navigation, and user expectations are met. Forms that rely solely on onClick handlers break this standard behavior and create accessibility issues.

## Severity Levels

- **ERROR** (❌) - Blocks validation, must fix
- **WARNING** (⚠️) - Should fix, doesn't block
- **INFO** (ℹ️) - Optional suggestion

## Checklist

### Form Structure

- [ ] **❌ Use semantic `<form>` element** - All forms must be wrapped in a `<form>` tag
    - **Why:** Semantic HTML provides accessibility hooks and Enter key behavior automatically
    - **Example:**
        - ✅ **Good:** `<form onSubmit={handleSubmit}><input /><button type="submit">Save</button></form>`
        - ❌ **Bad:** `<div><input /><button onClick={handleClick}>Save</button></div>`

- [ ] **❌ Include submit button with type="submit"** - Must have an explicit submit button
    - **Why:** The `type="submit"` button captures Enter key presses automatically
    - **Example:**
        - ✅ **Good:** `<button type="submit">Save</button>`
        - ❌ **Bad:** `<button>Save</button>` or `<div onClick={handleClick}>Save</div>`

### Event Handling

- [ ] **❌ Use onSubmit handler on form, not onClick on buttons** - Form submission logic goes on the form
    - **Why:** onSubmit is fired by both button click and Enter key press; onClick only fires on click
    - **Example:**
        - ✅ **Good:**
            ```tsx
            <form
                onSubmit={(e) => {
                    e.preventDefault();
                    handleSubmit();
                }}
            >
                <input type="text" />
                <button type="submit">Save</button>
            </form>
            ```
        - ❌ **Bad:**
            ```tsx
            <form>
                <input type="text" />
                <button onClick={() => handleSubmit()}>Save</button>
            </form>
            ```

- [ ] **❌ Call preventDefault() in onSubmit** - Must prevent default form submission if handling custom submission
    - **Why:** Prevents page reload and allows custom submission logic
    - **Example:**
        - ✅ **Good:** `onSubmit={(e) => { e.preventDefault(); submitForm(); }}`
        - ❌ **Bad:** `onSubmit={(e) => { submitForm(); }}` (causes page reload)

### Edge Cases

- [ ] **⚠️ Multiple submit buttons** - Use name/value attributes to distinguish which button was clicked
    - **Why:** Multiple buttons might have different actions; can be determined from e.target.submitter
    - **Example:**
        - ✅ **Good:**
            ```tsx
            <form onSubmit={handleSubmit}>
                <button type="submit" name="action" value="save">
                    Save
                </button>
                <button type="submit" name="action" value="publish">
                    Publish
                </button>
            </form>
            ```
        - ❌ **Bad:**
            ```tsx
            <form>
                <button onClick={() => save()}>Save</button>
                <button onClick={() => publish()}>Publish</button>
            </form>
            ```

- [ ] **⚠️ Controlled inputs** - Form state should not break Enter key behavior
    - **Why:** Controlled inputs can interfere with form submission if not properly handled
    - **Example:**
        - ✅ **Good:** onChange updates state, onSubmit reads current state
        - ❌ **Bad:** Preventing onChange from updating state or disabling input during submission

## Common Violations

| Issue                                        | Severity | Quick Fix                                     |
| -------------------------------------------- | -------- | --------------------------------------------- |
| Using `onClick` instead of `onSubmit`        | ❌       | Move logic to `onSubmit` on the form element  |
| Missing submit button                        | ❌       | Add `<button type="submit">` inside form      |
| Not calling `e.preventDefault()` in onSubmit | ❌       | Add preventDefault() to prevent page reload   |
| Using div instead of form                    | ❌       | Change to `<form>` and use `onSubmit` handler |
| Button type="button" instead of "submit"     | ⚠️       | Change to `type="submit"` to enable Enter key |

## Related Guidelines

- CLAUDE.md - React component best practices
- code-architecture-conventions.md - Form request validation patterns

---

**Last Updated:** 2026-01-13T21:42:00Z
