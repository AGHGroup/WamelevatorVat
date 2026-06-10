<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Oracle Tables</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f4f4f4; }
        h1 { color: #333; }
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 10px; margin-top: 20px; }
        .card { background: #fff; border: 1px solid #ddd; border-radius: 6px; padding: 12px; text-decoration: none; color: #333; font-size: 14px; transition: background 0.2s; }
        .card:hover { background: #e8f0fe; border-color: #4a90e2; color: #4a90e2; }
        .count { color: #888; font-size: 12px; margin-top: 4px; }
    </style>
</head>
<body>
    <h1>Oracle DB — {{ count($tables) }} Tables</h1>
    <div class="grid">
        @foreach ($tables as $t)
            <a class="card" href="{{ route('oracle.table.show', $t->TABLE_NAME) }}">
                {{ $t->TABLE_NAME }}
            </a>
        @endforeach
    </div>
</body>
</html>
