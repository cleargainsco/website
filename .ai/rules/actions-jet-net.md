---
paths:
    - 'app/Actions/JetNet/**'
---

# Actions Jet Net

## Normalise in the derived operator table, never in the tail mirror

`jetnet_tails` stays verbatim — JETNET's own spelling, plus `raw_response` for everything not lifted into a column. Cleaning happens in `DeriveOperatorsAction`, which reads no API.

Why the split: normalisation of vendor data is a heuristic you will tune. `JetNetPhone` already rejects ~2% of numbers, and improving that reading costs one more run of `app:jetnet:derive-operators` off the local snapshot rather than a re-sweep of ~485 pages. Bake it into the mirror and you can no longer tell what JETNET actually said, and every fix is a re-fetch.

`jetnet_operators.office`/`mobile` therefore hold E.164, not the vendor string — the one place the derived table diverges from "as carried on the tail rows". Unparseable is null, never half-normalised: the column has one shape, and the raw value is still on the tail row it was aggregated from.

Normalise in `toRow()`, after the aggregate. `slotQuery()` picks one number per company with `max()`, so doing it earlier would mean pushing it into SQL.

## Derive, not Build or Sync — the verb names the dependency

`Sync*` actions FETCH from JETNET. `DeriveOperatorsAction` does not — its output is a pure function of `jetnet_tails`. Keep that distinction in every name a new step gets: `Sync` if it reaches outside, `Derive` if it reads a table this app already holds.

It was briefly `Build` (renamed back in Sep 2026). `Build` is a verb that would equally fit something that fetched, which is the exact confusion the naming is there to prevent. Not `Import` either (implies outside), and not `Rebuild` — it upserts and never prunes, so a company that has left every tail keeps its row.

The determinism is load-bearing, not cosmetic: same snapshot in, same rows out, so anything wrong in the output is fixed by running it again. That is what makes it safe to normalise vendor data here instead of in the mirror.

Names that follow from it: `app:jetnet:derive-operators`, `DeriveOperatorsJob`, `OperatorDerivationResult`/`Tally`, `jetnet_operators.derived_at`. Rename all of them together — the previous half-rename left `derived_at` behind and it read as a different concept.

Corollary to the verb rule: a `Sync` is named in the VENDOR's vocabulary — `SyncCompanyCertificationsAction`, after JETNET's company/certifications — because what it mirrors is the vendor's shape, not ours.

## `jetnet_operators` has a second writer: directory companies we name operators

The derivation's rows are still a pure function of the mirror, but the table is no longer only its output. A company on no tail — picked from JETNET's directory as an invitee or incumbent (Fetch Air) — is recorded by `SyncJetNetCompanyAction`, called from `ImportOperatorFromJetNetAction`, so its name and `altname` (the DBA) persist. Backfill: `app:jetnet:sync-directory-companies`.

Its role is `operator`, because we say it is one even though JETNET does not: no tails, not a charter operator, `derived_at` null — that null is what tells it from a derived row. If JETNET later lists it as only an OWNER, the derivation will call it one; approved tail claims are what make our word win.

Insert-only (`insertOrIgnore` on `companyid`), so a derived row is never overwritten. The derivation never deletes and resets only rows carrying tails or a charter flag, so a directory row survives untouched — and is upserted into an Operator or Owner, same id, the run that first finds it on a tail. A company already on a tail is never asked about: the next derivation writes it.

Only a deliberate action records one. Searching JETNET's directory never writes.

## Read the FAR Part 135 certificate off `name`, never off `cert_type`

Match `jetnet_company_certifications.name === 'FAR Part 135'` exactly. Verified live: company 12868 carries WYVERN Wingman, ARG/US Gold AND FAR Part 135, all three with `cert_type` reading "Operating Certification" — so cert_type picks one of three arbitrarily. IS-BAO gave one operator '090027', which is not a certificate at all.

Equality, never a prefix. Live data also carries FAR Part 129 (foreign air carrier), 137 (agricultural) and 145 (repair station); `FAR Part 1%` takes all of them.

Never fold, case-map or transliterate a number. Production values contain a typo and a Cyrillic homoglyph (`СDF/2012`, U+0421); normalising those onto their lookalikes silently joins two different operators. Membership rows (NATA, NBAA) arrive with a null `cert_nbr`, so filter those out rather than storing a company as certificated with no number.

## Certifications are replaced per company, and the registry column is never nulled

`SyncCompanyCertificationsAction` REPLACES a company's set (delete + insert in one transaction), it does not upsert. The endpoint returns the complete set, so only a delete can reflect a surrendered certificate; and `cert_nbr` is nullable, so Postgres would treat `(companyid, name, cert_nbr)` NULLs as distinct and append a duplicate every run. It fetches the whole answer BEFORE writing, so the "nobody holds anything" guard runs while the mirror is still intact — evaluated after a streaming write it would fire only once the damage was done.

`operators.certificate_number` is deliberately left unwritten. Resolving an incumbent from a certificate reads `jetnet_company_certifications` (cert_nbr -> companyid -> `ImportOperatorFromJetNetAction`), so a denormalised copy on the registry would be a second source nothing reads. Do not add a step to populate it without a reader that needs it.

Unlike `DeriveOperatorsAction`, the sweep does not refuse an empty charter set — the empty-tails guard is upstream, and an owner-only mirror is a real state.
