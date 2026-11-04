# Student emergency contacts

Students may have no emergency contacts or multiple contacts. The feature is optional during admission and does not change existing students, guardians, academic records, or portal eligibility.

Each contact collects full name, relationship, mobile number, and optional alternate phone, email, GPS address, and residential address. Phone numbers are stored as text to preserve leading zeros and international prefixes.

Staff who can access student management (System, Super Admin, Administrator, Academic Officer, Finance Manager) can manage contacts on the student's details or edit page. Students can manage their own contacts under Student Information. Contact actions authorize the current user each time and scope contact IDs to the selected student. The linked user ID takes precedence over the legacy email fallback.

## Admission and imports

- The Create Student form supports optional, repeatable contacts. Once added, each contact requires a name, relationship, and mobile number; remove an unused form to admit without contacts.
- The import template includes two contacts. The mapping screen supports up to five contacts per row. Additional contacts can be added through the student record.
- DNSA headings such as Emergency Full Name / Emergency Name, Emergency Relationship, Emergency Mobile, Emergency Alt Phone, Emergency Email, Emergency GPS, and Emergency Residential are recognized automatically.
- Blank or unmapped contact columns do not delete existing contacts. Invalid, partially supplied contacts produce row validation errors before student creation or ID generation.
- Reimports update the same imported contact slots, retaining saved optional values when new cells are blank. Other contacts remain intact. Imported slots follow the numbered columns, so keep contact ordering consistent between files.
- Student and contact writes share the import row transaction. An account provisioning failure rolls them back together.
- Deleting a student cascades to their emergency contacts.

## Deployment

Deploy the code and run the normal database migration process (`php artisan migrate --force`) before serving the updated pages. The new migration only creates `student_emergency_contacts`; it does not update or backfill existing students. No contact data is required to continue using existing records.

## Verification

The focused tests use an isolated in-memory SQLite database and do not touch institution data:

```sh
vendor/bin/phpunit tests/Feature/StudentEmergencyContactsTest.php tests/Unit/StudentImportDateParsingTest.php tests/Feature/StudentImportDateDetectionTest.php
```
