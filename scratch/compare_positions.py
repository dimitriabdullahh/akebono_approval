import pymupdf
import json

doc_ref = pymupdf.open("10. Packaging Standard.pdf")
doc_gen = pymupdf.open("scratch/output_ps.pdf")

page_ref = doc_ref[0]
page_gen = doc_gen[0]

text_ref = page_ref.get_text("dict")["blocks"]
text_gen = page_gen.get_text("dict")["blocks"]

print("--- REF TEXTS COUNT ---", len(text_ref))
print("--- GEN TEXTS COUNT ---", len(text_gen))

# Let's inspect drawings
dr_ref = page_ref.get_drawings()
dr_gen = page_gen.get_drawings()

print(f"Ref drawings: {len(dr_ref)}, Gen drawings: {len(dr_gen)}")

# Let's check bbox of main elements
def get_text_spans(page):
    spans = []
    for b in page.get_text("dict")["blocks"]:
        if "lines" in b:
            for l in b["lines"]:
                for s in l["spans"]:
                    if s["text"].strip():
                        spans.append((s["text"].strip(), [round(x, 1) for x in s["bbox"]]))
    return spans

spans_ref = dict(get_text_spans(page_ref))
spans_gen = dict(get_text_spans(page_gen))

print("\n=== COMPARING POSITIONS OF KEY LABELS ===")
for k in ["PACKAGING STANDARD", "YOUR COMPANY LOGO", "Approved", "Checked", "Prepared", "PART / PACKAGING MATERIALS", "REVISION STATUS", "(Photographic Image of Packaging)", "(Part Identification Point)", "AAIJ Approval", "QA", "PPC", "Purchasing"]:
    r_pos = spans_ref.get(k)
    g_pos = spans_gen.get(k)
    print(f"{k:<35} | Ref: {str(r_pos):<25} | Gen: {str(g_pos):<25}")
