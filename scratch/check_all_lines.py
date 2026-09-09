import pymupdf

doc = pymupdf.open("scratch/output_ps.pdf")
page = doc[0]

print("=== ALL HORIZONTAL LINES IN GENERATED PDF ===")
for d in page.get_drawings():
    r = d["rect"]
    if r.height < 3 and r.width > 20:
        print(f"H-Line: Y={r.y0:.2f} -> {r.y1:.2f}, X={r.x0:.2f} -> {r.x1:.2f}")

print("\n=== ALL VERTICAL LINES IN GENERATED PDF ===")
for d in page.get_drawings():
    r = d["rect"]
    if r.width < 3 and r.height > 15:
        print(f"V-Line: X={r.x0:.2f} -> {r.x1:.2f}, Y={r.y0:.2f} -> {r.y1:.2f}")
