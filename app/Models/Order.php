<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'shipping_address', 'phone_number', 'rush_order', 
        'total_price', 'delivery_fee', 'status'
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function deliveryArea()
    {
    return $this->belongsTo(DeliveryArea::class);
    }

}
