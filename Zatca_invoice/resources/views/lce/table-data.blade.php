<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>{{ $table }}</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f4f4f4; }
        h1 { color: #333; }
        a.back { display: inline-block; margin-bottom: 16px; color: #4a90e2; text-decoration: none; }
        a.back:hover { text-decoration: underline; }
        .meta { margin-bottom: 16px; font-size: 13px; color: #666; }
        .meta span { margin-right: 16px; background: #fff; padding: 4px 10px; border-radius: 4px; border: 1px solid #ddd; }
        .wrap { overflow-x: auto; }
        table { border-collapse: collapse; width: 100%; background: #fff; font-size: 13px; }
        th { background: #4a90e2; color: #fff; padding: 8px 12px; text-align: left; white-space: nowrap; }
        td { padding: 6px 12px; border-bottom: 1px solid #eee; white-space: nowrap; max-width: 250px; overflow: hidden; text-overflow: ellipsis; }
        tr:hover td { background: #f0f7ff; }
        .empty { color: #999; font-style: italic; padding: 16px; }
    </style>
</head>
<body>
    <a class="back" href="{{ route('oracle.tables') }}">← All Tables</a>
    <h1>{{ $table }}</h1>

    <div class="meta">
        <span>{{ count($columns) }} columns</span>
        <span>{{ count($rows) }} rows shown (max 20)</span>
    </div>

    @if (count($rows) === 0)
        <p class="empty">No rows found in this table.</p>
    @else
        <div class="wrap">
            <table>
                <thead>
                    <tr>
                        @foreach ($columns as $col)
                            <th title="{{ $col->DATA_TYPE }}">{{ $col->COLUMN_NAME }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $row)
                        <tr>
                            @foreach ($columns as $col)
                                <td title="{{ $row->{$col->COLUMN_NAME} ?? '' }}">
                                    {{ $row->{$col->COLUMN_NAME} ?? '' }}
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</body>
</html>
