import json
import re

path = r"C:\Users\kurdn\.gemini\antigravity-ide\brain\ea172c79-2f5f-49f3-8ce4-119c22dba081\.system_generated\logs\transcript.jsonl"
out_path = r"C:\Users\kurdn\.gemini\antigravity-ide\brain\ea172c79-2f5f-49f3-8ce4-119c22dba081\phase_16_1_description.md"

with open(path, "r", encoding="utf-8") as f:
    for line in f:
        obj = json.loads(line)
        content = obj.get("content") or ""
        if "Phase 16.1" in content and obj.get("source") == "USER_EXPLICIT":
            with open(out_path, "w", encoding="utf-8") as out:
                out.write(content)
            print(f"Successfully wrote phase_16_1_description.md from step {obj.get('step_index')}")
            break
