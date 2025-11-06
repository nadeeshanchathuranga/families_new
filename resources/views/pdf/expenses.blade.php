<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'Expenses Report' }}</title>
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { font-size: 12px; color: #111; }
        .header { margin-bottom: 14px; }
        .title { font-size: 20px; font-weight: bold; }
        .meta { margin-top: 4px; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; }
        th { background: #f2f2f2; text-align: left; }
        tfoot td { font-weight: bold; }
        .right { text-align: right; }
        .muted { color: #777; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">{{ $title ?? 'Expenses Report' }}</div>
        <div class="meta">
            Range: <strong>{{ $rangeLabel }}</strong><br>
            Generated: {{ $generatedAt }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 40px;">#</th>
                <th>Title</th>
                <th class="right" style="width: 120px;">Amount</th>
                <th style="width: 120px;">Expense Date</th>
                <th>Note</th>
            </tr>
        </thead>
        <tbody>
        @forelse($expenses as $i => $e)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $e->title }}</td>
                <td class="right">{{ number_format((float)$e->amount, 2) }}</td>
                <td>{{ optional($e->expense_date)->format('Y-m-d') }}</td>
                <td>{{ $e->note ?: '—' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="muted">No expenses found for the selected range.</td>
            </tr>
        @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2">Total</td>
                <td class="right">{{ number_format((float)$total, 2) }}</td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
