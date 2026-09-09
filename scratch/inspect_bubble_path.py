import pymupdf

doc = pymupdf.open("10. Packaging Standard.pdf")
page = doc[0]

drawings = page.get_drawings()
for i in [52, 53]:
    d = drawings[i]
    print(f"\nDrawing {i}:")
    print("Rect:", d["rect"])
    print("Color:", d.get("color"))
    print("Fill:", d.get("fill"))
    print("Width:", d.get("width"))
    print("Items:")
    for item in d["items"]:
        print(" ", item)
