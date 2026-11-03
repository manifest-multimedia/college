# Importing canteen qualification lists

Imports currently run from the terminal; there is no College admin upload screen yet.

## Prepare the files

Keep one reviewed, complete list per class in the import folder. Put archived versions in a different folder. The importer reads every `.xlsx` and `.xlsm` file in the selected folder, so do not mix unrelated workbooks or multiple versions of the same class.

Supported layout: class label in C1; student index followed by name in column C from row 2; `QUALIFIED` or `NOT QUALIFIED` in column D. Keep class labels consistent between corrections. Blank rows are allowed. Missing/invalid statuses, duplicate indexes, wrong institution prefixes, multiple files for the same class, and students appearing in multiple files reject the batch. No Excel macros are executed.

## Preview, then import

For the current PNMTC term:

```bash
devctl artisan college canteen:import-qualifications "$HOME/Downloads/PNMTC/Finance" --academic-year=2026/2027 --semester=1 --dry-run
```

Check each file's row counts, qualified/not-qualified totals, errors and replay status. The preview validates using a transaction and rolls back imported rows. Any Import IDs shown during a preview are provisional; use the IDs from the actual import for audit/revocation. Take a current database backup before the actual import (see below), then run the same command without `--dry-run`:

```bash
devctl artisan college canteen:import-qualifications "$HOME/Downloads/PNMTC/Finance" --academic-year=2026/2027 --semester=1
```

A successful table lists each batch's Import ID. Record these with the source files and confirmed period. Directory imports are atomic: an invalid file rolls back the whole directory import. Scan a known qualified and not-qualified student after import; a successful process exit is not a substitute for checking the expected outcome.

## Corrections and replays

- Re-importing an unchanged file for the same institution/period is a no-op; it does not duplicate statuses.
- Import a corrected **complete class list**, using the same class label and period. A changed file replaces that class's earlier active list, retaining the earlier version in audit history.
- A partial correction can remove eligibility for omitted students. Do not submit a small list of only changed names as a replacement class snapshot.
- Missing students in a replacement require review unless valid current-period financial evidence independently decides clearance.
- Replaying an old file does not restore approval after a correction or revocation. Use a reviewed corrected source, rather than editing database rows.

To withdraw an incorrect batch without erasing its history:

```bash
devctl artisan college canteen:revoke-list IMPORT_ID "Withdrawn pending correction"
```

Replace `IMPORT_ID` with the number shown by the import command. Revocation does not reactivate an older list. Re-importing the exact revoked file does not reactivate it either.

## A new semester or institution

Use that institution's approved values. PNMTC's current rule is 2026/2027, Semester 1, minimum financial payment 50%; it is not a universal default. To change the active canteen scope, after the institution confirms the new values:

```bash
devctl artisan college canteen:configure --academic-year=2026/2027 --semester=1 --payment-threshold=50
```

Then preview/import the lists with matching year and semester options. Importing a list does not automatically switch the active canteen period or change College-wide academic/billing dates. Previous-term lists do not carry forward. Student intake years in index numbers are not clearance periods.

## What a list proves

A qualification list records access status, not a payment or receipt. List-based results retain a null payment percentage. An active fee bill for the configured period takes priority; invalid financial evidence or an absent financial rule requires review and cannot silently fall back to a list approval. No bills, payments or bank transactions are created by these imports.

Keep spreadsheets, exports and database backups private: they contain student information.

## Local backup command

For this Fedora development setup only, save a private, uniquely named database dump before applying changes:

```bash
mkdir -p "$HOME/Backups/college"
chmod 700 "$HOME/Backups/college"
(
  umask 077
  podman exec local-dev-mysql sh -c 'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" mysqldump -uroot --single-transaction --no-tablespaces college' > "$HOME/Backups/college/college-before-canteen-$(date -u +%Y%m%dT%H%M%SZ).sql"
)
```

Check that the command succeeds and the dump is nonempty. Production installations must use their own approved database backup process. Revoking an incorrect qualification batch is usually preferable to restoring an entire database, which could overwrite unrelated newer work.
