<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Product;

class SellerController extends Controller
{
    // Dashboard view
    public function dashboard()
    {
        return view('seller.dashboard');
    }

    // Products view
    public function products()
    {
        return view('seller.products');
    }

    // Index function for filtering products by category
    public function index(Request $request)
    {
        $categories = Category::all(); // Fetch all categories
        $category_id = $request->category_id; // Get selected category from request

        // Fetch products belonging to the logged-in seller and apply category filter
        $products = Product::where('seller_id', auth()->id()) // Ensure only seller's products are shown
            ->when($category_id, function ($query) use ($category_id) {
                return $query->where('category_id', $category_id);
            })->get();

        return view('seller.products', compact('products', 'categories', 'category_id'));
    }
}
