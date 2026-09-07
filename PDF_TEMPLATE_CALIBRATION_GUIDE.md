# PDF Template Calibration Guide - Inspection Standard

## Overview
PDF generation menggunakan **background image approach** untuk akurasi visual 100% dengan form Excel asli.

## Assets Location
- **Template images**: `public/images/pdf-templates/`
  - `xls_page1.png` - Halaman 1 blank form (1241x1754 px)
  - `xls_page2.png` - Halaman 2 blank form (1241x1754 px)
  - `xls_page3.png` - Halaman 3 blank form (1241x1754 px)
  - `xls_page1_grid.png` - Grid reference untuk kalibrasi halaman 1
  - `xls_page2_grid.png` - Grid reference untuk kalibrasi halaman 2
  - `xls_page3_grid.png` - Grid reference untuk kalibrasi halaman 3

## Template Files
- **Main template**: `resources/views/inspection-standard/pdf/template-v2.blade.php`
- **Old template (backup)**: `resources/views/inspection-standard/pdf/template.blade.php`
- **PDF Generator**: `app/Services/InspectionStandardPdfGenerator.php`

## Coordinate Conversion
**Image specs**: 1241 x 1754 pixels @ 150 DPI = 210mm x 297mm (A4 portrait)

**Conversion formula**:
- 1 mm = 5.9055 pixels
- 1 pixel = 0.169333 mm

**Grid reference**:
- Red lines (horizontal/X): every 100px
- Blue lines (vertical/Y): every 100px
- Use grid to read pixel coordinates, then convert to mm for CSS

## Page 1 Calibrated Coordinates

### Header Section
| Field | Top | Left | Font Size | Note |
|-------|-----|------|-----------|------|
| MODEL | 11mm | 44mm | 8px | Row 1 |
| DATE OF ISSUED | 11mm | 122mm | 8px | Row 1 |
| REF. NO | 19mm | 122mm | 8px | Row 2 |

### Part Route Section
| Field | Top | Left | Font Size |
|-------|-----|------|-----------|
| PART ROUTE | 29mm | 52mm | 7px |

### Supplier Row
| Field | Top | Left | Font Size |
|-------|-----|------|-----------|
| SUPPLIER | 38mm | 52mm | 7px |
| MANUF. | 38mm | 95mm | 7px |
| MATERIAL | 38mm | 126mm | 7px |
| GENERAL TOL | 38mm | 156mm | 7px |
| WEIGHT | 38mm | 181mm | 7px |

### Title Block (PART NO / PART NAME)
| Field | Top | Left | Font Size |
|-------|-----|------|-----------|
| PART NO | 70mm | 75mm | 8px |
| PART NAME | 77mm | 75mm | 8px |

### Manufacturer/Supplier Info
| Field | Top | Left | Font Size |
|-------|-----|------|-----------|
| Manufacturer | 97mm | 42mm | 7px |
| Supplier | 97mm | 105mm | 7px |

### Revision Column (First Row Data)
| Field | Top | Left | Font Size |
|-------|-----|------|-----------|
| DATE | 127mm | 48mm | 7px |
| REVISION RECORD | 127mm | 81mm | 7px |

## Page 2 Calibrated Coordinates

### Header Section
| Field | Top | Left | Font Size |
|-------|-----|------|-----------|
| MODEL | 11mm | 44mm | 8px |
| PART NO | 11mm | 118mm | 8px |

### Drawing Area
- **Position**: top: 34mm, left: 16mm
- **Size**: width: 178mm, height: 230mm
- **Content**: Dynamic drawing list with images

## Page 3 - HTML Table (Dynamic)
Page 3 menggunakan **HTML table biasa** karena jumlah baris dinamis (inspection items).

### Specifications
- **8 rows** with height: 55px each
- **7 columns** (NO "No" column):
  1. INSPECTION ITEM (20%)
  2. INSPECTION INSTRUMENT (14%)
  3. INSPECTION CRITERION (18%)
  4. RANK (7%)
  5. SUPPLIER SAMPLING (10%)
  6. AAIJ SAMPLING (10%)
  7. REMARK (21%)

## Calibration Process

### Step 1: Read Grid Reference
1. Open `xls_page1_grid.png` or `xls_page2_grid.png`
2. Identify the blank field area you want to fill
3. Read X coordinate (red numbers) and Y coordinate (blue numbers) in pixels

### Step 2: Convert to MM
```
mm = pixels × 0.169333
```

Example:
- Field at pixel (350, 65)
- X: 350px × 0.169333 = 59.27mm ≈ 59mm
- Y: 65px × 0.169333 = 11.01mm ≈ 11mm
- CSS: `top: 11mm; left: 59mm;`

### Step 3: Update Template
Edit `resources/views/inspection-standard/pdf/template-v2.blade.php`:

```html
<div class="field" style="top: 11mm; left: 59mm; font-size: 8px;">
    {{ $generalInformation['fieldName'] }}
</div>
```

### Step 4: Clear Cache & Test
```powershell
php artisan view:clear
php artisan cache:clear
```

Then generate PDF from Inspection Standard form and check alignment.

### Step 5: Fine-tune
If field is slightly off:
- Adjust top/left by ±1mm or ±2mm
- Adjust font-size if text too large/small
- Regenerate PDF and compare

## Current Status

✅ **Page 1**: Background image + calibrated field overlays
✅ **Page 2**: Background image + drawing area overlay
✅ **Page 3**: HTML table (dynamic rows)

### Initial Calibration Done
Coordinates are estimated based on grid reference. May need fine-tuning after first PDF generation.

## Testing Checklist

Generate test PDF and verify:

**Page 1:**
- [ ] MODEL field aligned
- [ ] DATE OF ISSUED aligned
- [ ] REF. NO aligned
- [ ] PART ROUTE aligned
- [ ] SUPPLIER, MANUF, MATERIAL, GENERAL TOL, WEIGHT aligned
- [ ] PART NO underline aligned
- [ ] PART NAME underline aligned
- [ ] Manufacturer/Supplier info aligned
- [ ] Revision DATE aligned
- [ ] Revision RECORD aligned

**Page 2:**
- [ ] MODEL field aligned
- [ ] PART NO field aligned
- [ ] Drawings appear in correct area
- [ ] Drawing images display properly

**Page 3:**
- [ ] Table has 8 rows
- [ ] No "No" column
- [ ] All 7 columns present
- [ ] Row height looks proportional (~55px)
- [ ] Data fills correctly

## Advantages of This Approach

✅ **100% Visual Accuracy** - Background = original form  
✅ **No Border/Spacing Guesswork** - All visual elements from image  
✅ **Fast Iteration** - Only adjust CSS coordinates, no HTML rebuild  
✅ **Future-proof** - Form change? Just replace background image  
✅ **Mixed Approach** - Static pages use image, dynamic page uses HTML table

## Troubleshooting

### Field Not Aligned
1. Check grid reference image again
2. Recalculate pixel → mm conversion
3. Fine-tune ±1-2mm in CSS
4. Regenerate PDF

### Font Too Large/Small
Adjust `font-size` in `.field` class or inline style:
- Try: 7px, 8px, 9px
- Check grid image for reference text size

### Background Image Not Showing
1. Verify image exists in `public/images/pdf-templates/`
2. Check `public_path()` resolves correctly
3. Check dompdf image loading settings

### Drawing Area Overflow
Adjust drawing area container:
- Increase `height` if truncated
- Add `overflow: auto` for scrolling (PDF may not support)
- Or limit number of drawings displayed

## Files Modified

1. `app/Services/InspectionStandardPdfGenerator.php` - Switch to template-v2
2. `resources/views/inspection-standard/pdf/template-v2.blade.php` - New template with background images
3. `public/images/pdf-templates/` - Added 6 PNG files

## Backup

Old template preserved at:
- `resources/views/inspection-standard/pdf/template.blade.php`

To revert:
```php
$pdf = Pdf::loadView('inspection-standard.pdf.template', $data)
```

---

**Last Updated**: 2026-09-07  
**Status**: Initial calibration complete, ready for first test generation
