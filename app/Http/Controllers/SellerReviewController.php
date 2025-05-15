<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;

class SellerReviewController extends Controller
{
    /**
     * Display a listing of reviews.
     */
    public function index()
    {
        $reviews = Review::with(['user', 'product', 'order'])
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('seller.reviews', compact('reviews'));
    }
    
    /**
     * Approve a review.
     */
    public function approve($id)
    {
        $review = Review::findOrFail($id);
        $review->status = 'approved';
        $review->save();
        
        return redirect()->back()->with('success', 'Review approved successfully.');
    }
    
    /**
     * Reject a review.
     */
    public function reject($id)
    {
        $review = Review::findOrFail($id);
        $review->status = 'rejected';
        $review->save();
        
        return redirect()->back()->with('success', 'Review rejected successfully.');
    }

    public function show($id)
    {
        $review = Review::with(['product', 'user', 'order'])->findOrFail($id);
        return view('seller.review-details', compact('review'));
    }
} 