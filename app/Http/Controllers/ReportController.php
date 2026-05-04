<?php

namespace App\Http\Controllers;
use Carbon\Carbon;
use App\Models\Product;
use App\Models\Report;
use App\Models\Sale;
use App\Models\ExpenseNew;
use App\Models\SaleItem;
use App\Models\StockTransaction;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */




 public function index(Request $request)
{
    if (!Gate::allows('hasRole', ['Admin'])) {
        abort(403, 'Unauthorized');
    }

    // Dates (normalize to day bounds)
    $startDateRaw = $request->input('start_date');
    $endDateRaw   = $request->input('end_date');

    $from = $startDateRaw ? Carbon::parse($startDateRaw)->startOfDay() : null;
    $to   = $endDateRaw   ? Carbon::parse($endDateRaw)->endOfDay()     : null;

    // Reusable datetime window
    $applyWindow = function ($q, string $column = 'created_at') use ($from, $to) {
        if ($from && $to) {
            $q->whereBetween($column, [$from, $to]);
        } elseif ($from) {
            $q->where($column, '>=', $from);
        } elseif ($to) {
            $q->where($column, '<=', $to);
        }
    };

    // -------- Top Products (sold in range via Sale.created_at) --------
    $productBaseQuery = Product::query()->select([
        'id',
        'name',
        'stock_quantity',
        'selling_price',
        'cost_price',
        'discount',
        'created_at',
    ]);

    if ($from || $to) {
        $productIds = SaleItem::whereHas('sale', function ($q) use ($applyWindow) {
                $applyWindow($q, 'created_at');
            })
            ->distinct()
            ->pluck('product_id');

        $products = (clone $productBaseQuery)
            ->whereIn('id', $productIds)
            ->orderBy('created_at', 'desc')
            ->get();
    } else {
        $products = (clone $productBaseQuery)->orderBy('created_at', 'desc')->get();
    }

    // -------- Sales (filter by created_at) --------
    $salesQuery = Sale::query()
        ->select([
            'id',
            'sale_date',
            'order_id',
            'service_name',
            'is_service',
            'customer_id',
            'employee_id',
            'payment_method',
            'total_amount',
            'total_cost',
            'discount',
            'custom_discount',
            'custom_discount_type',
            'created_at',
        ])
        ->with([
            'saleItems' => function ($q) {
                $q->select(['id', 'sale_id', 'product_id', 'quantity', 'total_price']);
            },
            'saleItems.product' => function ($q) {
                $q->select(['id', 'name', 'category_id']);
            },
            'employee:id,name',
            'customer:id,name',
        ]);

    if ($from || $to) {
        $applyWindow($salesQuery, 'created_at');
    }

    // For qty per product (respect same window through parent sale)
    $salesQuantitiesQuery = SaleItem::query()->whereHas('sale', function ($q) use ($applyWindow, $from, $to) {
        if ($from || $to) $applyWindow($q, 'created_at');
    });

    $salesQuantities = $salesQuantitiesQuery
        ->select('product_id')
        ->selectRaw('SUM(quantity) as total_sales_qty')
        ->groupBy('product_id')
        ->get()
        ->keyBy('product_id');

    // Attach sales_qty to products
    $products->transform(function ($product) use ($salesQuantities) {
        $product->sales_qty = (float) ($salesQuantities->get($product->id)->total_sales_qty ?? 0);
        return $product;
    });

    $sales = $salesQuery->orderBy('created_at', 'desc')->get();

    // Helpers
    $customDiscountToLkr = function ($sale) {
        $gross = (float) ($sale->total_amount ?? 0);
        $val   = (float) ($sale->custom_discount ?? 0);
        $type  = $sale->custom_discount_type ?? 'fixed';
        return $type === 'percent' ? ($gross * $val / 100.0) : $val;
    };

    // Category totals (computed in SQL to avoid holding nested relations in memory)
    $categorySales = DB::table('sale_items')
        ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
        ->join('products', 'products.id', '=', 'sale_items.product_id')
        ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
        ->when($from || $to, function ($q) use ($applyWindow) {
            $applyWindow($q, 'sales.created_at');
        })
        ->selectRaw('COALESCE(categories.name, "No Category") as category_name')
        ->selectRaw('SUM(sale_items.total_price) as total_sales')
        ->groupBy('category_name')
        ->pluck('total_sales', 'category_name')
        ->map(fn ($v) => (float) $v)
        ->toArray();

    // Payment totals (gross)
    $paymentMethodTotals = $sales->groupBy('payment_method')->map(
        fn($g) => (float) $g->sum('total_amount')
    )->toArray();

    // Employee sales (NET)
    $employeeSalesSummary = [];
    foreach ($sales as $sale) {
        if (!$sale->employee) continue;
        $name = $sale->employee->name;
        $employeeSalesSummary[$name] ??= [
            'Employee Name' => $name,
            'Total Sales Amount' => 0,
        ];
        $gross       = (float) ($sale->total_amount ?? 0);
        $prodDisc    = (float) ($sale->discount ?? 0);
        $customDisc  = $customDiscountToLkr($sale);
        $employeeSalesSummary[$name]['Total Sales Amount'] += ($gross - $prodDisc - $customDisc);
    }

    // Overall stats
    $totalSaleAmount         = (float) $sales->sum('total_amount');
    $totalCost               = (float) $sales->sum('total_cost');
    $totalProductDiscountLkr = (float) $sales->sum('discount');
    $totalCustomDiscountLkr  = (float) $sales->reduce(fn($c, $s) => $c + $customDiscountToLkr($s), 0.0);
    $netProfit               = $totalSaleAmount - $totalCost - ($totalProductDiscountLkr + $totalCustomDiscountLkr);
    $totalTransactions       = $sales->count();
    $averageTransactionValue = $totalTransactions > 0 ? ($totalSaleAmount / $totalTransactions) : 0;

    // Distinct customers (same filter)
    $totalCustomer = (clone $salesQuery)->distinct('customer_id')->count('customer_id');

    // -------- Expenses (filter by created_at) --------
    $expenseQuery = ExpenseNew::query()->select(['id', 'title', 'amount', 'expense_date', 'created_at']);
    if ($from || $to) {
        $applyWindow($expenseQuery, 'created_at');
    }
    $expenses = $expenseQuery->orderBy('created_at', 'desc')->get();
    $totalExpenseAmount = (float) $expenses->sum('amount');
    $totalExpenseCount  = $expenses->count();

    $stockTransactionsReturnQuery = StockTransaction::query()
        ->select(['id', 'product_id', 'transaction_date', 'quantity', 'transaction_type'])
        ->with(['product:id,name,selling_price'])
        ->where('transaction_type', 'Returned');

    if ($from || $to) {
        // transaction_date is usually a DATE; compare against date strings
        if ($from && $to) {
            $stockTransactionsReturnQuery->whereBetween('transaction_date', [$from->toDateString(), $to->toDateString()]);
        } elseif ($from) {
            $stockTransactionsReturnQuery->where('transaction_date', '>=', $from->toDateString());
        } elseif ($to) {
            $stockTransactionsReturnQuery->where('transaction_date', '<=', $to->toDateString());
        }
    }

    $stockTransactionsReturn = $stockTransactionsReturnQuery
        ->orderBy('transaction_date', 'desc')
        ->get();

    // -------- Inventory Stock Summary --------
    // Compute stock summary via SQL joins/aggregates to keep memory low.
    $soldAgg = DB::table('sale_items')
        ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
        ->when($from || $to, function ($q) use ($applyWindow) {
            $applyWindow($q, 'sales.created_at');
        })
        ->select('sale_items.product_id')
        ->selectRaw('SUM(sale_items.quantity) as sold_qty')
        ->selectRaw('SUM(sale_items.total_price) as total_sales_value')
        ->groupBy('sale_items.product_id');

    $stockSummary = DB::table('products')
        ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
        ->leftJoinSub($soldAgg, 'sold', function ($join) {
            $join->on('products.id', '=', 'sold.product_id');
        })
        ->select([
            'products.id',
            'products.name',
            DB::raw('COALESCE(categories.name, "N/A") as category'),
            DB::raw('COALESCE(products.stock_quantity, 0) as available_stock'),
            DB::raw('COALESCE(products.cost_price, 0) as cost_price'),
            DB::raw('COALESCE(products.selling_price, 0) as selling_price'),
            DB::raw('(COALESCE(products.stock_quantity, 0) * COALESCE(products.cost_price, 0)) as stock_cost_value'),
            DB::raw('(COALESCE(products.stock_quantity, 0) * COALESCE(products.selling_price, 0)) as stock_selling_value'),
            DB::raw('COALESCE(sold.sold_qty, 0) as sold_qty'),
            DB::raw('COALESCE(sold.total_sales_value, 0) as total_sales_value'),
        ])
        ->orderBy('products.name')
        ->get();

    // Calculate totals for stock summary
    $stockSummaryTotals = [
        'total_products' => $stockSummary->count(),
        'total_available_stock' => $stockSummary->sum('available_stock'),
        'total_stock_cost_value' => $stockSummary->sum('stock_cost_value'),
        'total_stock_selling_value' => $stockSummary->sum('stock_selling_value'),
        'total_sold_qty' => $stockSummary->sum('sold_qty'),
        'total_sales_value' => $stockSummary->sum('total_sales_value'),
    ];



    return Inertia::render('Reports/Index', [
        'products'                  => $products,
        'sales'                     => $sales,

        'totalSaleAmount'           => round($totalSaleAmount, 2),
        'totalDiscountLkr'          => round($totalProductDiscountLkr, 2),
        'totalCustomDiscountLkr'    => round($totalCustomDiscountLkr, 2),
        'netProfit'                 => round($netProfit, 2),
        'totalTransactions'         => $totalTransactions,
        'averageTransactionValue'   => round($averageTransactionValue, 2),
        'totalCustomer'             => $totalCustomer,

        'startDate'                 => $startDateRaw,
        'endDate'                   => $endDateRaw,

        'categorySales'             => $categorySales,
        'employeeSalesSummary'      => $employeeSalesSummary,
        'paymentMethodTotals'       => $paymentMethodTotals,

        'expenses'                  => $expenses,
        'totalExpenseAmount'        => round($totalExpenseAmount, 2),
        'totalExpenseCount'         => $totalExpenseCount,
        'stockTransactionsReturn'   => $stockTransactionsReturn,

        // Stock Summary Data
        'stockSummary'              => $stockSummary,
        'stockSummaryTotals'        => $stockSummaryTotals,
    ]);
}










    public function searchByCode(Request $request)
    {
        $code = $request->input('code');

        if (!$code) {
            return response()->json([
                'products' => [],
                'totalQuantity' => 0,
                'remainingQuantity' => 0
            ]);
        }

        $products = Product::where('code', $code)
            ->select([
                'batch_no',
                'total_quantity',
                'stock_quantity',
                'expire_date',
                'purchase_date',
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        // Prefer current available stock stored in `stock_quantity`.
        // Do not rely on legacy `total_quantity` field.
        $totalQuantity = $products->sum('stock_quantity');
        $remainingQuantity = $products->sum('stock_quantity');

        return response()->json([
            'products' => $products,
            'totalQuantity' => $totalQuantity,
            'remainingQuantity' => $remainingQuantity
        ]);
    }

    /**
     * Download Stock Summary Report as PDF
     */
    public function downloadStockSummaryPdf(Request $request)
    {
        if (!Gate::allows('hasRole', ['Admin'])) {
            abort(403, 'Unauthorized');
        }

        // DOMPDF can be memory-hungry for large tables; raise only for this action.
        @ini_set('memory_limit', '1024M');
        @set_time_limit(120);

        $startDateRaw = $request->input('start_date');
        $endDateRaw   = $request->input('end_date');

        $from = $startDateRaw ? Carbon::parse($startDateRaw)->startOfDay() : null;
        $to   = $endDateRaw   ? Carbon::parse($endDateRaw)->endOfDay()     : null;

        $applyWindow = function ($q, string $column = 'created_at') use ($from, $to) {
            if ($from && $to) {
                $q->whereBetween($column, [$from, $to]);
            } elseif ($from) {
                $q->where($column, '>=', $from);
            } elseif ($to) {
                $q->where($column, '<=', $to);
            }
        };

        $soldAgg = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->when($from || $to, function ($q) use ($applyWindow) {
                $applyWindow($q, 'sales.created_at');
            })
            ->select('sale_items.product_id')
            ->selectRaw('SUM(sale_items.quantity) as sold_qty')
            ->selectRaw('SUM(sale_items.total_price) as total_sales_value')
            ->groupBy('sale_items.product_id');

        $stockSummary = DB::table('products')
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->leftJoinSub($soldAgg, 'sold', function ($join) {
                $join->on('products.id', '=', 'sold.product_id');
            })
            ->select([
                'products.id',
                'products.name',
                DB::raw('COALESCE(categories.name, "N/A") as category'),
                DB::raw('COALESCE(products.stock_quantity, 0) as available_stock'),
                DB::raw('COALESCE(products.cost_price, 0) as cost_price'),
                DB::raw('COALESCE(products.selling_price, 0) as selling_price'),
                DB::raw('(COALESCE(products.stock_quantity, 0) * COALESCE(products.cost_price, 0)) as stock_cost_value'),
                DB::raw('(COALESCE(products.stock_quantity, 0) * COALESCE(products.selling_price, 0)) as stock_selling_value'),
                DB::raw('COALESCE(sold.sold_qty, 0) as sold_qty'),
                DB::raw('COALESCE(sold.total_sales_value, 0) as total_sales_value'),
            ])
            ->orderBy('products.name')
            ->get();

        $totals = (object) [
            'total_products' => $stockSummary->count(),
            'total_available_stock' => $stockSummary->sum('available_stock'),
            'total_stock_cost_value' => $stockSummary->sum('stock_cost_value'),
            'total_stock_selling_value' => $stockSummary->sum('stock_selling_value'),
            'total_sold_qty' => $stockSummary->sum('sold_qty'),
            'total_sales_value' => $stockSummary->sum('total_sales_value'),
        ];

        // Date range label
        $rangeLabel = 'All Time';
        if ($startDateRaw && $endDateRaw) {
            $rangeLabel = Carbon::parse($startDateRaw)->format('Y-m-d') . ' to ' . Carbon::parse($endDateRaw)->format('Y-m-d');
        } elseif ($startDateRaw) {
            $rangeLabel = 'From ' . Carbon::parse($startDateRaw)->format('Y-m-d');
        } elseif ($endDateRaw) {
            $rangeLabel = 'Until ' . Carbon::parse($endDateRaw)->format('Y-m-d');
        }

        $pdf = Pdf::loadView('pdf.stock-summary', [
            'title' => 'Stock Summary Report',
            'stockSummary' => $stockSummary,
            'totals' => $totals,
            'rangeLabel' => $rangeLabel,
            'generatedAt' => Carbon::now()->format('Y-m-d H:i:s'),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('Stock_Summary_Report_' . Carbon::now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Report $report)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Report $report)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Report $report)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Report $report)
    {
        //
    }
}
