<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseNew extends Model
{
    use HasFactory;

    protected $table = 'expense_news';

    protected $fillable = [
        'title',
        'amount',
        'expense_date',
        'note',
    ];


}
