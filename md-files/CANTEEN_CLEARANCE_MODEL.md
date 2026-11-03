# Canteen clearance evidence

Qualification and financial payment are different kinds of evidence. A QUALIFIED entry authorizes canteen access for its confirmed period; it does not establish a bill, payment percentage, balance, or receipt. The import never creates or changes financial tables, or infers dates/current-year flags from a student's intake index.

## Decision order

1. Unknown students produce `not_found`. Inactive students, mismatched institution prefixes and ambiguous/unconfigured periods require review.
2. An active fee bill for the exact configured academic year and semester is preferred. Compare its actual `amount_paid / total_amount` against the institution-approved minimum. Do not use a manufactured/stale cached percentage. Invalid amounts, ambiguous financial periods or a missing payment rule require review; they do not fall back to list approval. Threshold comparison uses the unrounded ratio.
3. Without financial evidence for that period, use the latest valid qualification evidence. QUALIFIED maps to `approved`; NOT QUALIFIED maps to `denied`. `payment_percentage` remains null in both cases.
4. Missing, stale or revoked list evidence requires review.

Online verification, paged rosters and offline reconciliation share this resolver. Offline captures retain the decision shown at capture and separately record the server's reconciled decision and its evidence. Online logs preserve their original evidence context on idempotent replay.

## Institution and period settings

There is no universal 50% default. Settings are stored in the existing `system_settings` table under keys scoped to a hash of the configured institution prefix, so switching institution branding cannot adopt another institution's policy. Prefixes come from the existing college Student ID settings. This supports 360 College's current deployments with separate databases; it is not a claim of shared-database tenant isolation.

For PNMTC, the institution confirmed 2026/2027, First Semester, and payment eligibility from 50% through 100%. Save those approved values explicitly:

```sh
php artisan canteen:configure --academic-year=2026/2027 --semester=1 --payment-threshold=50
```

The explicit canteen period does not change school-wide exam or billing settings. If no explicit canteen period is stored, the resolver uses only a uniquely current academic year and semester. It does not guess the first semester. Advance the canteen period when the institution changes semesters; earlier lists do not carry forward. A list can be imported before billing-period setup exists, without inventing academic-year start or end dates.

## Import and audit

Run the new migration through the normal deployment process before using these APIs. Production deployment and configuration were not performed by this refactor.

```sh
php artisan migrate
php artisan canteen:import-qualifications "/path/to/Finance" --academic-year=2026/2027 --semester=1
```

The current spreadsheet adapter reads the class label from C1, student index/name from C, and QUALIFIED/NOT QUALIFIED from D. It supports the provided `.xlsm`/`.xlsx` layout for any configured institution prefix. Other spreadsheet layouts need an explicit adapter, rather than guessed column mappings. `canteen:import-pnmtr` remains a compatibility alias; PnmtrFinanceImport delegates to the same non-financial importer. The compatibility PnmtrCanteenSeeder requires explicit `canteen.import_academic_year` and `canteen.import_semester` configuration and never infers a term from enrollment years.

`canteen_qualification_imports` stores prefix, confirmed year/semester, original file name, SHA-256, class label, operator (when supplied), import time and revocation information. `canteen_qualifications` stores the student, original index, source row, source text/status, and parsed fields. There are deliberately no money or percentage fields in this model.

A changed file for the same class and period replaces that class snapshot. Earlier imports remain in the audit history but are revoked. Students omitted from a replacement require review. Replaying an identical file is idempotent and cannot restore old approval. Multiple files for the same class, duplicate indexes, unknown statuses and wrong-institution rows reject the import. Directory imports are atomic: no partial students or statuses survive an invalid file.

```sh
php artisan canteen:revoke-list IMPORT_ID "Reason for withdrawal"
```

Revoking the latest list does not silently reactivate an older approval. Financial evidence remains independently authoritative.

## API and client responsibilities

Verification and roster clearance include `decision_context.source` (`qualification_list`, `financial_records`, or null), scope and evidence identifiers. List decisions have a null payment percentage. The client must not display list approval as "fully paid" or compute clearance itself.

Rosters return `sync_mode: full_snapshot` and cursor pages. The existing `since` parameter is accepted but does not filter by Student.updated_at: imports, policy edits, revocations and payments can change clearance without changing a student record. Clients must fetch all pages and replace their cached snapshot; transport errors must not reuse an incomplete snapshot as current evidence. Broader offline expiry and permissions remain deployment decisions, not guarantees added by this refactor.

The Phase 4 Smart Canteen adapter now uses College login, session validation, token-specific logout and online verification. The local PNMTC policy was explicitly configured for 2026/2027, Semester 1, 50% on 3 November 2026. This is not a production deployment or a claim of shared-database tenant isolation. Native device acceptance and a new APK remain pending.

## Validation

Regression tests cover non-financial imports, exact-threshold decisions, source precedence, institution isolation, term scoping, invalid financial evidence, replacements, omission/revocation, idempotency, directory rollback, roster metadata and offline reconciliation. Tests use an isolated temporary database. Existing local dummy finance records were left alone; no production database was accessed.

Observed validation on 20 October 2026:

- 57 targeted tests passed, 194 assertions. Four pre-existing PHPUnit deprecations remain in student-import tests.
- Pint and Git whitespace checks passed for this refactor.
- The four provided Finance workbooks imported in the isolated database: 615 student/qualification records, 321 approved and 294 denied, all with null payment percentages.
- Bills, bill items, payments, fee types, fee structures and academic years created by that import: zero in every case.
- Replaying all four source files was idempotent and retained 615 qualification records.
- The isolated test database was removed after validation. No application database migration, production configuration or production import was performed.

## Local import completed — 3 November 2026

The four restored PNMTC Finance workbooks were imported into the local College database for 2026/2027, Semester 1: 615 statuses, 321 qualified and 294 not qualified. Active import IDs are 5–8. All 615 resolved from qualification-list evidence with null payment percentages. Existing financial, academic-period and account data was unchanged; identical replay was verified as a no-op. A private database snapshot was saved before import. This does not change production.

The command supports `--dry-run` for validation with rollback of imported rows. Preview batch IDs are provisional. See CANTEEN_IMPORT_OPERATIONS.md for future imports, corrections, revocation and backups.
