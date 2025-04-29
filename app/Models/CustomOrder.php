<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomOrder extends Model
{
    use HasFactory;

    protected $fillable = ['order_id', 'details', 'budget_max', 'final_price', 'status'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
