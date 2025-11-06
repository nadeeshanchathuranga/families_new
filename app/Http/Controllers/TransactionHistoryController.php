<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Sale;
use App\Models\Cheque;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\CompanyInfo;
use App\Models\StockTransaction;
use Illuminate\Support\Facades\Gate;

class TransactionHistoryController extends Controller
{
    public function index()
{
    $allhistoryTransactions = Sale::with(['saleItems','saleItems.product','customer','user','cheque'])
        ->orderBy('id', 'desc')
        ->get();

    $companyInfo = CompanyInfo::all();

    return Inertia::render('TransactionHistory/Index', [
        'allhistoryTransactions' => $allhistoryTransactions,
        'totalhistoryTransactions' => $allhistoryTransactions->count(),
        'companyInfo' => $companyInfo,
    ]);
}

public function destroy(Request $request)
{
    $request->validate([
        'order_id' => 'required|string|exists:sales,order_id',
    ]);

    $sale = Sale::where('order_id', $request->order_id)->first();

    if (!$sale) {
        return response()->json(['message' => 'Sale not found'], 404);
    }

    // Retrieve sale items and restore product stock
    foreach ($sale->saleItems as $saleItem) {
        $product = Product::find($saleItem->product_id);
        if ($product) {
            $product->increment('stock_quantity', $saleItem->quantity);

            // Log the stock transaction
            StockTransaction::create([
                'product_id' => $saleItem->product_id,
                'transaction_type' => 'Deleted',
                'quantity' => $saleItem->quantity,
                'transaction_date' => now(),
                'supplier_id' => $product->supplier_id ?? null, // If applicable
                'reason' => null,
            ]);
        }
    }

    // Delete associated sale items
    SaleItem::where('sale_id', $sale->id)->delete();

    // Delete the sale record
    $sale->delete();

    return redirect()->route('transactionHistory.index')->banner('Record deleted successfully, and stock updated.');
}






public function markAsPaid(Request $request)
{


    $request->validate([
        'order_id' => 'required|exists:sales,order_id',
        'amount' => 'nullable|numeric',
    ]);

    $order = Sale::where('id', $request->id)->first();

    if (!$order) {
        return response()->json(['error' => 'Order not found'], 404);
    }

    $order->credit_bill = 0;



    // Optionally update cash if sent
    if ($request->filled('amount')) {
        $order->cash = $request->amount;
    }



    $order->save();

    return back()->with('success', 'Payment marked as completed.');
}



public function markAsPaidCheque(Request $request)
{
    $request->validate([
        'cheque_id' => 'required|exists:cheques,id',
    ]);

    $cheque = Cheque::findOrFail($request->cheque_id);
    $cheque->update(['status' => 'cleared']);

    return back()->with('success', 'Cheque marked as paid');
}






}
