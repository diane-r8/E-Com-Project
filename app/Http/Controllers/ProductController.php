<?php

namespace App\Http\Controllers;


use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // 🛒 Show product catalog for buyers
   
    public function index()
    {
        $products = Product::all(); // Fetch all products
        return view('products', compact('products')); // Pass to view
    }
    // 📦 Show product creation form (Only for Sellers)
    public function create()
    {
        return view('seller.create_product'); // Make sure to create this Blade file
    }

    // ✅ Store new product (Only for Sellers)
   

    public function store(Request $request)
    {
        $product = new Product();
        $product->name = $request->name;
        $product->description = $request->description;
        $product->price = $request->price;
        $product->stock = $request->stock;
        $product->availability = $request->availability;
    
        // Handle image upload
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
            $product->image = $imagePath;
        }
    
        $product->save();
    
        return redirect()->route('seller.create_product')->with('success', 'Product added successfully!');
    }
       // 🛍️ Show seller's products
    public function sellerProducts()
    {
        $products = Product::all(); // Fetch all products
        return view('seller.products', compact('products'));
    }

    //edit and update product
    public function edit($id)
    {
        $product = Product::findOrFail($id);
        return view('seller.edit_product', compact('product'));
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $product->name = $request->name;
        $product->description = $request->description;
        $product->price = $request->price;
        $product->stock = $request->stock;
        $product->availability = $request->availability;

        // Handle new image upload if provided
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
            $product->image = $imagePath;
        }

        $product->save();

        return redirect()->route('seller.create_product')->with('success', 'Product updated successfully!');
    }

     // 🗑️ Delete product
     public function destroy($id)
     {
         $product = Product::findOrFail($id);
         $product->delete();
         
         return redirect()->route('seller.products')->with('success', 'Product deleted successfully!');
     }


}
