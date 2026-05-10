import json

with open(r'c:\Users\Lenovo\Desktop\E.Events\frontend\src\locales\se.js', 'r', encoding='utf-8') as f:
    content = f.read()

start = content.index('JSON.parse("') + len('JSON.parse("')
end = content.rindex('")')
json_str = content[start:end]
wrapped = '"' + json_str + '"'
decoded = json.loads(wrapped)
data = json.loads(decoded)

# Print all string values with their keys
for k, v in data.items():
    if isinstance(v, str):
        print(f"{k}|||{v}")
