<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use App\Models\Sale;
use App\Models\CreditPayment;
use App\Models\Customer;

class CreditController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!Gate::allows('hasRole', ['Admin', 'Cashier'])) {
                abort(403, 'Unauthorized');
            }
            return $next($request);
        });
    }

    /**
     * Display all credit payments with part payments included
     */
    public function index()
    {
        $payments = CreditPayment::with(['customer:id,name', 'sale:id,order_id,total_amount'])
            ->orderBy('id', 'desc')
            ->get()
            ->groupBy('sale_id')
            ->map(function ($group) {
                $first = $group->first();
                $totalPaid = $group->sum('part_payment');
                $pending = max(0, $first->sale->total_amount - $totalPaid);

                $status = $pending === 0 ? 'Completed' : 'Pending';

                // Update all credit payments for this sale to reflect correct status
                $group->each(function ($p) use ($pending) {
                    $p->status = $p->pending_payment == 0 ? 'Completed' : 'Pending';
                    $p->save();
                });

                return [
                    'sale_id' => $first->sale_id,
                    'order_id' => $first->sale->order_id,
                    'customer_name' => $first->customer ? $first->customer->name : '-',
                    'pending_payment' => $pending,
                    'paid_amount' => $totalPaid,
                    'total_amount' => $first->sale->total_amount,
                    'latest_payment_date' => $group->last()->created_at->format('Y-m-d'),
                    'status' => $status,
                    'part_payments' => $group->map(function ($p) {
                        return [
                            'part_payment' => $p->part_payment,
                            'description' => $p->description,
                            'date' => $p->created_at->format('Y-m-d'),
                            'status' => $p->pending_payment == 0 ? 'Completed' : 'Pending',
                        ];
                    })->values(),
                ];
            })->values();

        $customers = Customer::select('id', 'name')->get();
        $sales = Sale::select('id', 'order_id', 'customer_id', 'total_amount', 'credit_bill')
            ->where('credit_bill', 1)
            ->with(['customer:id,name'])
            ->get();

        return Inertia::render('CreditPayment/Index', [
            'allPayments' => $payments,
            'customers' => $customers,
            'sales' => $sales,
        ]);
    }

    /**
     * Store a new credit payment
     */
    public function store(Request $request)
    {


        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'order_id' => 'nullable|string',
            'sale_id' => 'required|exists:sales,id',
            'part_payment' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:255',
        ]);

        $sale = Sale::findOrFail($validated['sale_id']);

        // Total paid so far
        $totalPaid = CreditPayment::where('sale_id', $sale->id)->sum('part_payment');

        // Prevent overpayment
        if ($totalPaid + $validated['part_payment'] > $sale->total_amount) {
            $overAmount = ($totalPaid + $validated['part_payment']) - $sale->total_amount;
            return redirect()->back()->with('error', "Payment exceeds total sale amount by {$overAmount}.");
        }

        // Calculate pending amount after this payment
        $pending = max(0, $sale->total_amount - $totalPaid - $validated['part_payment']);

        // Determine status
        $status = $pending === 0 ? 'Completed' : 'Pending';

        // Create CreditPayment record
        $creditPayment = CreditPayment::create([
            'sale_id' => $sale->id,
            'customer_id' => $validated['customer_id'],
            'order_id' => $validated['order_id'] ?? null,
            'part_payment' => $validated['part_payment'],
            'pending_payment' => $pending,
            'description' => $validated['description'] ?? null,
            'status' => $status,
        ]);

        // Update Sale if fully paid
        if ($status === 'Completed') {
            $sale->status = 'completed';
            $sale->credit_bill = 0;
            $sale->save();
        }

        return redirect()->route('credit_payment.index')
            ->with('success', "Payment of {$validated['part_payment']} recorded successfully. Pending: {$pending}. Status: {$status}.");
    }

    /**
     * Fetch single sale with part payments (for modal or API)
     */
    public function show($sale_id)
    {
        $sale = Sale::with([
            'customer:id,name',
            'creditPayments' => fn($q) => $q->orderBy('created_at', 'asc')
        ])->findOrFail($sale_id);

        $totalPaid = $sale->creditPayments->sum('part_payment');
        $pending = max(0, $sale->total_amount - $totalPaid);
        $status = $pending === 0 ? 'Completed' : 'Pending';

        // Update each payment status if needed
        $sale->creditPayments->each(function ($p) {
            $p->status = $p->pending_payment == 0 ? 'Completed' : 'Pending';
            $p->save();
        });

        return response()->json([
            'sale_id' => $sale->id,
            'order_id' => $sale->order_id,
            'customer_name' => $sale->customer ? $sale->customer->name : '-',
            'total_amount' => $sale->total_amount,
            'paid_amount' => $totalPaid,
            'pending_payment' => $pending,
            'latest_payment_date' => optional($sale->creditPayments->last())->created_at->format('Y-m-d'),
            'status' => $status,
            'part_payments' => $sale->creditPayments->map(fn($p) => [
                'part_payment' => $p->part_payment,
                'description' => $p->description,
                'date' => $p->created_at->format('Y-m-d'),
                'status' => $p->pending_payment == 0 ? 'Completed' : 'Pending',
            ]),
        ]);
    }
}
