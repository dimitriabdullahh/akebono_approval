import pymupdf

doc = pymupdf.open("scratch/output_ps.pdf")
page = doc[0]

for d in page.get_drawings():
    r = d["rect"]
    # check lines in approval area (X > 600, Y < 120)
    if r.x0 > 600 and r.y0 < 120:
        print(f"Approval line: rect={[round(x, 2) for x in r]}, width={d.get('width')}")
