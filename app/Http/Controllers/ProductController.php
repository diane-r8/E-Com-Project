<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use App\Models\Category;
use App\Models\ProductVariation;
use League\Csv\Reader;

class ProductController extends Controller
{
    // 🛒 Show product catalog for buyers
    public function index(Request $request)
    {
        $categories = Category::all();
        $category_id = $request->category_id;
    
        $products = Product::with('variations') // 👈 Ensure variations are loaded
            ->when($category_id, function ($query) use ($category_id) {
                return $query->where('category_id', $category_id);
            })
            ->get();
    
        return view('products', compact('products', 'categories', 'category_id'));
    }
    

    // ✅ Store new product (Only for Sellers)
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'description' => 'required',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
            'availability' => 'required|boolean',
            'category_id' => 'required|exists:categories,id',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        // Create the product
        $product = new Product();
        $product->name = $request->name;
        $product->description = $request->description;
        $product->price = $request->price;
        $product->stock = $request->stock;
        $product->availability = $request->availability;
        $product->category_id = $request->category_id;

        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('images'), $imageName);
            $product->image = $imageName;
        }

        $product->save();

        // Handle product variations
        if ($request->has('variations')) {
            foreach ($request->variations as $variation) {
                $product->variations()->create([
                    'name' => $variation['name'],
                    'price' => $variation['price'] ?? $product->price,
                    'stock' => $variation['stock']
                ]);
            }
        }

        return redirect()->route('seller.products')->with('success', 'Product added successfully!');
    }

    // 🛍️ Show seller's products
    public function sellerProducts()
    {
        $products = Product::all();
        return view('seller.products', compact('products'));
    }

    // 📝 Edit product
    public function edit($id)
    {
        $product = Product::findOrFail($id);
        return view('seller.edit_product', compact('product'));
    }

    // 🔄 Update product
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'name' => 'required',
            'description' => 'required',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
            'availability' => 'required|boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

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

        return redirect()->route('seller.products')->with('success', 'Product updated successfully!');
    }

    // 🗑️ Delete product
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
        return redirect()->route('seller.products')->with('success', 'Product deleted successfully!');
    }

    // 📦 Import products from CSV
    public function importProducts()
    {
        $filePath = storage_path('app/products.csv');

        if (!file_exists($filePath)) {
            return redirect()->back()->with('error', 'CSV file not found.');
        }

        $csv = Reader::createFromPath($filePath, 'r');
        $csv->setHeaderOffset(0);

        foreach ($csv as $record) {
            Product::create([
                'name' => $record['name'],
                'description' => $record['description'],
                'price' => $record['price'],
                'stock' => $record['stock'],
                'availability' => $record['availability'],
                'category_id' => $record['category_id'],
                'image' => $record['image']
            ]);
        }

        return redirect()->back()->with('success', 'Products imported successfully!');
    }

    // 📦 Adjust stock
    public function adjustStock(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $product->stock = $request->stock;
        $product->save();

        return response()->json(['success' => true, 'new_stock' => $product->stock]);
    }

    // 🔍 Search for products
    public function search(Request $request)
    {
        $categories = Category::all();
        $category_id = $request->category_id;
        $searchQuery = $request->search;

        $products = Product::when($category_id, function ($query) use ($category_id) {
            return $query->where('category_id', $category_id);
        })
        ->when($searchQuery, function ($query) use ($searchQuery) {
            return $query->where('name', 'like', '%' . $searchQuery . '%')
                         ->orWhere('description', 'like', '%' . $searchQuery . '%');
        })
        ->get();

        return view('products', compact('products', 'categories', 'category_id', 'searchQuery'));
    }

    // 🆕 Create product page
    public function create()
    {
        $categories = Category::all();
        return view('seller.create_product', compact('categories'));
    }
}
