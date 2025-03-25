<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryArea extends Model
{
    protected $fillable = [
        'area_name',
        'delivery_fee'
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
