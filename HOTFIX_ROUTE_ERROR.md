# Hotfix: Route Name Error

## Issue
Route `[inspection-standard.view]` not defined error when accessing admin review page.

## Root Cause
The view was referencing a route name that doesn't exist:
- **Used:** `inspection-standard.view`
- **Actual:** `inspection-standard.pdf`

## Solution
Updated the route reference in admin/review/show.blade.php:

### Before
```blade
loadPDF('{{ route('inspection-standard.view', $inspectionStandard->id) }}');
```

### After
```blade
loadPDF('{{ route('inspection-standard.pdf', $inspectionStandard->id) }}');
```

## File Changed
- `resources/views/admin/review/show.blade.php` (line 547)

## Verification
All inspection-standard routes in routes/web.php:
- ✅ `inspection-standard.form` - Show form
- ✅ `inspection-standard.pdf` - View PDF
- ✅ `inspection-standard.download` - Download PDF
- ✅ `inspection-standard.serve-drawing` - Serve drawing files

## Status
✅ Fixed - Route now correctly references the defined route name

