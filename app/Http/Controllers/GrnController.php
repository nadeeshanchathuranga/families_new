<?php

namespace App\Http\Controllers;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use App\Models\Grn;
use App\Models\Product;


class GrnController extends Controller
{
    public function index()
    {
        if (!Gate::allows('hasRole', ['Admin','Manager'])) {
            abort(403, 'Unauthorized');
        }

        $allgrns = Grn::with('products')->orderBy('id', 'desc')->get();

        return Inertia::render('Grn/Index', [
            'allgrns' => $allgrns,
            'totalGrns' => $allgrns->count(),
        ]);
    }



    public function store(Request $request)
    {
        $request->validate([
            'grn_number' => 'required|string|unique:grns,grn_number',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string',
            'items.*.stock_quantity' => 'required|numeric',
            'items.*.code' => 'required|string',
            'items.*.cost_price' => 'required|numeric',
            'items.*.barcode' => 'nullable|string',
            'items.*.batch_no' => 'required|string',
            'items.*.expired_date' => 'nullable|date',
        ]);


        // Create the GRN record
        $grn = Grn::create([
            'grn_number' => $request->grn_number,
            'status' => 'pending',
        ]);

        // Loop through items and create product entries
        foreach ($request->items as $item) {
            Product::create([
                'name' => $item['name'],
                'code' => $item['code'],
                'stock_quantity' => $item['stock_quantity'],
                'total_quantity' => $item['stock_quantity'],
                'cost_price' => $item['cost_price'],
                'barcode' => $item['barcode'] ?? null,
                'batch_no' => $item['batch_no'],
                'expire_date' => $item['expired_date'] ?? null,
                'purchase_date' => now(),
                'grn_id' => $grn->id,
            ]);
        }

        return redirect()->route('grns.index')->banner('Grn created successfully !');
    }

    public function getNextBatchNo(Request $request)
    {
        $code = $request->input('code');

        $latestProduct = Product::where('code', $code)
            ->orderBy('created_at', 'desc')
            ->first();

        $latestBatch = Product::where('code', $code)
            ->orderBy('created_at', 'desc')
            ->pluck('batch_no')
            ->filter()
            ->map(function ($batch) {
                if (preg_match('/batch(\d+)/i', $batch, $matches)) {
                    return (int)$matches[1];
                }
                return 0;
            })
            ->max();

        $nextBatch = 'batch' . (($latestBatch ?? 0) + 1);

        return response()->json([
            'next_batch_no' => $nextBatch,
            'name' => $latestProduct?->name ?? null,
        ]);
    }

    public function update(Request $request, Grn $grn)
    {
        $request->validate([
            'grn_number' => 'required|string|unique:grns,grn_number,' . $grn->id,
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string',
            'items.*.stock_quantity' => 'required|numeric',
            'items.*.code' => 'required|string',
            'items.*.cost_price' => 'required|numeric',
            'items.*.barcode' => 'nullable|string',
            'items.*.batch_no' => 'required|string',
            'items.*.expire_date' => 'nullable|date',
        ]);

        // Update the GRN main fields
        $grn->update([
            'grn_number' => $request->grn_number,
        ]);

        foreach ($request->items as $item) {
            // Find the product by its ID (assuming you are updating existing products)
            $product = Product::where('code', $item['code'])->where('grn_id', $grn->id)->first();

            if ($product) {
                // Update the product if it exists
                $product->update([
                    'name' => $item['name'],
                    'stock_quantity' => $item['stock_quantity'],
                    'total_quantity' => $item['stock_quantity'],
                    'cost_price' => $item['cost_price'],
                    'barcode' => $item['barcode'] ?? null,
                    'batch_no' => $item['batch_no'],
                    'expire_date' => $item['expire_date'] ?? null,
                    'purchase_date' => now(),
                    'grn_id' => $grn->id,
                ]);
            } else {
                // If the product doesn't exist, create a new one (optional)
                Product::create([
                    'name' => $item['name'],
                    'stock_quantity' => $item['stock_quantity'],
                    'total_quantity' => $item['stock_quantity'],
                    'cost_price' => $item['cost_price'],
                    'barcode' => $item['barcode'] ?? null,
                    'batch_no' => $item['batch_no'],
                    'expire_date' => $item['expire_date'] ?? null,
                    'purchase_date' => now(),
                    'grn_id' => $grn->id,
                ]);
            }
        }


        return redirect()->route('grns.index')->banner('GRN updated successfully!');
    }


    public function destroy(Grn $grn)
    {
        $grn->products()->delete();

        $grn->delete();

        return redirect()->route('grns.index')->with('success', 'GRN and its products deleted!');
    }




}
