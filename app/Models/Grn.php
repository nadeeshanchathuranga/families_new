<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grn extends Model
{
    use HasFactory;
    protected $fillable = ['grn_number', 'status'];

    public function products()
    {
        return $this->hasMany(Product::class, 'grn_id', 'id');
    }
}
