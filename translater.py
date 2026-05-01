#!/usr/bin/env python3
import os
import re
import argparse
import pandas as pd
import ast

# ---------------- CONFIG ----------------
DEFAULT_SOURCE_DIRS = ["./app", "./resources"]
DEFAULT_LANG_BASE_DIR = "./lang"
DEFAULT_LANGS = ["de", "en", "fr"]
DEFAULT_EXCEL_FILE = "translations.xlsx"

# Match t('a.b') or just 'a.b' strings inside quotes if they look like translation keys
TRANSLATION_REGEX = re.compile(
    r"(?:[t|_]\(\s*|title:\s*|placeholder:\s*)['\"]([a-zA-Z0-9_]+)\.([a-zA-Z0-9_\.]+)['\"]"
)

# ---------------- UTILITIES ----------------

def flatten(d, prefix=""):
    out = {}
    for k, v in d.items():
        key = f"{prefix}.{k}" if prefix else k
        if isinstance(v, dict):
            out.update(flatten(v, key))
        else:
            out[key] = v
    return out

def php_to_dict(file_path):
    """
    Safely parses a simple Laravel PHP array return file.
    Does not use eval(). Uses a basic recursive structure parser.
    """
    if not os.path.exists(file_path):
        return {}
    
    with open(file_path, "r", encoding="utf-8") as f:
        content = f.read()

    # Extract array content between return [ ... ];
    match = re.search(r"return\s*\[(.*)\];", content, re.DOTALL)
    if not match:
        return {}
    
    # This is a naive parser. For full PSR compliance, a real token parser is needed.
    # But for standard Laravel lang files, this works well.
    return _parse_php_array_content(match.group(1))

def _parse_php_array_content(content):
    # This replaces PHP-style array syntax with Python dictionary-like syntax
    # and uses ast.literal_eval for safety.
    # 1. Remove comments
    content = re.sub(r"//.*", "", content)
    # 2. Convert 'key' => 'val' to 'key': 'val'
    content = re.sub(r"(['\"])\s*=>\s*", r"\1: ", content)
    # 3. Handle trailing commas
    content = re.sub(r",\s*]", "]", content)
    
    try:
        # Wrap in brackets to make it a valid dict string
        return ast.literal_eval("{" + content + "}")
    except:
        return {}

def build_nested(key, value, root):
    parts = key.split(".")
    d = root
    for p in parts[:-1]:
        d = d.setdefault(p, {})
    d[parts[-1]] = value

def dict_to_php(d, indent=1):
    pad = "    " * indent
    lines = []
    for k, v in d.items():
        if isinstance(v, dict):
            lines.append(f'{pad}"{k}" => {dict_to_php(v, indent + 1)}')
        else:
            val = str(v).replace('"', '\\"')
            lines.append(f'{pad}"{k}" => "{val}"')
    return "[\n" + ",\n".join(lines) + f"\n{'    ' * (indent-1)}]"

# ---------------- COMMANDS ----------------

def extract(source_dirs, lang_base_dir, langs, excel_file):
    rows = []
    for base in source_dirs:
        for root, _, files in os.walk(base):
            for file in files:
                if not file.endswith(('.php', '.tsx', '.ts')): continue
                path = os.path.join(root, file)
                with open(path, encoding="utf-8", errors="ignore") as f:
                    for line in f:
                        for m in TRANSLATION_REGEX.finditer(line):
                            rows.append({"table": m.group(1), "value": m.group(2)})

    df = pd.DataFrame(rows).drop_duplicates()
    
    # Load existing
    existing = {lang: {} for lang in langs}
    for lang in langs:
        lang_dir = os.path.join(lang_base_dir, lang)
        if os.path.isdir(lang_dir):
            for f in os.listdir(lang_dir):
                if f.endswith(".php"):
                    existing[lang][f[:-4]] = flatten(php_to_dict(os.path.join(lang_dir, f)))

    with pd.ExcelWriter(excel_file, engine="xlsxwriter") as writer:
        for table, g in df.groupby("table"):
            sheet = g[["value"]].copy()
            for lang in langs:
                sheet[lang] = sheet["value"].apply(lambda k: existing.get(lang, {}).get(table, {}).get(k, ""))
            sheet.to_excel(writer, sheet_name=table[:31], index=False)
    print(f"✅ Extracted to {excel_file}")

def apply(lang_base_dir, langs, excel_file):
    if not os.path.exists(excel_file): return
    xls = pd.ExcelFile(excel_file)
    for table in xls.sheet_names:
        df = xls.parse(table)
        for lang in langs:
            nested = {}
            for _, row in df.iterrows():
                if pd.notna(row.get(lang)):
                    build_nested(row["value"], row[lang], nested)
            if nested:
                os.makedirs(os.path.join(lang_base_dir, lang), exist_ok=True)
                with open(os.path.join(lang_base_dir, lang, f"{table}.php"), "w") as f:
                    f.write("<?php\n\nreturn " + dict_to_php(nested) + ";\n")
    print("✅ PHP files updated")

def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("command", choices=["extract", "apply"])
    parser.add_argument("--langs", default=",".join(DEFAULT_LANGS))
    parser.add_argument("--src", default=",".join(DEFAULT_SOURCE_DIRS))
    parser.add_argument("--langd", default=DEFAULT_LANG_BASE_DIR)
    parser.add_argument("--excel", default=DEFAULT_EXCEL_FILE)
    args = parser.parse_args()

    if args.command == "extract":
        extract(args.src.split(","), args.langd, args.langs.split(","), args.excel)
    else:
        apply(args.langd, args.langs.split(","), args.excel)

if __name__ == "__main__":
    main()
