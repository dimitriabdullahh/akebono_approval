import fitz # PyMuPDF
import json

doc = fitz.open("10. Packaging Standard.pdf")
page = doc[0]

print("Page rect:", page.rect)

# Render to PNG
pix = page.get_pixmap(dpi=150)
pix.save("scratch/reference_page.png")
print("Saved reference_page.png")

# Extract drawings (lines, rects)
drawings = page.get_drawings()
print(f"Total drawings: {len(drawings)}")

lines_info = []
for d in drawings:
    rect = d["rect"]
    color = d.get("color")
    width = d.get("width")
    fill = d.get("fill")
    lines_info.append({
        "rect": [round(x, 2) for x in rect],
        "width": width,
        "color": color,
        "fill": fill,
        "items": len(d.get("items", []))
    })

# Extract text blocks
text_blocks = page.get_text("dict")["blocks"]
texts = []
for b in text_blocks:
    if "lines" in b:
        for l in b["lines"]:
            for s in l["spans"]:
                texts.append({
                    "text": s["text"],
                    "bbox": [round(x, 2) for x in s["bbox"]],
                    "font": s["font"],
                    "size": round(s["size"], 2),
                    "color": s["color"],
                    "flags": s["flags"]
                })

with open("scratch/reference_structure.json", "w") as f:
    json.dump({"rect": [page.rect.x0, page.rect.y0, page.rect.x1, page.rect.y1], "drawings": lines_info, "texts": texts}, f, indent=2)

print("Saved reference_structure.json")
