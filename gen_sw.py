import json

with open(r"c:\Users\Lenovo\Desktop\E.Events\frontend\src\locales\se.js", "r", encoding="utf-8") as f:
    content = f.read()

start = content.index('JSON.parse("') + len('JSON.parse("')
end = content.rindex('")')
json_str = content[start:end]
wrapped = chr(34) + json_str + chr(34)
decoded = json.loads(wrapped)
data = json.loads(decoded)
