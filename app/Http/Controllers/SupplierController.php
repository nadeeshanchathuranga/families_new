<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\SupplierNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;



class SupplierController extends Controller
{

    public function index()
    {

  



        $allsuppliers = Supplier::with([
    'numbers' => function ($query) {
        $query->orderBy('id', 'desc');
    },
    'products' => function ($query) {
        $query->select('id', 'supplier_id', 'name', 'cost_price', 'total_quantity');
    }
])
->orderBy('id', 'desc')
->get();



        return Inertia::render('Suppliers/Index', [
            'allsuppliers' => $allsuppliers,
            'totalSuppliers' => $allsuppliers->count()
        ]);
    }



public function store(Request $request)
{
    if (!Gate::allows('hasRole', ['Admin'])) {
        abort(403, 'Unauthorized');
    }

    $validated = $request->validate([
        'name' => 'required|string|max:191|regex:/^[a-zA-Z\s]+$/',
        'email' => 'required|email|regex:/^[\w\.-]+@[a-zA-Z0-9\.-]+\.[a-zA-Z]{2,6}$/|max:255|unique:suppliers,email',
        'address' => 'required|string|max:500',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:1024',
        'phone_numbers' => 'required|array|min:1',
        'phone_numbers.*.name' => 'required|string|max:100',
        'phone_numbers.*.phone' => ['required', 'regex:/^[0-9]{10,15}$/'],
    ]);

    // Handle image upload
    if ($request->hasFile('image')) {
        $fileExtension = $request->file('image')->getClientOriginalExtension();
        $fileName = 'supplier_' . date("YmdHis") . '.' . $fileExtension;
        $path = $request->file('image')->storeAs('suppliers', $fileName, 'public');
        $validated['image'] = 'storage/' . $path;
    }

    // Create the supplier
    $supplier = Supplier::create($validated);

    // Save phone numbers
    foreach ($validated['phone_numbers'] as $contact) {
        SupplierNumber::create([
            'supplier_id' => $supplier->id,
            'name' => $contact['name'],
            'number' => $contact['phone'],
        ]);
    }

return redirect()->route('suppliers.index')->banner('Supplier created successfully.');
}





public function update(Request $request, Supplier $supplier)
{
    if (!Gate::allows('hasRole', ['Admin'])) {
        abort(403, 'Unauthorized');
    }

    // Validate request
    $validated = $request->validate([
        'name' => 'nullable|string|max:191',
        'email' => 'nullable|email|max:255|unique:suppliers,email,' . $supplier->id,
        'address' => 'nullable|string|max:500',
        'image' => 'nullable|mimes:jpeg,png,jpg,gif|max:2048',
        'phone_numbers' => 'nullable|array',
        'phone_numbers.*.name' => 'required_with:phone_numbers.*.phone|string|max:100',
        'phone_numbers.*.phone' => 'required_with:phone_numbers.*.name|regex:/^[0-9]{10,15}$/',
    ]);

    // Handle image upload
    if ($request->hasFile('image')) {
        if ($supplier->image && Storage::disk('public')->exists(str_replace('storage/', '', $supplier->image))) {
            Storage::disk('public')->delete(str_replace('storage/', '', $supplier->image));
        }

        $fileExtension = $request->file('image')->getClientOriginalExtension();
        $fileName = 'supplier_' . date("YmdHis") . '.' . $fileExtension;
        $path = $request->file('image')->storeAs('suppliers', $fileName, 'public');
        $validated['image'] = 'storage/' . $path;
    } else {
        $validated['image'] = $supplier->image;
    }

    // Update supplier
    $supplier->update($validated);

    // Update phone contacts in a transaction
    DB::transaction(function () use ($supplier, $request) {
        // Remove all existing numbers
        SupplierNumber::where('supplier_id', $supplier->id)->delete();

        // Reinsert new phone_numbers
        if ($request->has('phone_numbers')) {
            foreach ($request->phone_numbers as $contact) {
                if (!empty($contact['name']) && !empty($contact['phone'])) {
                    SupplierNumber::create([
                        'supplier_id' => $supplier->id,
                        'name' => $contact['name'],
                        'number' => $contact['phone'],
                    ]);
                }
            }
        }
    });

    return redirect()->route('suppliers.index')->banner('Supplier updated successfully.');
}




public function destroy(Supplier $supplier)
{
    if (!Gate::allows('hasRole', ['Admin'])) {
        abort(403, 'Unauthorized');
    }

    // Delete related phone numbers manually
    SupplierNumber::where('supplier_id', $supplier->id)->delete();

    // Delete supplier image if exists
    if ($supplier->image && Storage::disk('public')->exists(str_replace('storage/', '', $supplier->image))) {
        Storage::disk('public')->delete(str_replace('storage/', '', $supplier->image));
    }

    // Delete the supplier
    $supplier->delete();

    return redirect()->route('suppliers.index')->banner('Supplier deleted successfully.');
}






 public function supplierPayment(Request $request)
{
    $validated = $request->validate([
        'supplier_id' => 'required|exists:suppliers,id',
        'pay'         => 'required|numeric|min:0',
        'total'       => 'required|numeric|min:0',
        'balance'     => 'required|numeric',
    ]);

    $supplier = Supplier::findOrFail($validated['supplier_id']);

    // If no previous payments
    if ($supplier->total_cost == 0 && $supplier->pay == 0 && $supplier->balance == 0) {
        // First payment
        $supplier->total_cost = $validated['total'];
        $supplier->pay        = $validated['pay'];
        $supplier->balance    = $validated['balance'];
    } else {

        $supplier->pay        += $validated['pay'];
        $supplier->balance     = $validated['balance'];
    }

    $supplier->save();

    return response()->json([
        'message' => 'Payment recorded successfully',
        'supplier' => $supplier
    ]);
}








}
