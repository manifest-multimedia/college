# Deprecated: Livewire Assessment Scores Module

**Status**: ⚠️ DEPRECATED  
**Date Deprecated**: January 28, 2026  
**Replaced By**: AJAX-based Assessment Scores module

## Why Deprecated?

The Livewire-based Assessment Scores module was replaced with a pure AJAX implementation due to:

1. **State Management Issues**: Livewire's wire:model had unpredictable behavior with conditional disabling of dropdowns
2. **Performance**: AJAX provides more explicit control over DOM updates and loading states
3. **Enhanced Features**: New AJAX version includes:
   - Excel import with preview modal
   - Loading spinners for all operations
   - Keyboard shortcuts (Ctrl+S, Enter navigation, Esc)
   - Enhanced error handling with field-level validation
   - Better UX with explicit state control

## Deprecated Files

### Component
- `component/AssessmentScores.php` - Main Livewire component (565 lines)

### Views
- `views/assessment-scores.blade.php` - Main Livewire view
- `views/assessment-scores-wrapper.blade.php` - Wrapper view that loaded Livewire component
- `views/assessment-scores-index.blade.php` - Index route view

## Current Implementation

**Controller**: `app/Http/Controllers/Admin/AssessmentScoresController.php`  
**View**: `resources/views/admin/assessment-scores-ajax.blade.php`  
**Routes**: `routes/web.php` (admin.assessment-scores.* group)

### Routes (Active)
- `GET  /admin/assessment-scores` → Main view
- `GET  /admin/assessment-scores/get-courses` → Filter courses by program
- `POST /admin/assessment-scores/load-scoresheet` → Load students with scores
- `POST /admin/assessment-scores/save-scores` → Bulk save
- `GET  /admin/assessment-scores/download-template` → Excel template
- `POST /admin/assessment-scores/import-excel` → Import preview
- `POST /admin/assessment-scores/confirm-import` → Confirm import
- `POST /admin/assessment-scores/export-excel` → Export to Excel

## Migration Notes

All functionality from the Livewire version has been preserved and enhanced in the AJAX version:

✅ Filter by Program, Course, Cohort, Semester  
✅ Dynamic assignment columns (3-5)  
✅ Weight configuration (assignment, mid-semester, end-semester)  
✅ Real-time score calculation  
✅ Grade determination  
✅ Statistics dashboard  
✅ Excel template download  
✅ Excel import with validation (NEW: with preview)  
✅ Excel export  
✅ Bulk save with validation  

**Plus NEW Features**:
- Loading spinners on all AJAX operations
- Keyboard shortcuts for faster data entry
- Import preview modal before saving
- Enhanced error messages
- Better performance and reliability

## Restoration (If Needed)

If you need to restore the Livewire version:

1. Move files back to original locations:
   ```bash
   mv deprecated/livewire-assessment-scores/component/AssessmentScores.php app/Livewire/Admin/
   mv deprecated/livewire-assessment-scores/views/assessment-scores.blade.php resources/views/livewire/admin/
   mv deprecated/livewire-assessment-scores/views/assessment-scores-wrapper.blade.php resources/views/admin/assessment-scores.blade.php
   ```

2. Update routes in `routes/web.php` to use Livewire component

3. Disable AJAX controller routes

**Note**: This is NOT recommended. The AJAX version is superior in every way.

## Documentation

See detailed documentation:
- [AJAX Enhancement Guide](../../md-files/ASSESSMENT_SCORES_AJAX_ENHANCEMENTS.md)
- [Module Specification](../../ASSESSMENT_SCORES_MODULE_SPEC.md)
- [Implementation Guide](../../ASSESSMENT_SCORES_IMPLEMENTATION_COMPLETE.md)
- [Testing Guide](../../ASSESSMENT_SCORES_TESTING_GUIDE.md)

---

**Do NOT delete these files** - kept for reference and potential code reuse in other modules.
