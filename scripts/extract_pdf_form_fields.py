#!/opt/venv/bin/python

import sys
import json
import pymupdf

def extract_form_fields(pdf_path):
    doc = pymupdf.open(pdf_path)
    fields = []

    for page_num, page in enumerate(doc):
        # Loop through all form widgets on the page
        for widget in page.widgets():
            fields.append({
                'name': widget.field_name,
                'value': widget.field_value,
                'type': widget.field_type_string, # e.g., 'Text', 'Btn'
                'page': page_num + 1,
                'rect': {
                    'x': widget.rect.x0,
                    'y': widget.rect.y0,
                    'width': widget.rect.width,
                    'height': widget.rect.height
                }
            })
    
    return fields

if __name__ == "__main__":
    if len(sys.argv) < 2:
        print(json.dumps({"error": "No file path provided"}))
        sys.exit(1)

    try:
        data = extract_form_fields(sys.argv[1])
        print(json.dumps(data))
    except Exception as e:
        print(json.dumps({"error": str(e)}))