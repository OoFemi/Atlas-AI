from unstructured.partition.auto import partition
from qdrant_client import QdrantClient
import requests
import os

OLLAMA_URL = "http://ollama:11434/api/embeddings"
COLLECTION = "docs"

client = QdrantClient(host="qdrant", port=6333)

def embed(text):
    r = requests.post(
        OLLAMA_URL,
        json={"model": "nomic-embed-text", "prompt": text},
        timeout=120
    )
    return r.json()["embedding"]

def chunk_text(text, size=500):
    return [text[i:i+size] for i in range(0, len(text), size)]

def process_file(path):
    print(f"Processing {path}")
    elements = partition(filename=path)
    text = "\n".join([el.text for el in elements if el.text])

    for chunk in chunk_text(text):
        vector = embed(chunk)
        client.upsert(
            collection_name=COLLECTION,
            points=[{
                "id": abs(hash(chunk)),
                "vector": vector,
                "payload": {
                    "text": chunk,
                    "source": os.path.basename(path)
                }
            }]
        )

FILES_DIR = "/files"

for filename in os.listdir(FILES_DIR):
    full_path = os.path.join(FILES_DIR, filename)
    if os.path.isfile(full_path) and not filename.endswith(".py"):
        process_file(full_path)
