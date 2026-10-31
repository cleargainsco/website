---
paths:
  - 'app/Http/Requests/**'
---

# Requests

## Prefer FormRequest::authorize() for writes; the controller is right for reads
Where a route takes a FormRequest, prefer putting the policy check in `authorize()`. Keeping `$this->authorize()` in the controller as well is fine and often clearer — they call the same policy method, so there is one definition and nothing to drift. This is a preference, not a mandate.

Why the request is the better home for a write: Laravel resolves `authorize()` before `rules()`, whereas a controller's `$this->authorize()` runs after the whole FormRequest has resolved. With the check only in the controller, somebody with no business on the endpoint gets a 422 listing every field name before anything asks whether they may be there — handing out the shape of a form to a caller who should learn only "not for you". On this platform, which companies exist and which are being recruited is confidential (AC14.4).

THE CONTROLLER IS THE RIGHT PLACE, NOT A FALLBACK, for anything with no request body: `viewAny` on an index, `view` on a record, a destroy, a resend. There is nothing earlier to hook and nothing to leak, and `$this->authorize('viewAny', OperatorInvitation::class)` at the top of the action is the clearest statement of who may open the screen.

Ask the POLICY, not the permission: `$this->user()?->can('create', OperatorInvitation::class) ?? false`. One definition of who may act, in the policy, per .ai/rules/policies.md.

AN ACTION NEVER AUTHORIZES, AND THAT IS NOT THE SAME AS "AN ACTION NEVER REFUSES". Actions are called by controllers, console commands, jobs and seeders, most of which have no actor at all, so an authorization check there is either dead code or a second answer that can disagree with the policy. What an action DOES enforce is the domain invariant — whether the thing is possible, not whether this person may ask. `DeleteOrganizationAction` refuses an organization that still has people; `IssueOperatorInvitationAction` refuses a company that already has a live invitation. Those belong in the action precisely because `Gate::before` short circuits policies for platform staff, so a policy clause would be skipped by everyone it was written for (.ai/rules/policies.md). Rule of thumb: WHO may act is the request's or controller's question; WHETHER it is possible is the action's.

Prove the ordering with a test that posts an EMPTY body as an unauthorized user and asserts 403 with no validation errors. A test that posts a valid payload passes either way and proves nothing.

`OperatorInvitationController` authorizes in both places for its two writes and in the controller alone for its reads. `OrganizationController` and `UserController` authorize in the controller only, so an unauthorized caller still gets a 422 from them; adding the request half is a separate change.

## Messages and labels name the control, not the payload key

Give every field a person reads an `attributes()` name, and a `messages()` entry where the default still reads wrong:
"The legs.0.origin_aid field is required" names an array index and a column the sheet never shows. Use the AE's word
for the thing ("operator cost"), not the column's ("incumbent cost") — `BookingFormRequest` is the example.
