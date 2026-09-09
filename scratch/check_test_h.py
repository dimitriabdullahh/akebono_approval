import pymupdf
import json

doc = pymupdf.open('scratch/test_h.pdf')
page_count = len(doc)
if page_count > 0:
    page = doc[0]
    drawings = page.get_drawings()
    # Find bottom line of center title (around x=400, y=114.8)
    center_y1 = None
    for d in drawings:
        r = d['rect']
        if 250 < r.x0 < 550 and 110 < r.y0 < 120:
            center_y1 = round(r.y1, 2)
    
    # Find lines in approval box
    app_lines = []
    for d in drawings:
        r = d['rect']
        if r.x0 > 600 and 100 < r.y0 < 125:
            app_lines.append({'x0': round(r.x0, 2), 'y0': round(r.y0, 2), 'x1': round(r.x1, 2), 'y1': round(r.y1, 2)})
    
    print(json.dumps({'pages': page_count, 'center_bottom_y': center_y1, 'app_lines': app_lines}))
