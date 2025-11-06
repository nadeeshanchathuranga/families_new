<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ReturnItemController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\CompanyInfoController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ColorController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\SizeController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\GrnController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\BulkUploadController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\StockTransactionController;
use App\Http\Controllers\TransactionHistoryController;
use App\Http\Controllers\ManualPosController;
use App\Http\Controllers\ExpenseNewController;
use App\Http\Controllers\CreditController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;

Route::get('/dashboard', function () {
    return Inertia::location(route('dashboard'));
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/', function () {
        if (Gate::allows('hasRole', ['Cashier'])) {
            return redirect()->route('pos.index');
        }
        return Inertia::render('Dashboard');
    })->name('dashboard');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('categories', CategoryController::class);
    Route::get('/products/next-batch', [ProductController::class, 'getNextBatchNo']);
    Route::get('/grns/next-batch', [GrnController::class, 'getNextBatchNo']);
    Route::post('/csv-upload', [ProductController::class, 'uploadCsv'])->name('csv.upload');
    Route::resource('products', ProductController::class);
    Route::resource('suppliers', SupplierController::class);
    Route::post('/supplier-payment', [SupplierController::class, 'supplierPayment']);
    Route::post('suppliers/{supplier}', [SupplierController::class, 'update']);
    Route::post('products/{product}', [ProductController::class, 'update']);
    Route::post('products-variant', [ProductController::class, 'productVariantStore'])->name('productVariant');
    Route::resource('grns', GrnController::class);
    Route::post('products-size', [ProductController::class, 'sizeStore'])->name('productSize');

    Route::get('/company-info', [CompanyInfoController::class, 'index'])->name('companyInfo.index');
    Route::post('/company-info/{companyInfo}', [CompanyInfoController::class, 'update'])->name('companyInfo.update');

    Route::put('/sales/{sale}/mark-guide-completed', [PosController::class, 'markGuideCompleted']);
    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    Route::post('/pos', [PosController::class, 'getProduct'])->name('pos.getProduct');
    Route::post('/get-coupon', [PosController::class, 'getCoupon'])->name('pos.getCoupon');
    Route::post('/pos/submit', [PosController::class, 'submit'])->name('pos.checkout');
Route::post('/service/submit', [PosController::class, 'serviceSubmit'])
    ->name('pos.service_submit');



    Route::resource('payment', PaymentController::class);
    Route::resource('reports', ReportController::class);
    Route::get('/batch-management/search', [ReportController::class, 'searchByCode']);
    Route::resource('customers', CustomerController::class);
    Route::resource('colors', ColorController::class);
    Route::resource('coupons', CouponController::class);
    Route::resource('sizes', SizeController::class);
    Route::resource('employees', EmployeeController::class);
    Route::resource('transactionHistory', TransactionHistoryController::class);
    Route::post('/transactions/delete', [TransactionHistoryController::class, 'destroy'])->name('transactions.delete');
    Route::post('/transactions/mark-as-paid', [TransactionHistoryController::class, 'markAsPaid'])->name('transactions.markAsPaid');
    Route::post('/cheque/mark-paid', [TransactionHistoryController::class, 'markAsPaidCheque'])->name('cheque.markAsPaid');

    Route::resource('stock-transition', StockTransactionController::class);
    Route::resource('manualpos', ManualPosController::class);

    Route::resource('quotation', QuotationController::class);
    Route::post('/api/save-quotation', [QuotationController::class, 'saveQuotationPdf']);
    Route::post('/pos/get-past-order', [PosController::class, 'getPastOrder'])->name('pos.getPastOrder');
    Route::put('/pos/update/{order_id}', [PosController::class, 'updateOrder'])->where('order_id', '.*');

    Route::resource('bulk', BulkUploadController::class);

    Route::resource('return-bill', ReturnItemController::class);

    Route::post('/api/products', [ProductController::class, 'fetchProducts']);
    Route::post('/api/sale/items', [ReturnItemController::class, 'fetchSaleItems'])->name('sale.items');

    // Expenses
Route::resource('units', UnitController::class);

    Route::resource('expenses', ExpenseNewController::class);

    Route::get('/credit_payment/{sale_id}/fetch', [CreditController::class, 'fetchSalePayments'])
        ->name('CreditPayment.fetch');
    Route::resource('credit_payment', CreditController::class);

Route::resource('users', UserController::class); 


});

Route::get('/barcode/{id}', [CategoryController::class, 'showBarcode']);
