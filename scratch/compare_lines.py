import pymupdf

doc_ref = pymupdf.open("10. Packaging Standard.pdf")
doc_gen = pymupdf.open("scratch/output_ps.pdf")

page_ref = doc_ref[0]
page_gen = doc_gen[0]

print("=== REF MAIN HORIZONTAL LINES ===")
for d in page_ref.get_drawings():
    r = [round(x, 1) for x in d["rect"]]
    # horizontal line if height <= 2
    if r[3] - r[1] <= 3 and r[2] - r[0] > 100:
        print(f"Ref H-Line: Y={r[1]} -> {r[3]}, X={r[0]} -> {r[2]}")

print("\n=== REF MAIN VERTICAL LINES ===")
for d in page_ref.get_drawings():
    r = [round(x, 1) for x in d["rect"]]
    # vertical line if width <= 3
    if r[2] - r[0] <= 3 and r[3] - r[1] > 50:
        print(f"Ref V-Line: X={r[0]} -> {r[2]}, Y={r[1]} -> {r[3]}")

print("\n=== GEN MAIN HORIZONTAL LINES ===")
for d in page_gen.get_drawings():
    r = [round(x, 1) for x in d["rect"]]
    if r[3] - r[1] <= 3 and r[2] - r[0] > 100:
        print(f"Gen H-Line: Y={r[1]} -> {r[3]}, X={r[0]} -> {r[2]}")

print("\n=== GEN MAIN VERTICAL LINES ===")
for d in page_gen.get_drawings():
    r = [round(x, 1) for x in d["rect"]]
    if r[2] - r[0] <= 3 and r[3] - r[1] > 50:
        print(f"Gen V-Line: X={r[0]} -> {r[2]}, Y={r[1]} -> {r[3]}")
