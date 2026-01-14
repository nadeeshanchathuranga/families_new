<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'Stock Summary Report' }}</title>
    <style>
        * {
            font-family: DejaVu Sans, sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-size: 10px;
            color: #111;
            padding: 15px;
        }
        .header {
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .title {
            font-size: 22px;
            font-weight: bold;
            color: #1a365d;
        }
        .meta {
            margin-top: 6px;
            color: #555;
            font-size: 11px;
        }

        /* Summary Cards */
        .summary-cards {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .summary-card {
            display: table-cell;
            width: 16.66%;
            padding: 8px;
            text-align: center;
            border: 1px solid #ddd;
            background: #f8fafc;
        }
        .summary-card .label {
            font-size: 9px;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        .summary-card .value {
            font-size: 14px;
            font-weight: bold;
            color: #1a365d;
        }

        /* Main Table */
        table.main-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table.main-table th,
        table.main-table td {
            border: 1px solid #ddd;
            padding: 6px 8px;
            font-size: 9px;
        }
        table.main-table th {
            background: linear-gradient(to bottom, #2d3748, #1a202c);
            color: white;
            text-align: left;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8px;
        }
        table.main-table th.center,
        table.main-table td.center {
            text-align: center;
        }
        table.main-table th.right,
        table.main-table td.right {
            text-align: right;
        }
        table.main-table tbody tr:nth-child(even) {
            background: #f7fafc;
        }
        table.main-table tbody tr:hover {
            background: #edf2f7;
        }
        table.main-table tfoot td {
            font-weight: bold;
            background: #e2e8f0;
            border-top: 2px solid #333;
        }

        /* Section Headers */
        .section-header {
            background: #2d3748;
            color: white;
            padding: 4px 8px;
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
        }
        .stock-section {
            background: #2b6cb0;
        }
        .sales-section {
            background: #38a169;
        }

        .muted {
            color: #777;
        }
        .text-green {
            color: #38a169;
        }
        .text-blue {
            color: #2b6cb0;
        }

        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">{{ $title ?? 'Stock Summary Report' }}</div>
        <div class="meta">
            Date Range: <strong>{{ $rangeLabel }}</strong> | Generated: {{ $generatedAt }}
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="summary-cards">
        <div class="summary-card">
            <div class="label">Total Products</div>
            <div class="value">{{ number_format($totals->total_products) }}</div>
        </div>
        <div class="summary-card">
            <div class="label">Available Stock</div>
            <div class="value">{{ number_format($totals->total_available_stock) }}</div>
        </div>
        <div class="summary-card">
            <div class="label">Stock Cost Value</div>
            <div class="value">{{ number_format($totals->total_stock_cost_value, 2) }}</div>
        </div>
        <div class="summary-card">
            <div class="label">Stock Selling Value</div>
            <div class="value">{{ number_format($totals->total_stock_selling_value, 2) }}</div>
        </div>
        <div class="summary-card">
            <div class="label">Total Sold Qty</div>
            <div class="value text-green">{{ number_format($totals->total_sold_qty) }}</div>
        </div>
        <div class="summary-card">
            <div class="label">Total Sales Value</div>
            <div class="value text-green">{{ number_format($totals->total_sales_value, 2) }}</div>
        </div>
    </div>

    <!-- Main Table -->
    <table class="main-table">
        <thead>
            <tr>
                <th style="width: 30px;" class="center">#</th>
                <th style="width: 180px;">Product Name</th>
                <th style="width: 100px;">Category</th>
                <th class="center" colspan="4" style="background: #2b6cb0;">
                    Available Stock
                </th>
                <th class="center" colspan="2" style="background: #38a169;">
                    Sold Products
                </th>
            </tr>
            <tr>
                <th class="center"></th>
                <th></th>
                <th></th>
                <th class="right" style="width: 60px; background: #3182ce;">Qty</th>
                <th class="right" style="width: 80px; background: #3182ce;">Cost Price</th>
                <th class="right" style="width: 80px; background: #3182ce;">Selling Price</th>
                <th class="right" style="width: 90px; background: #3182ce;">Total Value</th>
                <th class="right" style="width: 60px; background: #48bb78;">Qty</th>
                <th class="right" style="width: 90px; background: #48bb78;">Total Sales</th>
            </tr>
        </thead>
        <tbody>
        @forelse($stockSummary as $i => $item)
            <tr>
                <td class="center">{{ $i + 1 }}</td>
                <td>{{ $item->name }}</td>
                <td>{{ $item->category }}</td>
                <td class="right">{{ number_format($item->available_stock) }}</td>
                <td class="right">{{ number_format($item->cost_price, 2) }}</td>
                <td class="right">{{ number_format($item->selling_price, 2) }}</td>
                <td class="right">{{ number_format($item->stock_selling_value, 2) }}</td>
                <td class="right text-green">{{ number_format($item->sold_qty) }}</td>
                <td class="right text-green">{{ number_format($item->total_sales_value, 2) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="9" class="muted center">No products found.</td>
            </tr>
        @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="right"><strong>TOTALS:</strong></td>
                <td class="right">{{ number_format($totals->total_available_stock) }}</td>
                <td class="right">—</td>
                <td class="right">—</td>
                <td class="right">{{ number_format($totals->total_stock_selling_value, 2) }}</td>
                <td class="right text-green">{{ number_format($totals->total_sold_qty) }}</td>
                <td class="right text-green">{{ number_format($totals->total_sales_value, 2) }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
