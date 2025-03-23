<?php

namespace App\Http\Controllers;


use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use App\Models\Category;
use League\Csv\Reader;
//use Illuminate\Support\Facades\Storage;



class ProductController extends Controller
{
    // 🛒 Show product catalog for buyers
   
   
    public function index(Request $request)
    {
        $categories = Category::all(); // Get all categories
        $category_id = $request->category_id; // Get selected category from request
    
        // Fetch products based on selected category or all products if no category is selected
        $products = Product::when($category_id, function ($query) use ($category_id) {
            return $query->where('category_id', $category_id);
        })->get();
    
        return view('products', compact('products', 'categories', 'category_id'));
    }
        // ✅ Store new product (Only for Sellers)



public function importProducts()
{
    $filePath = storage_path('app/products.csv'); // Path to CSV file

    // Check if the file exists
    if (!file_exists($filePath)) {
        return redirect()->back()->with('error', 'CSV file not found.');
    }

    // Read the CSV file
    $csv = Reader::createFromPath($filePath, 'r');
    $csv->setHeaderOffset(0); // Use the first row as headers

    foreach ($csv as $record) {
        // Create a new product record
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

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'description' => 'required',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
            'availability' => 'required|boolean',
            'category_id' => 'required|exists:categories,id', // Ensure category exists
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048' // Validate image type
        ]);
    
        $product = new Product();
        $product->name = $request->name;
        $product->description = $request->description;
        $product->price = $request->price;
        $product->stock = $request->stock;
        $product->availability = $request->availability;
        $product->category_id = $request->category_id; // Assign category to product
    
        // Handle image upload (store all images in public/images/)
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
    
            // Move to public/images/
            $image->move(public_path('images'), $imageName);
    
            // Store relative path in database
            $product->image =  $imageName;
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




public function create()
{
    $categories = Category::all();
    return view('seller.create_product', compact('categories'));
}

}

