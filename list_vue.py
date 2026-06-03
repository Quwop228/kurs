import os, sys
sys.stdout.reconfigure(encoding='utf-8')
base = r'D:\Docs\Codegpt\kurs\resources\js'
groups = {'Layouts': [], 'Pages': [], 'Components': []}
for root, dirs, files in os.walk(base):
    for f in files:
        if f.endswith('.vue'):
            full = os.path.join(root, f)
            rel = os.path.relpath(full, base).replace(os.sep, '/')
            lines = sum(1 for _ in open(full, encoding='utf-8'))
            top = rel.split('/')[0]
            key = top if top in groups else 'Pages'
            groups[key].append((rel, lines))
for g in ['Layouts', 'Components', 'Pages']:
    print(f'=== {g} ===')
    for rel, lines in sorted(groups[g]):
        print(f'  {lines:4d} lines  {rel}')
