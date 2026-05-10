import json

# Swedish to Swahili translation map for string values
# Key: Swedish string, Value: Swahili string
sw_map = {}

# Load the Swedish locale
with open(r"c:\Users\Lenovo\Desktop\E.Events\frontend\src\locales\se.js", "r", encoding="utf-8") as f:
    content = f.read()

start = content.index(chr(74)+chr(83)+chr(79)+chr(78)+chr(46)+chr(112)+chr(97)+chr(114)+chr(115)+chr(101)+chr(40)+chr(34)) + 12
end = content.rindex(chr(34)+chr(41))
json_str = content[start:end]
wrapped = chr(34) + json_str + chr(34)
decoded = json.loads(wrapped)
data = json.loads(decoded)

print("Keys:", len(data))

