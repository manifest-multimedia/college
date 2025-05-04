🛠️ Laravel Development Standards — Refined Instructions
📦 Framework Version
Laravel Version: 12.11.1

Livewire: Uses tag-based notation (e.g., <livewire:component-name />)

🖼️ View Layout Standards
Use Laravel Blade Components for all views:

Backend Views:
Use components.backend.layout.blade.php

Dashboard Views:
Use components.default.layout.blade.php

Apply these layouts to all new feature implementations to maintain consistency across the application.

🗃️ Database Migration Policy
✅ You may modify migration files directly, only if they belong to new features currently under development and have not yet been deployed to production.

❌ You must not alter existing migrations for features that are already in production.

🆕 For any schema changes to existing, deployed features (e.g., adding/modifying columns or constraints), you must create a new migration file.

This ensures stability and backward compatibility in the production environment while preserving clean migrations during development.

New Feature Implementation:
- We're now implemnting new features in the exam-prd.md document. For these new features we can revise their migrations until we are done with a successful implementation and move to production.
- Note that for every mistake in development which requires an update on the migration for these modules we'd run php artisan migrate:rollback instead of migrate:fresh.
- Before creating new migrations for new features ensure no existing migration exists that may conflict with it.
- Pay close attention to how id fields are defined in tables you need to build relationships for for instance some tables use increments('id') whereas others may simply use table->id we need to know these before proceed to build relationships to avoid errors. 
- use the components.dashboard.default component for all layouts.
- Use Bootstrap for UI 
- for component cards inside the "card-header" div use "card-title" to wrap the content for a consistent look with our ui template. e.g. 
<div class="card-header">
    <div class="something-else">
    <h1 class="card-title">
    <i>some icon</i>
    </h1>
    </div>
</div>   

- When done update the applications navigation component to include the relevant routes. This file is: components.app.sidebar

Tasks completed:
- Created migrations and models for the Finance Management + Exam cleareance models.
- Created FeeStructureManager (Livewire Component)
- Created FeeTypesManager (Livewire Component)
- Created StudentBillingManager (Livewire Component)

Incomplete Tasks:
- Create StudentBillingManager Service
