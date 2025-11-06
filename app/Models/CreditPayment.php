<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'order_id',
        'customer_id',
        'pending_payment',
        'part_payment',
        'description',
    ];

    /**
     * The sale this credit payment belongs to.
     */
    public function sale()
    {
        return $this->belongsTo(Sale::class, 'sale_id', 'id');
    }

    /**
     * The order related to this credit payment (nullable).
     */
    public function order()
    {
        return $this->belongsTo(Sale::class, 'order_id', 'id');
    }

    /**
     * The customer who made this credit payment (nullable).
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }
}
