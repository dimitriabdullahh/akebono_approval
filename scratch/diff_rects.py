import pymupdf
import json

doc_ref = pymupdf.open("10. Packaging Standard.pdf")
doc_gen = pymupdf.open("scratch/output_ps.pdf")

page_ref = doc_ref[0]
page_gen = doc_gen[0]

print("=== REF DRAWINGS ===")
for d in page_ref.get_drawings():
    r = [round(x, 1) for x in d["rect"]]
    print(f"Ref Rect: {r}")

print("\n=== GEN DRAWINGS ===")
for d in page_gen.get_drawings():
    r = [round(x, 1) for x in d["rect"]]
    print(f"Gen Rect: {r}")
