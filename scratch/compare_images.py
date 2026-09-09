import pymupdf # PyMuPDF
from PIL import Image

def render_pdf_to_img(pdf_path, out_path):
    doc = pymupdf.open(pdf_path)
    page = doc[0]
    pix = page.get_pixmap(dpi=150)
    pix.save(out_path)
    return out_path

render_pdf_to_img("10. Packaging Standard.pdf", "scratch/ref.png")
render_pdf_to_img("scratch/output_ps.pdf", "scratch/generated.png")

img1 = Image.open("scratch/ref.png")
img2 = Image.open("scratch/generated.png")

# Create side-by-side comparison
w, h = img1.size
combined = Image.new("RGB", (w * 2 + 20, h), (200, 200, 200))
combined.paste(img1, (0, 0))
combined.paste(img2, (w + 20, 0))
combined.save("scratch/side_by_side.png")
print("Saved scratch/side_by_side.png")
