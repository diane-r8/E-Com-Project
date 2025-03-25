<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    // Specify the table name if not plural of the model name
    protected $table = 'payments';

    // ✅ Mass assignable attributes
    protected $fillable = [
        'order_id',
        'payment_method',
        'proof_of_payment',
        'amount_paid',
        'status',
    ];

    // ✅ Relationships

    // Each payment belongs to an order
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
