# Canteen prefix review — 20 October 2026

## Resolved

`STUDENT_ID_INSTITUTION_PREFIX` already existed as a branding configuration environment fallback. The newly added canteen import command unnecessarily directed administrators to set it and duplicated the generator's prefix-resolution code in both the command and seeder.

The institution prefix is now administered in the existing college Student ID settings. General Settings reads and saves that same setting. The older `settings.school_name_prefix` remains a backward-compatible fallback. `StudentIdGenerationService::configuredInstitutionPrefix()` resolves these sources; generation, reassignment, the legacy ID helper, configuration reporting, and canteen imports use the shared service. Import authorization does not use the generator's inferred institution-name fallback, and an unrelated prefix such as `PNMTCOTHER/DA` no longer passes the PNMTC guard.

The local deployment previously had neither a saved branding prefix nor a legacy school prefix. Its effective `PNMTC/DA` value was preserved through the existing BrandingSettingsService before the prefix entry was removed from `.env`. A fresh application boot confirms it still resolves to `PNMTC/DA`. No live finance import, migration, student ID reassignment, or financial-record update was run.

The string `STUDENT_ID_INSTITUTION_PREFIX` remains the existing internal input key used by the branding settings service/controller; it is no longer read from the environment. The finance-directory environment option is separate and remains supported through `config/canteen.php`, so command/seeder code uses Laravel configuration rather than calling `env()` at runtime. See [Laravel configuration caching](https://laravel.com/docs/12.x/configuration#configuration-caching).

For other deployments, save their actual institution prefix in Student ID settings before removing an old environment entry or deploying this change. Existing school-prefix records remain supported without an environment entry.

## Validation

- 34 targeted PHPUnit tests passed, with 145 assertions: prefix settings, canteen command/seeder/import, canteen API, branding persistence, and student imports/ID generation.
- Tests ran in a newly created isolated MySQL database, separate from the college database, and the isolated database was removed afterward.
- PHP syntax checks passed for all 13 changed PHP files.
- Pint passed for the nine core/config/test files. Four auxiliary legacy files still report existing formatting conventions; broad formatting changes were avoided.
- PHPUnit reports four existing deprecations from the student import tests.
- Fresh application boot verified the saved prefix and absence of its `.env` entry.
- No Android code changed or APK rebuilt for this correction.

## Separate findings requiring follow-up

The importer/clearance model described below was subsequently refactored; see [Canteen clearance evidence](CANTEEN_CLEARANCE_MODEL.md). Existing local finance rows are testing data and were not treated as production incidents or repaired.

1. **Finance data integrity:** `PnmtrFinanceImport::createBill()` creates bills with an assumed total of 1,000 and assigns payment percentages of 100 or 40 from QUALIFIED/NOT QUALIFIED labels. These are synthetic balances and amounts, not actual payment figures imported from finance. This behavior was reviewed but not changed or executed against live data. It needs an agreed data model before using this importer to establish real financial balances.
2. **Mobile integration remains incomplete:** the mobile workspace still selects MockAuthProvider in demo mode; LaravelAuthProvider.login() throws an unconfigured-authentication error, and production builds remain blocked. The college API tests pass, but this does not establish an integrated mobile-to-college workflow.

This was a focused prefix/configuration consistency review, not a complete security or financial-policy audit of the new API.
