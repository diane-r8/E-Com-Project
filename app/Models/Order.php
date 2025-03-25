<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'rush_order',
        'phone_number',
        'delivery_area_id',
        'status',
        'total_price',
        'payment_method',
        'proof_of_payment'
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function customOrder()
    {
        return $this->hasOne(CustomOrder::class);
    }

    public function deliveryArea()
    {
        return $this->belongsTo(DeliveryArea::class);
    }
}
