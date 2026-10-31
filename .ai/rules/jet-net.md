---
paths:
  - 'app/Services/JetNet/**'
---

# Jet Net

## JETNET phone numbers carry a calling code only outside North America
JETNET writes contact numbers as digits and hyphens with no `+`, and splits on the NANP: a North American number carries NO calling code (`713-458-5200`), everything else carries it inline (`49-2203-908-4974`, `39-06-49-861`). Read them with `JetNetPhone::toE164()`, never by hand.

Two traps, both silent:
- Passing `comp1country`/`comp2country` to libphonenumber as a REGION HINT double-prefixes the code — `39-06-49-861` + region IT becomes `+39390649861`. The country column is a switch between the two schemes, never a parse region.
- Prefixing `+` to everything mis-assigns bare NANP numbers to countries where they are also VALID: `203-790-7800` becomes an Alexandria number, `516-225-6109` a Peruvian one. Measured: 94 US numbers on the live snapshot.

The country column is the COMPANY's, not the number's, and the two disagree on real rows (a German-registered company on a Swiss `41-...` number). The calling code in the string wins.

`JetNetPhone` holds only that decision — which of the two schemes a value is in. The reading itself is `App\Support\E164`, and JETNET-specific handling belongs here rather than there: `E164` answers what it is asked and never guesses. ~2% of the snapshot fails validation, mostly Brazilian mobiles missing the ninth digit added in 2016; those store as null.

## The sweep's memory bound counts rows, not pages — and a kernel kill records nothing
`jetnet.sweep.fetch_rows` (default 1000) bounds how many ROWS a fetch round may hold. `PagedSweeper` keeps every page of a round decoded in memory at once, so the cost is `pages × page_size` rows; pages per round is derived from the budget, and a round smaller than `concurrency` is allowed on purpose — the memory bound wins, throughput gives way.

It used to be `fetch_batch`, a page count, and the two knobs MULTIPLIED. Raising `page_size` (which reads as a wall-clock/throttle knob) silently raised the memory bound with it. Measured on a 512MB Laravel Cloud container: `page_size=250` × a 10-page round = 2,500 rows, OOM-killed at the second round boundary.

WHAT A KERNEL KILL LOOKS LIKE, because it cost an evening to identify: no output at all — a container OOM kill is not a PHP error, so nothing prints and no shutdown handler runs. The `console_command_runs` row stays `running`; Horizon shows the job neither active nor failed; and `retry_after` later surfaces `MaxAttemptsExceededException`, which names the queue's bookkeeping and says nothing about the cause. Silence plus a stuck `running` row is the fingerprint — a PHP fatal would have printed. `app:close-abandoned-runs` (hourly) is what closes those rows.

## A lookup whose empty answer is meaningful must call `JetNetEnvelope::isError()`
`classify()` only recovers TOKEN-shaped `Error:` bodies; every other error (company not found, lapsed subscription) classifies as `Fetched`, and `rows()` then answers `[]` because JETNET sends null for an empty section. That is fine for the reference syncs, where `SyncsReferenceTable::mirror()` refuses an empty result anyway — and wrong for any call whose empty answer is recorded as a fact. `certificationsForCompanies()` guards with `isError()` and throws for exactly that reason.

Certifications are read off `getCompanyListPaged` rather than `getCompanyCertifications/{id}`: `CompListOptions.complist` takes an array of company ids, so 100 companies arrive in one request (~1.2s) instead of 100 requests at ~1.05s each. Both carry the identical block — verified field for field. The chunk is sized to the page, so a `maxpages > 1` answer means an assumption broke and is refused rather than half-read.

There is no reverse lookup. No JETNET endpoint or filter answers "which company holds this certificate", which is why the numbers are mirrored locally at all.
