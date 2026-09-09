import pymupdf

doc = pymupdf.open("scratch/output_ps.pdf")
page = doc[0]

rects = [d["rect"] for d in page.get_drawings()]
min_x = min(r.x0 for r in rects)
max_x = max(r.x1 for r in rects)
min_y = min(r.y0 for r in rects)
max_y = max(r.y1 for r in rects)

print(f"Page width: {page.rect.width}, height: {page.rect.height}")
print(f"Table bbox: X: {min_x:.2f} -> {max_x:.2f}, Y: {min_y:.2f} -> {max_y:.2f}")
print(f"Margin Left: {min_x:.2f} pt")
print(f"Margin Right: {page.rect.width - max_x:.2f} pt")
print(f"Margin Top: {min_y:.2f} pt")
print(f"Margin Bottom: {page.rect.height - max_y:.2f} pt")
