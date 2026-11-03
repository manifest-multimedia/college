# College Portal qualification-list module

Authorized staff open **Canteen qualification lists** in the College sidebar, or `/canteen/qualification-lists`.

The module is independent of Finance and AuthCentral. College System, Super Admin, Administrator and Finance Manager roles can manage lists. Other staff can be delegated College's `canteen.manage qualifications` permission. AuthCentral identity roles are not used for authorization.

## Routine workflow

1. Select the confirmed clearance year and semester and upload reviewed complete class lists (`.xlsx` or `.xlsm`). Limits: 8 files, 1 MB each, 5 MB combined, 20,000 rows per workbook. The current layout is C1 class label, column C index plus name, and column D QUALIFIED/NOT QUALIFIED.
2. Review the preview totals and replacement/omission warnings. No qualification rows are committed during preview. A preview is bound to its uploading user and institution and expires after 30 minutes. Changed files, changed lists or an intervening period change require another preview.
3. Confirm the review checkbox and import. Identical files do not duplicate records or reactivate withdrawn lists. A changed complete class list replaces earlier active lists for that class and period. Omitted students no longer retain an earlier approval.
4. The optional **Make this the active canteen period** checkbox changes the active period only when the reviewed import is confirmed. Otherwise future lists can be prepared without changing current access. The institution's financial threshold is unchanged.
5. Check history for batch ID, counts, file checksum, operator, time and status. Portal-uploaded original files are retained in private storage and can be downloaded only by authorized managers. Older terminal imports can lack a retained source file or named operator.
6. To withdraw an incorrect list, select **Withdraw list**, enter a reason, acknowledge and confirm. Audit history and the revoking operator are retained. An earlier list is not automatically reactivated.

Qualification lists do not create bills, payments, receipts or academic-period dates. Genuine financial evidence retains precedence. Source downloads and every write action are authenticated, permission checked and institution scoped. State changes use CSRF-protected POST requests. Review and confirmation do not depend on mobile or desktop JavaScript APIs.

## Deployment

Deploy migrations before serving the new application release, including `2026_11_03_000001_add_revoker_to_canteen_imports.php`. Keep the existing Laravel `storage` directory persistent across deployments; privately back up `storage/app/private/canteen/sources` and the database. A shared cache/lock backend is required for multi-node operation (database or Redis). Laravel's scheduler should run normally; it clears expired upload previews daily. Preview cleanup also runs when new files are uploaded.

No student spreadsheets or production credentials are included in Git. Existing local imports are not migrated to production automatically: upload the institution-approved production lists through this screen after deployment.
