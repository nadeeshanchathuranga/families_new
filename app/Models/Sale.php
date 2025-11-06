<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'employee_id',
        'user_id',
        'order_id',
        'total_amount',
        'discount',
        'payment_method',
        'sale_date',
        'total_cost',
        'cash',
        'custom_discount',
        'is_return_bill',
        'return_date',
        'is_whole',
        'guide_name',
        'guide_comi',
        'guide_cash',
        'guide_pending',
        'total_to_include_guide',
        'credit_bill',
        'cheque_id',
        'custom_discount_type',
        'service_name',
        'is_service',
    ];

    protected $casts = [
        'sale_date' => 'date',
        'return_date' => 'date',
        'total_amount' => 'decimal:2',
        'discount' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'cash' => 'decimal:2',
        'custom_discount' => 'decimal:2',
          'custom_discount_type' => 'string',
        'guide_comi' => 'decimal:2',
        'guide_cash' => 'decimal:2',
        'guide_pending' => 'decimal:2',
        'total_to_include_guide' => 'decimal:2',
        'is_return_bill' => 'boolean',
        'is_whole' => 'boolean',
        'credit_bill' => 'boolean',
           'is_service'       => 'boolean',
    ];

    // Relationships
    public function cheque()
    {
        return $this->belongsTo(Cheque::class);
    }

    // REMOVED: Incorrect self-referencing relationship
    // public function sale() - This was wrong

    // REMOVED: Incorrect relationship with SaleItem using order_id
    // public function saleItem() - This was wrong

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class, 'sale_id', 'id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    // Scopes for common filters
    public function scopeExcludeReturns($query)
    {
        return $query->where(function($q) {
            $q->where('is_return_bill', '!=', 1)
              ->orWhereNull('is_return_bill');
        });
    }

    public function scopeOnlyReturns($query)
    {
        return $query->where('is_return_bill', 1);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        if ($startDate && $endDate) {
            return $query->whereBetween('sale_date', [$startDate, $endDate]);
        }
        return $query;
    }

    // Accessors
    public function getFinalTotalAttribute()
    {
        $discountRate = $this->custom_discount ?? 0;
        $discounted = ($this->total_amount ?? 0) * (1 - $discountRate / 100);
        return $discounted + ($this->guide_cash ?? 0);
    }

    public function getTotalDiscountAttribute()
    {
        return ($this->discount ?? 0) + ($this->custom_discount ?? 0);
    }

    public function getNetAmountAttribute()
    {
        return $this->total_amount - $this->total_discount;
    }

    // Helper methods
    public function isReturnBill()
    {
        return $this->is_return_bill == 1;
    }
}
