🛠️ Laravel Development Standards — Refined Instructions
📦 Framework & Tools
Laravel Version: 12.11.1

Livewire: Uses tag-based syntax — e.g., <livewire:component-name />

🖼️ View Layout Standards
Use Laravel Blade Components for all views:

✅ Layouts
Dashboard/Dashboard Views: Use components.default.layout.blade.php

To use this in blade we use tag syntax and pass in the title 
<x-backend.default title="title" /> 
Contnet 
</x-backend.default>

📌 Note: Use components.dashboard.default for all dashboard-related views.

✅ UI Guidelines
Use Bootstrap for frontend styling.

For card headers, wrap content using the card-title class for consistency:

html
Copy
Edit
<div class="card-header">
    <div class="something-else">
        <h1 class="card-title">
            <i>some icon</i>
        </h1>
    </div>
</div>
🗃️ Database Migration Policy
✅ Allowed
You may modify migration files only for new features under development and not deployed to production.

Use php artisan migrate:rollback (not migrate:fresh) during active development to fix migration-related issues.

❌ Not Allowed
Never alter migration files for features already in production.

📌 For Existing Deployed Features
If you need to update the schema for production features (e.g., add/modify columns):

Create a new migration file.

This maintains backward compatibility and production stability.

⚠️ Migration Cautions
Before creating a new migration:

Ensure no existing migration causes conflicts.

When building relationships:

Be mindful of how ID fields are defined (increments('id') vs id()).

Always confirm ID type compatibility between related tables.

🔧 New Feature Development Instructions (Finance & Exam Clearance Modules)
New feature development is documented in exam-prd.md.

Layouts: Use components.dashboard.default for all related views.

Migrations can still be revised until final production deployment.

🧩 Navigation Updates
When features are complete, update the sidebar navigation:

File: components.app.sidebar

✅ Tasks Completed
We completed the impementation of the Finance Management and Course Registration Modules but their still in development and testing stage.

While testing we've shipped it to production now.

In Laravel 12  Kernel is no longer present we use bootstrap/app.php isntead.

When impletting logging to track issues use the laravel logs facade by important the class and referencing the Log;

When working with Uploads and Image Photos in Livewire: Reference the documentation https://livewire.laravel.com/docs/uploads#temporary-preview-urls

Our students table uses             $table->increments('id'); for the id field. 

Hence a realtionship with this would be defined as follows: 

$table->unsignedInteger('subject_id');
$table->foreign('subject_id')->references('id')->on('subjects');

This is an example of how you properly reference tables tables which uses increments id for the ID field.
