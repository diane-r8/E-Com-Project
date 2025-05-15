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

    protected $appends = ['average_rating', 'review_count'];

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

    // Relationship with Reviews
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    // Get the average rating for this product
    public function getAverageRatingAttribute()
    {
        $approvedReviews = $this->reviews()->where('status', 'approved');
        
        if ($approvedReviews->count() > 0) {
            return round($approvedReviews->avg('rating'), 1);
        }
        
        return 0;
    }

    // Get the total number of reviews
    public function getReviewCountAttribute()
    {
        return $this->reviews()->where('status', 'approved')->count();
    }
}
