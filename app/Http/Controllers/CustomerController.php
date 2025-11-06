<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
   public function index()
{
    $allcustomers = Customer::orderBy('id', 'desc')->get();
    $totalCustomers = $allcustomers->count();

    return Inertia::render('Customers/Index', [
        'allcustomers'   => $allcustomers,
        'totalCustomers' => $totalCustomers, // <-- pass the integer
    ]);
}


    public function create()
    {
        //
    }


    public function store(Request $request)
    {
        //
    }


    public function show(Customer $customer)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('customers')->ignore($customer->id),
            ],
            'phone' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('customers')->ignore($customer->id),
            ],
            'address' => 'nullable|string|max:255',
            'loyalty_points' => 'nullable|integer|max:255',
        ]);

        $customer->update($validated);

        return redirect()->route('customers.index')->banner('Customer updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {

        $customer->delete();
        return redirect()->route('customers.index')->banner('Customer Deleted successfully.');
    }
}
