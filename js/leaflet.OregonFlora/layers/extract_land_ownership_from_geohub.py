#!/usr/bin/env python3
"""
Extract each LandManager from Ownership_Land_Management.geojson
into a separate GeoJSON file, named <LandManager>_coordinates.geojson.
"""

import json
import os

INPUT_FILE = os.path.join(os.path.dirname(__file__), "Ownership_Land_Management.geojson")
OUTPUT_DIR = os.path.dirname(__file__)

with open(INPUT_FILE, encoding="utf-8") as f:
    data = json.load(f)

# Layer features by LandManager
groups: dict[str, list] = {}
for feature in data["features"]:
    layer = feature["properties"].get("LandManager") or "Unknown"
    # insert empty list for new layer (key), then append
    groups.setdefault(layer, []).append(feature)  

# sort layer name in alphabetical order for deterministic output
for layer, features in sorted(groups.items()):  
    out = {
        "type": "FeatureCollection",
        "name": f"{layer}_Land_Management",
        "features": features,
    }
    filename = os.path.join(OUTPUT_DIR, f"{layer}_coordinates.geojson")
    with open(filename, "w", encoding="utf-8") as f:
        json.dump(out, f, separators=(",", ":"))
    print(f"Written {len(features):4d} features -> {os.path.basename(filename)}")

print(f"\nDone. {len(groups)} files created.")
