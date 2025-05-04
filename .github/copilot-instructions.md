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

🗳️ Election System Implementation
Use the existing student data to verify and authorize students for voting.

Do not create duplicate student models or tables.

If any structural extensions are needed (e.g., new relations or pivot tables), implement them using new migration files only.

The Election System is still in development hence we can modify it's migrations where needed until everything is working correctly. 