<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Oracle Tables</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; padding: 30px; background: #f0f2f5; }
        h1 { color: #1a1a2e; margin-bottom: 6px; font-size: 22px; }
        .subtitle { color: #888; font-size: 13px; margin-bottom: 24px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(190px, 1fr)); gap: 10px; margin-bottom: 30px; }
        .card { background: #fff; border: 1px solid #e0e0e0; border-radius: 8px; padding: 12px 14px; text-decoration: none; color: #333; font-size: 13px; font-weight: 500; transition: all 0.15s; box-shadow: 0 1px 3px rgba(0,0,0,.04); }
        .card:hover { background: #eef3ff; border-color: #4a90e2; color: #2563eb; transform: translateY(-1px); box-shadow: 0 4px 10px rgba(74,144,226,.15); }

        /* Pagination */
        .pagination-wrap { display: flex; align-items: center; justify-content: center; gap: 6px; margin-top: 10px; }
        .pagination-wrap a,
        .pagination-wrap span { display: inline-flex; align-items: center; justify-content: center; min-width: 36px; height: 36px; padding: 0 10px; border-radius: 6px; font-size: 13px; font-weight: 500; text-decoration: none; border: 1px solid #ddd; background: #fff; color: #444; transition: all 0.15s; }
        .pagination-wrap a:hover { background: #4a90e2; border-color: #4a90e2; color: #fff; }
        .pagination-wrap span.active { background: #4a90e2; border-color: #4a90e2; color: #fff; }
        .pagination-wrap span.disabled { color: #bbb; cursor: default; }
        nav[role="navigation"] { margin-top: 24px; }
        nav[role="navigation"] > div:first-child { text-align: center; color: #888; font-size: 12px; margin-bottom: 10px; }
        nav[role="navigation"] > div:last-child { display: flex; align-items: center; justify-content: center; gap: 4px; flex-wrap: wrap; }
        nav[role="navigation"] span[aria-current] span,
        nav[role="navigation"] span[aria-current] { display: inline-flex; align-items: center; justify-content: center; min-width: 36px; height: 36px; padding: 0 10px; border-radius: 6px; font-size: 13px; font-weight: 600; background: #4a90e2; border: 1px solid #4a90e2; color: #fff; }
        nav[role="navigation"] span.relative span { display: inline-flex; align-items: center; justify-content: center; min-width: 36px; height: 36px; padding: 0 10px; border-radius: 6px; font-size: 13px; background: #fff; border: 1px solid #ddd; color: #bbb; }
        nav[role="navigation"] a { display: inline-flex; align-items: center; justify-content: center; min-width: 36px; height: 36px; padding: 0 10px; border-radius: 6px; font-size: 13px; font-weight: 500; text-decoration: none; background: #fff; border: 1px solid #ddd; color: #444; transition: all 0.15s; }
        nav[role="navigation"] a:hover { background: #4a90e2; border-color: #4a90e2; color: #fff; }
    </style>
</head>
<body>
    <h1>Oracle DB Tables</h1>
    <p class="subtitle">{{ $tables->total() }} tables total &mdash; page {{ $tables->currentPage() }} of {{ $tables->lastPage() }}</p>

    <div class="grid">
        @foreach ($tables as $t)
            <a class="card" href="{{ route('oracle.table.show', $t->TABLE_NAME) }}">
                {{ $t->TABLE_NAME }}
            </a>
        @endforeach
    </div>

    {{ $tables->links() }}
</body>
</html>
