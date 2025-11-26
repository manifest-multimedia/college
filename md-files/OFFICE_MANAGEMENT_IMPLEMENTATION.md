# Office Management Implementation

## Overview
Extended the Department module to include Offices as a hierarchical layer between Departments and Assets. This allows better organization of assets by physical office location within departments.

## Database Structure

### Offices Table
- **id**: Primary key
- **department_id**: Foreign key to departments table (cascade on delete)
- **name**: Office name
- **code**: Unique office code (nullable)
- **location**: Physical location
- **phone**: Contact phone
- **email**: Office email
- **description**: Office description
- **is_active**: Active status (boolean, default true)
- **timestamps**: Created at, Updated at

### Assets Table (Extended)
- **office_id**: Foreign key to offices table (nullable, set null on delete)
  - Added after department_id column
  - Nullable for backward compatibility with existing assets

## Models & Relationships

### Office Model (`app/Models/Office.php`)
**Fillable Attributes:**
- department_id, name, code, location, phone, email, description, is_active

**Relationships:**
- `department()` - belongsTo Department
- `assets()` - hasMany Asset

**Scopes:**
- `active()` - Filter only active offices

### Department Model (Updated)
**New Relationship:**
- `offices()` - hasMany Office

### Asset Model (Updated)
**Fillable Attributes:**
- Added `office_id` to fillable array

**New Relationship:**
- `office()` - belongsTo Office

## Livewire Component

### OfficeManager (`app/Livewire/OfficeManager.php`)
**Features:**
- Full CRUD operations (Create, Read, Update, Delete)
- Search functionality (name, code, location)
- Filter by department
- Filter by status (active/inactive)
- Pagination (10 per page)
- Toggle office status
- Delete protection (prevents deletion if office has assets)

**Public Properties:**
- Form fields: department_id, name, code, location, phone, email, description, is_active
- Filters: search, filterDepartment, filterStatus
- Modal state: showModal, editMode

**Methods:**
- `openCreateModal()` - Open modal for new office
- `openEditModal($officeId)` - Open modal with office data
- `save()` - Create or update office
- `toggleStatus($officeId)` - Toggle active/inactive
- `delete($officeId)` - Delete office (with asset check)
- `closeModal()` - Close modal and reset form

## View (`resources/views/livewire/office-manager.blade.php`)
**Layout:** x-dashboard.default

**Sections:**
1. **Header**: Title + "Add New Office" button
2. **Alerts**: Success/error messages
3. **Filters Card**: Search, Department dropdown, Status dropdown
4. **Offices Table**: Code, Name, Department, Location, Phone, Email, Status, Actions
5. **Create/Edit Modal**: Form with all office fields

**Actions:**
- Edit (pencil icon)
- Toggle Status (toggle icon)
- Delete (trash icon with confirmation)

## Routing (`routes/web.php`)
```php
Route::middleware(['auth:sanctum', 'role:System|IT Manager|Super Admin'])->group(function () {
    Route::get('/offices', App\Livewire\OfficeManager::class)->name('offices');
});
```

**Access Control:** System, IT Manager, Super Admin roles only

## Navigation (`resources/views/components/app/sidebar.blade.php`)
Added "Office Management" menu item with:
- Office bag icon
- Role restriction: @hasanyrole('System|IT Manager|Super Admin')
- Active state highlighting
- Route: `route('offices')`

## Seeder

### OfficeSeeder (`database/seeders/OfficeSeeder.php`)
Creates 2 sample offices for each department:
1. Main Office (code: DEPT-MAIN)
2. Admin Office (code: DEPT-ADM)

**Usage:**
```bash
php artisan db:seed --class=OfficeSeeder
```

## Migrations

### Migration Files Created:
1. `2025_11_03_090154_create_offices_table.php` - Creates offices table
2. `2025_11_03_090416_add_office_id_to_assets_table.php` - Adds office_id to assets

**Run Migrations:**
```bash
php artisan migrate
```

## Hierarchy Structure
```
Department
  └── Office (many)
        └── Asset (many)
```

**Example:**
- Department: "Information Technology"
  - Office: "IT Main Office" (IT-MAIN)
    - Assets: Laptops, Servers, etc.
  - Office: "IT Admin Office" (IT-ADM)
    - Assets: Printers, Desks, etc.

## Next Steps for Asset Management Integration

To fully integrate offices with the Asset Management module:

1. **Update Asset Create/Edit Forms:**
   - Add cascade dropdown: Select Department → Select Office (filtered by department)
   - Make office_id optional but recommended

2. **Update Asset List View:**
   - Add Office column to assets table
   - Add office filter dropdown
   - Show office name alongside department

3. **Update Asset Details View:**
   - Display office information
   - Show breadcrumb: Department > Office > Asset

4. **Add Asset Livewire Components Updates:**
   - Modify asset creation component to include office selection
   - Add wire:model for office_id
   - Add department change listener to reload offices dropdown
   - Validate that selected office belongs to selected department

## Testing Checklist

- [ ] Create new office
- [ ] Edit existing office
- [ ] Toggle office status
- [ ] Delete office without assets
- [ ] Try to delete office with assets (should fail)
- [ ] Search offices by name/code/location
- [ ] Filter offices by department
- [ ] Filter offices by status
- [ ] Verify role-based access (only System, IT Manager, Super Admin can access)
- [ ] Check sidebar navigation visibility
- [ ] Verify pagination works correctly

## Security & Validation

**Role-Based Access:**
- Route middleware: `role:System|IT Manager|Super Admin`
- Sidebar visibility: `@hasanyrole('System|IT Manager|Super Admin')`

**Validation Rules:**
- department_id: Required, must exist in departments table
- name: Required, max 255 characters
- code: Unique (excluding current record on update), max 50 characters
- email: Valid email format, max 255 characters
- phone: Max 50 characters
- is_active: Boolean

**Data Integrity:**
- Foreign key constraints with proper cascade/set null rules
- Delete protection for offices with assets
- Soft deletes NOT implemented (hard delete only after asset check)

## Files Modified/Created

### Created:
- `/cis/app/Models/Office.php`
- `/cis/app/Livewire/OfficeManager.php`
- `/cis/resources/views/livewire/office-manager.blade.php`
- `/cis/database/seeders/OfficeSeeder.php`
- `/cis/database/migrations/2025_11_03_090154_create_offices_table.php`
- `/cis/database/migrations/2025_11_03_090416_add_office_id_to_assets_table.php`

### Modified:
- `/cis/app/Models/Department.php` - Added offices() relationship
- `/cis/app/Models/Asset.php` - Added office_id to fillable, added office() relationship
- `/cis/routes/web.php` - Added offices route
- `/cis/resources/views/components/app/sidebar.blade.php` - Added Office Management menu item

## Deployment Notes

When deploying to production:
1. Run migrations: `php artisan migrate`
2. Optionally seed sample offices: `php artisan db:seed --class=OfficeSeeder`
3. Clear cache: `php artisan config:clear && php artisan route:clear && php artisan view:clear`
4. Ensure Super Admin, System, and IT Manager roles exist
