<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'payment_id',
        'external_id',
        'amount',
        'status',
        'payment_method',
        'payment_channel',
        'payment_url',
        'expiry_date',
        'payload',
    ];

    /**
     * Get the order associated with the payment transaction.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}