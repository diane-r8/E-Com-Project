<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 
        'description', 
        'price', 
        'stock', 
        'availability', 
        'image',
        'category_id' 
    ];

    // Relationship with Category
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    //  Relationship with ProductVariation 
    public function variations()
    {
        return $this->hasMany(ProductVariation::class);
    }  
}
