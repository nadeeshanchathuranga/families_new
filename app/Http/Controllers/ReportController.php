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
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

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

    // Reusable created_at window
    $applyCreatedWindow = function ($q) use ($from, $to) {
        if ($from && $to) {
            $q->whereBetween('created_at', [$from, $to]);
        } elseif ($from) {
            $q->where('created_at', '>=', $from);
        } elseif ($to) {
            $q->where('created_at', '<=', $to);
        }
    };

    // -------- Top Products (sold in range via Sale.created_at) --------
    if ($from || $to) {
        $productIds = SaleItem::whereHas('sale', function ($q) use ($applyCreatedWindow) {
                $applyCreatedWindow($q);
            })
            ->pluck('product_id')
            ->unique();

        $products = Product::whereIn('id', $productIds)
            ->orderBy('created_at', 'desc')
            ->get();
    } else {
        $products = Product::orderBy('created_at', 'desc')->get();
    }

    // -------- Sales (filter by created_at) --------
    $salesQuery = Sale::with(['saleItems.product.category', 'employee', 'customer']);

    if ($from || $to) {
        $applyCreatedWindow($salesQuery);
    }

    // For qty per product (respect same window through parent sale)
    $salesQuantitiesQuery = SaleItem::query()->whereHas('sale', function ($q) use ($applyCreatedWindow, $from, $to) {
        if ($from || $to) $applyCreatedWindow($q);
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

    // Category totals (from filtered sales)
    $categorySales = [];
    foreach ($sales as $sale) {
        foreach ($sale->saleItems as $item) {
            $categoryName = $item->product->category->name ?? 'No Category';
            $categorySales[$categoryName] = ($categorySales[$categoryName] ?? 0) + (float) $item->total_price;
        }
    }

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
    $expenseQuery = ExpenseNew::query();
    if ($from || $to) {
        $applyCreatedWindow($expenseQuery);
    }
    $expenses = $expenseQuery->orderBy('created_at', 'desc')->get();
    $totalExpenseAmount = (float) $expenses->sum('amount');
    $totalExpenseCount  = $expenses->count();

     $stockTransactionsReturn = StockTransaction::with('product')->where('transaction_type','Returned')->get();
    if ($startDateRaw && $endDateRaw) {
        $stockTransactionsReturn->whereBetween('transaction_date', [$startDateRaw, $endDateRaw]);
    }


    
  

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
         'stockTransactionsReturn'  => $stockTransactionsReturn,
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

        $totalQuantity = $products->sum('total_quantity');
        $remainingQuantity = $products->sum('stock_quantity');

        return response()->json([
            'products' => $products,
            'totalQuantity' => $totalQuantity,
            'remainingQuantity' => $remainingQuantity
        ]);
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
