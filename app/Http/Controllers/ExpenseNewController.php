<?php
namespace App\Http\Controllers;
use App\Models\ExpenseNew;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;





class ExpenseNewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // View permission: Admin or Manager


        $expenses = ExpenseNew::query()
            ->latest('id')
            ->get();

        return Inertia::render('ExpenseNew/Index', [
            'expenses'      => $expenses,
            'totalExpenses' => $expenses->count(),
            'sumAmount'     => $expenses->sum('amount'),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'amount'       => 'required|numeric',
            'expense_date' => 'required|date',
            'note'         => 'nullable|string',
        ]);

        ExpenseNew::create($validated);


        if ($request->has('expenseTitle')) {
            return redirect()
                ->route('expenses.index')
                ->with('success', 'Expense created successfully and redirected to Expenses.');
        }

        return back()->banner('Expense created successfully!');
    }

    /**
     * Update the specified resource in storage.
     */

 public function update(Request $request, ExpenseNew $expense)
{
    // Only Admin can update (same pattern as your Color update)
    if (!Gate::allows('hasRole', ['Admin'])) {
        abort(403, 'Unauthorized');
    }

    // Validation (as requested)
    $validated = $request->validate([
        'title'        => ['nullable', 'string', 'max:255'],
        'amount'       => ['nullable', 'numeric'],
        'expense_date' => ['nullable', 'date'],
        'note'         => ['nullable', 'string'],
    ]);

    // Update the expense
    $expense->update($validated);


    return redirect()
        ->route('expenses.index')
        ->banner('Expense updated successfully.');
}
    /**
     * Remove the specified resource from storage.
     *
     * (Using $id here because your front-end calls /expenses/{id}. If you
     * switch to implicit model binding, change signature to:
     *   destroy(ExpenseNew $expense)
     * and ensure the route parameter is {expense}.)
     */
    public function destroy($id)
    {

        $expense = ExpenseNew::findOrFail($id);
        $expense->delete();

        return back()->banner('Expense deleted successfully.');
    }
}
