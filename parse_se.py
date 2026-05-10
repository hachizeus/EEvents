import json, re

with open(r'c:\Users\Lenovo\Desktop\E.Events\frontend\src\locales\se.js', 'r', encoding='utf-8') as f:
    content = f.read()

# Find the JSON string inside JSON.parse("...")
start = content.index('JSON.parse("') + len('JSON.parse("')
end = content.rindex('")')
json_str = content[start:end]

print("JSON string length:", len(json_str))
print("First 200 chars:", repr(json_str[:200]))

# The json_str is a JSON-encoded string (with escaped quotes etc.)
# We need to parse it as a JSON string value
# Wrap it in quotes and parse
try:
    # The json_str already has \" for quotes inside, so wrap in outer quotes
    wrapped = '"' + json_str + '"'
    decoded = json.loads(wrapped)
    print("Decoded length:", len(decoded))
    data = json.loads(decoded)
    print("Total keys:", len(data))
    # Print first 5 keys
    for i, (k, v) in enumerate(data.items()):
        if i >= 5:
            break
        print(f"  {k!r}: {v!r}")
except Exception as e:
    print("Error:", e)
