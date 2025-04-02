<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariation;


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

    public function editVariation($id)
{
    $variation = ProductVariation::findOrFail($id);
    return view('seller.edit_variation', compact('variation'));
}

public function updateVariation(Request $request, $id)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'price' => 'required|numeric|min:0',
        'stock' => 'required|integer|min:0'
    ]);

    $variation = ProductVariation::findOrFail($id);
    $variation->update([
        'name' => $request->name,
        'price' => $request->price,
        'stock' => $request->stock,
    ]);

    return redirect()->route('seller.products')->with('success', 'Product variation updated successfully.');
}

public function adjustProductStock(Request $request, $id) {
    $product = Product::findOrFail($id);
    $product->stock = $request->stock;
    $product->save();

    return response()->json(['success' => true, 'stock' => $product->stock]);
}

public function adjustVariationStock(Request $request, $id) {
    $variation = ProductVariation::findOrFail($id);
    $variation->stock = $request->stock;
    $variation->save();

    return response()->json(['success' => true, 'stock' => $variation->stock]);
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

