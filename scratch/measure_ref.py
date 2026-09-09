import pymupdf

doc = pymupdf.open("10. Packaging Standard.pdf")
page = doc[0]

# Ignore white background rect if any
lines = [d["rect"] for d in page.get_drawings() if d.get("fill") != [1.0, 1.0, 1.0]]
min_x = min(r.x0 for r in lines)
max_x = max(r.x1 for r in lines)
min_y = min(r.y0 for r in lines)
max_y = max(r.y1 for r in lines)

print(f"REFERENCE PDF:")
print(f"Page width: {page.rect.width}, height: {page.rect.height}")
print(f"Table bbox: X: {min_x:.2f} -> {max_x:.2f}, Y: {min_y:.2f} -> {max_y:.2f}")
print(f"Margin Left: {min_x:.2f} pt")
print(f"Margin Right: {page.rect.width - max_x:.2f} pt")
print(f"Margin Top: {min_y:.2f} pt")
print(f"Margin Bottom: {page.rect.height - max_y:.2f} pt")
