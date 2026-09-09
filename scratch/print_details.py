import json

with open("scratch/reference_structure.json") as f:
    data = json.load(f)

print("=== TEXTS ===")
for t in data["texts"]:
    print(f"[{t['bbox'][0]:6.1f}, {t['bbox'][1]:6.1f}, {t['bbox'][2]:6.1f}, {t['bbox'][3]:6.1f}] size={t['size']:4.1f} font={t['font']:<15} text={repr(t['text'])}")

print("\n=== DRAWINGS / RECTS ===")
for i, d in enumerate(data["drawings"]):
    print(f"{i:2d}: rect={d['rect']} width={d.get('width')} fill={d.get('fill')}")
