<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Color;
use App\Models\Cheque;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\ReturnItem;
use App\Models\ReturnReason;
use App\Models\Size;
use App\Models\StockTransaction;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PosController extends Controller
{
    public function index(Request $request)
    {
        if (!Gate::allows('hasRole', ['Admin', 'Cashier'])) {
            abort(403, 'Unauthorized');
        }

        $allcategories = Category::with('parent')->get()->map(function ($category) {
            $category->hierarchy_string = $category->hierarchy_string; // Access it
            return $category;
        });
        $sales = Sale::with('customer','employee')->get();
        $saleItems  = SaleItem::with('product')->orderBy('created_at', 'desc')->get();


        $returnReasons = ReturnReason::orderBy('created_at', 'desc')->get();
        $colors = Color::orderBy('created_at', 'desc')->get();
        $sizes = Size::orderBy('created_at', 'desc')->get();
        $allemployee = Employee::orderBy('created_at', 'desc')->get();

        // Render the page for GET requests
        return Inertia::render('Pos/Index', [
            'product' => null,
            'error' => null,
            'loggedInUser' => Auth::user(),
            'allcategories' => $allcategories,
            'allemployee' => $allemployee,
            'colors' => $colors,
            'returnReasons' => $returnReasons,
            'sizes' => $sizes,
               'sales'=> $sales,
            'saleItems' => $saleItems,
        ]);
    }

    public function getProduct(Request $request)
    {
        if (!Gate::allows('hasRole', ['Admin', 'Cashier'])) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'barcode' => 'required',
        ]);

        $barcodeOrCode = $request->barcode;

        // 🔁 First, try to match as a code (to enable FIFO)
        $product = Product::where('code', $barcodeOrCode)
            ->where('stock_quantity', '>', 0)
            ->orderBy('created_at', 'asc') // FIFO
            ->first();


        // 🆘 Fallback: match as unique barcode (not FIFO)
        if (!$product) {
            $product = Product::where('barcode', $barcodeOrCode)
                ->where('stock_quantity', '>', 0)
                ->first();
        }

        return response()->json([
            'product' => $product,
            'error' => $product ? null : 'Product not found',
        ]);
    }



public function serviceSubmit(Request $request)
{


    // Validate payload coming from the modal
    $data = $request->validate([
        'service_name'   => ['required', 'string'],
        'total_cost'     => ['required', 'numeric', 'min:0'],

    ]);

    try {
        $now = now();

        $sale = DB::transaction(function () use ($request, $data, $now) {
            return Sale::create([
                // Identifiers (fallbacks provided so NOT NULL columns won’t fail)
                'customer_id'   => $request->input('customer_id'),                 // or null
                'employee_id'   => $request->input('employee_id'),                 // or null
                'user_id'       => auth()->id(),
                'order_id'      => null,

                // Money
                'total_amount'  => $data['total_cost'],                            // keep in sync if your schema needs both
                'discount'      => 0,
                'payment_method'=> $request->input('payment_method', 'cash'),     // default to cash
                'sale_date'     => $now,
                'total_cost'    => 0,
                'cash'          => 0,  // assume fully paid unless specified
                'custom_discount'       => 0,
                'custom_discount_type'  => 'fixed',

                // Return / wholesale / guide defaults
                'is_return_bill'=> 0,
                'return_date'   => null,
                'is_whole'      => 0,
                'guide_name'    => null,
                'guide_comi'    => 0,
                'guide_cash'    => 0,
                'guide_pending' => 0,
                'total_to_include_guide' => 0,
                'credit_bill'   => 0,
                'cheque_id'     => null,

                // Service fields
                'service_name'  => $data['service_name'], // TEXT column
                'is_service'    => 1,
            ]);
        });

        return response()->json([
            'success'       => true,
            'message'       => 'Service saved successfully',
            'sale_id'       => $sale->id,
            'service_name'  => $sale->service_name,
            'total_cost'    => (float)$sale->total_cost,
        ], 201);

    } catch (\Throwable $e) {
        report($e);
        return response()->json([
            'success' => false,
            'message' => 'Failed to save service',
        ], 500);
    }
}


    public function getCoupon(Request $request)
    {
        $request->validate(
            ['code' => 'required|string'],
            ['code.required' => 'The coupon code missing.', 'code.string' => 'The coupon code must be a valid string.']
        );

        $coupon = Coupon::where('code', $request->code)->first();

        if (!$coupon) {
            return response()->json(['error' => 'Invalid coupon code.']);
        }

        if (!$coupon->isValid()) {
            return response()->json(['error' => 'Coupon is expired or has been fully used.']);
        }

        return response()->json(['success' => 'Coupon applied successfully.', 'coupon' => $coupon]);
    }






public function submit(Request $request)
{

    
    // Normalize UI label "online" -> backend "cheque" (comment this line if your UI already sends 'cheque')
    if ($request->input('paymentMethod') === 'online') {
        $request->merge(['paymentMethod' => 'cheque']);
    }

    // ---- VALIDATION ----
    $validated = $request->validate([
        'products' => 'required|array|min:1',
        'products.*.id' => 'required|integer|exists:products,id',
        'products.*.quantity' => 'required|numeric|min:0.01',
        // Optional/overrides coming from UI
        'products.*.unit_price' => 'nullable|numeric|min:0',
        'products.*.cost_price' => 'nullable|numeric|min:0',

        'userId'   => 'required|integer|exists:users,id',
        'orderid'  => 'required|string|max:50',

        // accept cheque in addition to cash/card
        'paymentMethod' => 'required|string|in:cash,card,cheque',
        'cash' => 'nullable|numeric|min:0',

        'isWholesale' => 'nullable|boolean',

        'custom_discount'      => 'nullable|numeric|min:0',
        'custom_discount_type' => 'nullable|string|in:fixed,percent',

        'appliedCoupon' => 'nullable|array',
        'appliedCoupon.discount' => 'nullable|numeric|min:0',

        'credit_bill' => 'nullable|boolean',

        'customer' => 'nullable|array',
        'customer.name'          => 'nullable|string|max:255',
        'customer.email'         => 'nullable|email|max:255',
        'customer.contactNumber' => 'nullable|string|max:30',
        'customer.countryCode'   => 'nullable|string|max:5',
        'customer.address'       => 'nullable|string|max:255',

        'employee_id' => 'nullable|integer|exists:employees,id',

        // Cheque payload comes inside "online" (keep the key for compatibility)
        // These fields are required ONLY when paymentMethod is 'cheque'
        'online' => 'nullable|array',
        'online.cheque_number' => 'required_if:paymentMethod,cheque|string|max:120',
        'online.bank_name'     => 'required_if:paymentMethod,cheque|string|max:120',
        'online.cheque_date'   => 'required_if:paymentMethod,cheque|date',
        'online.amount'        => 'required_if:paymentMethod,cheque|numeric|min:0.01',
        'online.notes'         => 'nullable|string|max:500',
    ], [
        'online.cheque_number.required_if' => 'Cheque number is required for cheque payments.',
        'online.bank_name.required_if'     => 'Bank name is required for cheque payments.',
        'online.cheque_date.required_if'   => 'Cheque date is required for cheque payments.',
        'online.amount.required_if'        => 'Cheque amount is required for cheque payments.',
    ]);

    $products = $validated['products'];
    
        $products = $request->input('products');
      $returnItems = $request->input('return_items', []);
    $isWhole  = (bool) ($validated['isWholesale'] ?? false);

    // ---- PRICE RESOLUTION & STOCK CHECKS ----
    $totalAmount = 0.0;
    $totalCost   = 0.0;

    foreach ($products as &$p) {
        /** @var \App\Models\Product $productModel */
        $productModel = Product::find($p['id']);
        if (!$productModel) {
            return response()->json(['message' => "Product with ID {$p['id']} not found"], 404);
        }

        $qty = (float)($p['quantity'] ?? 0);

        // Stock availability
        if ($productModel->stock_quantity < $qty) {
            return response()->json([
                'message' => "Insufficient stock for {$productModel->name}. Available: {$productModel->stock_quantity}, Requested: {$qty}"
            ], 423);
        }

        // Expiry check
        if ($productModel->expire_date && now()->greaterThan($productModel->expire_date)) {
            $expireDate = \Carbon\Carbon::parse($productModel->expire_date)->format('Y-m-d');
            return response()->json([
                'message' => "Product '{$productModel->name}' expired on {$expireDate}."
            ], 423);
        }

        // Resolve unit price (UI override > whole/sell fallback)
        $unitPrice = isset($p['unit_price']) && $p['unit_price'] > 0
            ? (float)$p['unit_price']
            : (float)($isWhole
                ? ($productModel->whole_price ?? $productModel->selling_price ?? 0)
                : ($productModel->selling_price ?? $productModel->whole_price ?? 0));

        $costPrice = isset($p['cost_price']) && $p['cost_price'] > 0
            ? (float)$p['cost_price']
            : (float)($productModel->cost_price ?? 0);

        $p['__resolved_unit_price'] = $unitPrice;
        $p['__resolved_cost_price'] = $costPrice;

        $totalAmount += $qty * $unitPrice;
        $totalCost   += $qty * $costPrice;
    }
    unset($p);

    // ---- DISCOUNTS ----
    // Per-product (if UI marks apply_discount with discount + discounted_price)
    $productDiscounts = 0.0;
    foreach ($products as $p) {
        if (!empty($p['apply_discount']) && isset($p['discount'], $p['discounted_price'])) {
            $discount = (float)($p['discount'] ?? 0);
            if ($discount > 0) {
                $originalPrice   = (float)($p['unit_price'] ?? $p['__resolved_unit_price']);
                $discountedPrice = (float)$p['discounted_price'];
                $productDiscounts += ($originalPrice - $discountedPrice) * (float)$p['quantity'];
            }
        }
    }

     $totalReturnAmount = collect($returnItems)->reduce(function ($carry, $item) {
            return $carry + ($item['quantity'] * $item['unit_price']);
        }, 0);

    // Coupon & custom
    $couponDiscount = (float) data_get($validated, 'appliedCoupon.discount', 0);
    $customDiscount = (float) ($validated['custom_discount'] ?? 0);
    $customType     = $validated['custom_discount_type'] ?? 'fixed';

    if ($customType === 'percent') {
        // percent is based on subtotal BEFORE discounts
        $customDiscount = round(($totalAmount * $customDiscount) / 100, 2);
    }

    // Final discount (cap at subtotal)
    $totalDiscount = max(0, min($totalAmount, $productDiscounts + $couponDiscount + $customDiscount));

    DB::beginTransaction();
    try {
        // ---- CUSTOMER UPSERT ----
        $customer = null;
        if (!empty($validated['customer'])) {
            $customer = $this->upsertCustomerFromArray($validated['customer']);
        }

        // ---- CHEQUE (when paymentMethod=cheque) ----
        $cheque = null;
        if ($validated['paymentMethod'] === 'cheque') {
            $chequeData = $validated['online'] ?? null; // payload kept under 'online' for compatibility
            if (!$chequeData) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Incomplete cheque details. Please provide cheque number, bank name, date, and amount.'
                ], 422);
            }

            $cheque = Cheque::create([
                'cheque_number' => $chequeData['cheque_number'],
                'bank_name'     => $chequeData['bank_name'],
                'cheque_date'   => $chequeData['cheque_date'],
                'amount'        => $chequeData['amount'],
                'notes'         => $chequeData['notes'] ?? null,
                'status'        => 'pending',
            ]);
        }

        // ---- SALE CREATION ----
        // Calculate the true bill amount as the sum of all discounted product prices (matches frontend bill)
        $discountedTotal = 0.0;
        foreach ($products as $p) {
            $price = (float)($p['unit_price'] ?? $p['__resolved_unit_price']);
            if (isset($p['discount_value']) && isset($p['discount_type'])) {
                if ($p['discount_type'] === 'rs') {
                    $price = max($price - (float)$p['discount_value'], 0);
                } elseif ($p['discount_type'] === 'percent') {
                    $price = max($price - ($price * (float)$p['discount_value'] / 100), 0);
                }
            }
            $discountedTotal += $price * (float)$p['quantity'];
        }
        // Apply custom discount (fixed or percent)
        $customDiscount = (float) ($validated['custom_discount'] ?? 0);
        if ($customType === 'percent') {
            $customDiscount = ($discountedTotal * $customDiscount) / 100;
        }
        $finalBillAmount = $discountedTotal - $customDiscount;
        $saleData = [
            'customer_id'    => $customer?->id,
            'employee_id'    => $validated['employee_id'] ?? null,
            'user_id'        => $validated['userId'],
            'order_id'       => $validated['orderid'],
            'total_amount'   => $finalBillAmount,
            'discount'       => $totalDiscount,
            'total_cost'     => $totalCost,
            'payment_method' => $validated['paymentMethod'], // cash|card|cheque
            'sale_date'      => now()->toDateString(),
            'cash'           => (float)($validated['cash'] ?? 0),
            'is_whole'       => $isWhole,
            'custom_discount_type' => $customType,
            'custom_discount'      => $validated['custom_discount'] ?? 0, // store the raw value entered
            'credit_bill'    => (bool)($validated['credit_bill'] ?? false),
        ];

        // Optionally attach cheque_id if your 'sales' table has it
        if ($cheque && \Schema::hasColumn('sales', 'cheque_id')) {
            $saleData['cheque_id'] = $cheque->id;
        }

        /** @var \App\Models\Sale $sale */
        $sale = Sale::create($saleData);

        // ---- SALE ITEMS & STOCK MOVES ----
        foreach ($products as $p) {
            $productModel = Product::find($p['id']);
            $qty       = (float)$p['quantity'];
            $unitPrice = (float)$p['__resolved_unit_price'];

            SaleItem::create([
                'sale_id'        => $sale->id,
                'product_id'     => $productModel->id,
                'quantity'       => $qty,
                'unit_price'     => $unitPrice,
                'total_price'    => $qty * $unitPrice,
                'discount_amount'=> isset($p['discount_value']) ? $p['discount_value'] : null,
                'discount_type'  => isset($p['discount_type']) ? $p['discount_type'] : null,
            ]);

            StockTransaction::create([
                'product_id'        => $productModel->id,
                'sale_id'           => $sale->id,
                'transaction_type'  => 'Sold',
                'quantity'          => $qty,
                'transaction_date'  => now(),
                'supplier_id'       => $productModel->supplier_id ?? null,
            ]);

            $productModel->decrement('stock_quantity', $qty);
        }

         foreach ($returnItems as $item) {
                    // You may want to validate the sale_item_id and product_id exist
               
                    $unitPrice = $item['unit_price'] ?? 0;
                    $quantity = $item['quantity'] ?? 0;
                    $totalPrice = $quantity * $unitPrice;
                    ReturnItem::create([
                        'sale_id' => $sale->id,
                        'customer_id' => $customer ? $customer->id : null,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'reason' => $item['reason'] ?? '',
                        'unit_price' =>$unitPrice,
                        'return_date' => $item['return_date'] ?? now()->toDateString(),
                        'total_price' => $totalPrice,
                      
                    ]);
                    

                         // For return items, we need to increase stock
                $returnedProduct = Product::find($item['product_id']);
                if ($returnedProduct) {
                    $returnedProduct->update([
                        'stock_quantity' => $returnedProduct->stock_quantity + $item['quantity']
                    ]); 

                    StockTransaction::create([
                        'product_id' => $item['product_id'],
                        'transaction_type' => 'Returned',
                        'quantity' => $item['quantity'],
                        'transaction_date' => now(),
                        'supplier_id' => $returnedProduct->supplier_id ?? null,
                    ]);

       
                }

            }

        DB::commit();

        return response()->json([
            'message' => 'Sale recorded successfully!',
            'sale_id' => $sale->id
        ], 201);

    } catch (\Throwable $e) {
        DB::rollBack();

        \Log::error('Sale submission error: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString(),
            'request_data' => $request->all()
        ]);

        return response()->json([
            'message' => 'Failed to record sale.',
            'error'   => config('app.debug') ? $e->getMessage() : 'Server error occurred. Please try again.',
        ], 500);
    }
}













/**
 * Normalize a phone number: strip non-digits, keep leading + on country code.
 */
private function normalizePhone(?string $countryCode, ?string $number): string
{
    $cc  = $countryCode ? preg_replace('/\D+/', '', $countryCode) : '';
    $num = $number ? preg_replace('/\D+/', '', $number) : '';

    if ($cc && Str::startsWith($num, '0')) {
        $num = ltrim($num, '0');
    }

    return $cc && $num ? '+' . $cc . $num : ($num ?: '');
}

/**
 * Create or update a customer from request array.
 * - Prefer match by email (if present), else by normalized phone.
 * - Update basic fields if they’re blank/changed.
 * - Skip creating if nothing meaningful was provided.
 */
private function upsertCustomerFromArray(array $customerData): ?Customer
{
    $name        = isset($customerData['name']) ? trim($customerData['name']) : null;
    $email       = isset($customerData['email']) ? trim($customerData['email']) : null;
    $contact     = isset($customerData['contactNumber']) ? trim($customerData['contactNumber']) : null;
    $countryCode = isset($customerData['countryCode']) ? trim($customerData['countryCode']) : null;
    $address     = isset($customerData['address']) ? trim($customerData['address']) : null;

    $phone = $this->normalizePhone($countryCode, $contact);

    if (!$email && !$phone && !$name) {
        return null; // nothing meaningful provided
    }

    // Find existing customer by email, then by phone
    $existing = null;
    if ($email) {
        $existing = Customer::where('email', $email)->first();
    }
    if (!$existing && $phone) {
        $existing = Customer::where('phone', $phone)->first();
    }

    // Create new if not found
    if (!$existing) {
        return Customer::create([
            'name'           => $name ?: null,
            'email'          => $email ?: null,
            'phone'          => $phone ?: null,
            'address'        => $address ?: null,
            'member_since'   => now()->toDateString(),
            'loyalty_points' => 0,
        ]);
    }

    // Update fields if provided and changed
    $dirty = false;
    if ($name && $existing->name !== $name) {
        $existing->name = $name; $dirty = true;
    }
    if ($email && $existing->email !== $email) {
        $existing->email = $email; $dirty = true;
    }
    if ($phone && $existing->phone !== $phone) {
        $existing->phone = $phone; $dirty = true;
    }
    if ($address && $existing->address !== $address) {
        $existing->address = $address; $dirty = true;
    }
    if ($dirty) {
        $existing->save();
    }

    return $existing;
}




    public function updateOrder(Request $request, $order_id)
    {
        try {
            $sale = Sale::where('order_id', $order_id)->firstOrFail();
            $isReturnBill = $request->input('returnBill', false);

            // Process existing items before updating
            $existingItems = SaleItem::where('sale_id', $sale->id)->get();

            // Only restore stock if not processing a return
            if (!$isReturnBill) {
                foreach ($existingItems as $item) {
                    $product = Product::find($item->product_id);
                    if ($product) {
                        $product->increment('stock_quantity', $item->quantity);
                    }
                }
                // Delete existing items to re-create them
                SaleItem::where('sale_id', $sale->id)->delete();
            }

            // Update the main sale record
            $sale->update([
                'customer_id' => $request->input('customer_id'),
                'employee_id' => $request->input('employee_id'),
                'payment_method' => $request->input('paymentMethod'),
                'cash' => $request->input('cash'),
                'custom_discount' => $request->input('custom_discount'),
                'status' => $request->input('status', $sale->status),
                'kitchen_note' => $request->input('kitchen_note'),
                'is_return_bill' => $isReturnBill ? 1 : $sale->is_return_bill,
                'return_date' => $isReturnBill ? now() : $sale->return_date,
            ]);

            // Process all products
            foreach ($request->input('products') as $product) {
                $productModel = Product::find($product['id']);

                if (!$productModel) {
                    if ($isReturnBill)
                        continue;

                    $productModel = Product::create([
                        'name' => $product['name'],
                        'stock_quantity' => $product['quantity'],
                        'selling_price' => $product['selling_price'],
                        'supplier_id' => $product['supplier_id'] ?? null,
                    ]);

                    StockTransaction::create([
                        'product_id' => $productModel->id,
                        'sale_id' => $sale->id,
                        'transaction_type' => 'New Product',
                        'quantity' => $product['quantity'],
                        'transaction_date' => now(),
                        'supplier_id' => $product['supplier_id'] ?? null,
                    ]);
                }

                $reasonId = $product['returnReason'] ?? null;
                $oldStockQuantity = $productModel->stock_quantity;

                if ($isReturnBill) {
                    // Check if this product already exists in the sale
                    $saleItem = SaleItem::where('sale_id', $sale->id)
                        ->where('product_id', $productModel->id)
                        ->first();

                    if ($saleItem) {
                        // Handle quantity changes for returned items
                        $originalQuantity = $saleItem->getOriginal('quantity');
                        $newQuantity = $product['quantity'];
                        $quantityDifference = $originalQuantity - $newQuantity;

                        // Update sale item
                        $saleItem->update([
                            'reason_id' => $reasonId,
                            'quantity' => $newQuantity,
                            'total_price' => $newQuantity * $saleItem->unit_price,
                        ]);

                        // Handle stock adjustments based on return reason
                        if ($reasonId == "2") { // "Returning a New Product"
                            if ($quantityDifference > 0) {
                                // Reduced quantity - add back to stock
                                $productModel->increment('stock_quantity', $quantityDifference);

                                StockTransaction::create([
                                    'product_id' => $productModel->id,
                                    'sale_id' => $sale->id,
                                    'transaction_type' => 'Return',
                                    'quantity' => $quantityDifference,
                                    'transaction_date' => now(),
                                    'supplier_id' => $productModel->supplier_id ?? null,
                                ]);
                            } elseif ($quantityDifference < 0) {
                                // Increased quantity - deduct from stock
                                $productModel->decrement('stock_quantity', abs($quantityDifference));

                                StockTransaction::create([
                                    'product_id' => $productModel->id,
                                    'sale_id' => $sale->id,
                                    'transaction_type' => 'Exchange',
                                    'quantity' => abs($quantityDifference),
                                    'transaction_date' => now(),
                                    'supplier_id' => $productModel->supplier_id ?? null,
                                ]);
                            }
                        } elseif ($reasonId != "1") { // Not damaged product
                            // Standard return logic for other reasons
                            if ($quantityDifference > 0) {
                                $productModel->increment('stock_quantity', $quantityDifference);

                                StockTransaction::create([
                                    'product_id' => $productModel->id,
                                    'sale_id' => $sale->id,
                                    'transaction_type' => 'Return',
                                    'quantity' => $quantityDifference,
                                    'transaction_date' => now(),
                                    'supplier_id' => $productModel->supplier_id ?? null,
                                ]);
                            }
                        }
                    } else {
                        // New item in return bill - create sale item
                        SaleItem::create([
                            'sale_id' => $sale->id,
                            'product_id' => $productModel->id,
                            'quantity' => $product['quantity'],
                            'unit_price' => $product['selling_price'],
                            'total_price' => $product['quantity'] * $product['selling_price'],
                            'reason_id' => $reasonId,
                        ]);

                        // For "Returning a New Product", deduct from stock for new items
                        if ($reasonId == "2") {
                            $productModel->decrement('stock_quantity', $product['quantity']);

                            StockTransaction::create([
                                'product_id' => $productModel->id,
                                'sale_id' => $sale->id,
                                'transaction_type' => 'Exchange',
                                'quantity' => $product['quantity'],
                                'transaction_date' => now(),
                                'supplier_id' => $productModel->supplier_id ?? null,
                            ]);
                        }
                    }
                } else {
                    // Standard update process
                    if ($productModel->stock_quantity < $product['quantity']) {
                        return response()->json([
                            'message' => "Insufficient stock for product: {$productModel->name} ({$productModel->stock_quantity} available)",
                        ], 423);
                    }

                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $productModel->id,
                        'quantity' => $product['quantity'],
                        'unit_price' => $product['selling_price'],
                        'total_price' => $product['quantity'] * $product['selling_price'],
                    ]);

                    $productModel->decrement('stock_quantity', $product['quantity']);

                    StockTransaction::create([
                        'product_id' => $productModel->id,
                        'sale_id' => $sale->id,
                        'transaction_type' => 'Sold',
                        'quantity' => $product['quantity'],
                        'transaction_date' => now(),
                        'supplier_id' => $productModel->supplier_id ?? null,
                    ]);
                }
            }

            return response()->json(['message' => $isReturnBill ? 'Return processed successfully!' : 'Order updated successfully!'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Order not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getPastOrder(Request $request)
    {
        $request->validate([
            'barcode' => 'required|string',
        ]);

        try {
            $order = Sale::where('order_id', $request->barcode)
                ->with(['saleItems.product', 'saleItems.returnReason', 'employee', 'customer'])
                ->first();

            \Log::info('Raw Order Data:', $order ? $order->toArray() : ['order' => null]);

            if (!$order) {
                return response()->json([
                    'error' => 'Order not found'
                ], 404);
            }

            // Transform the data to match frontend expectations
            $transformedOrder = [
                'order_id' => $order->order_id,
                'products' => $order->saleItems->map(function ($item) {
                    return [
                        ...$item->product->toArray(),
                        'pivot' => [
                            'quantity' => $item->quantity,
                            'price' => $item->unit_price,
                            'reason_id' => $item->reason_id,
                            'return_reason' => $item->returnReason ? [
                                'id' => $item->returnReason->id,
                                'reason' => $item->returnReason->reason
                            ] : null
                        ]
                    ];
                }),
                'customer_name' => $order->customer->name ?? '',
                'customer_phone' => $order->customer->contactNumber ?? '',
                'customer_email' => $order->customer->email ?? '',
                'return_date' => $order->return_date,
                'employee_id' => $order->employee_id,
                'payment_method' => $order->payment_method,
                'cash' => $order->cash,
                'custom_discount' => $order->custom_discount,
                'discount' => $order->discount,
                'is_return_bill' => $order->is_return_bill,
            ];

            return response()->json([
                'order' => $transformedOrder
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error fetching order details: ' . $e->getMessage()
            ], 500);
        }
    }

    public function markGuideCompleted(Sale $sale)
    {
        $sale->update([
            'guide_pending' => 0,
        ]);

        return response()->json(['message' => 'Guide marked as completed.']);
    }

}
