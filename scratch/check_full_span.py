import pymupdf
import json

doc = pymupdf.open("10. Packaging Standard.pdf")
page = doc[0]

# Let's inspect all horizontal lines that span all the way across (from X ~ 4.9 to X ~ 787.2)
print("=== FULL SPAN HORIZONTAL LINES IN REFERENCE PDF ===")
for d in page.get_drawings():
    r = d["rect"]
    if r.x0 < 10 and r.x1 > 780 and (r.y1 - r.y0) < 3:
        print(f"Y = {r.y0:.2f} to {r.y1:.2f} (X: {r.x0:.2f} -> {r.x1:.2f})")
