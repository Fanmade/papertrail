import sys
import json
import fitz  # PyMuPDF

def fill_pdf(input_path, output_path, json_file_path):
    doc = fitz.open(input_path)

    # READ JSON FROM FILE
    with open(json_file_path, 'r', encoding='utf-8') as f:
        form_data = json.load(f)

    for page in doc:
        for widget in page.widgets():
            if widget.field_name in form_data:
                fill_value = form_data[widget.field_name]

                if widget.field_type_string == 'Btn':
                    if isinstance(fill_value, bool):
                        widget.field_value = widget.on_state() if fill_value else "Off"
                    else:
                        widget.field_value = str(fill_value)
                else:
                    widget.field_value = str(fill_value)

                widget.update()

    doc.save(output_path, deflate=True)

if __name__ == "__main__":
    if len(sys.argv) < 4:
        print(json.dumps({"error": "Usage: python3 fill_form.py <input> <output> <json_file_path>"}))
        sys.exit(1)

    try:
        # sys.argv[3] is now the path to the json file
        fill_pdf(sys.argv[1], sys.argv[2], sys.argv[3])
        print(json.dumps({"status": "success"}))
    except Exception as e:
        print(json.dumps({"status": "error", "message": str(e)}))
        sys.exit(1)