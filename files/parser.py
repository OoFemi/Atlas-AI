from flask import Flask, request
import pdfplumber
import pandas as pd

app = Flask(__name__)

@app.route('/parse', methods=['POST'])
def parse_file():
    data = request.json

    # ✅ Get BOTH values correctly
    file_path = data.get("file_path")
    file_name = data.get("file_name")

    if file_path.endswith(".pdf"):
        with pdfplumber.open(file_path) as pdf:
            text = "\n".join(page.extract_text() or "" for page in pdf.pages)

    elif file_path.endswith(".xlsx"):
        df = pd.read_excel(file_path)
        text = df.to_string()

    else:
        text = "Unsupported file"

    # ✅ Return both text and source
    return {
        "text": text,
        "source": file_name
    }

app.run(host="0.0.0.0", port=5000)
