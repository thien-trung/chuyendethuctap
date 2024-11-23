<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Requests\StoreProduct;

class ProductController extends Controller
{
    // public function index()
    // {
    //     return Product::with("category", "stocks")->paginate(5);
    // }
    public function index()
    {
        return Product::with("category", "stocks")->paginate(4);
    }

    public function show($id)
    {
        $product = Product::with("category", "stocks")->findOrFail($id);
        if ($product->reviews()->exists()) {
            $product['review'] = $product->reviews()->avg('rating');
            $product['num_reviews'] = $product->reviews()->count();
        }
        return $product;
    }
    public function newPage($id)
    {
        $perpage = 4;
        $products = Product::with('category')->where('category_id', $id)->orderBy('id', 'desc')->paginate($perpage);
        $products->each(function ($product) {
            if ($product->reviews()->exists()) {
                $product['review'] = $product->reviews()->avg('rating');
            }
        });
        return $products;
    }
    public function getProducts(Request $request)
    {
        // Xác định số trang (mặc định là 1)
        $pageNumber = $request->input('page', 1);

        // Số lượng sản phẩm trên mỗi trang
        $perPage = 10; // Bạn có thể điều chỉnh số này theo nhu cầu của mình

        // Lấy sản phẩm theo trang
        $products = Product::paginate($perPage, ['*'], 'page', $pageNumber);

        // Trả về dữ liệu
        return response()->json([
            'current_page' => $products->currentPage(),
            'per_page' => $products->perPage(),
            'total' => $products->total(),
            'data' => $products->items(),
        ]);
    }
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'brand' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'details' => 'required|string',
            'price' => 'required|numeric',
            'size' => 'required|string|max:50',
            'color' => 'required|string|max:50',
            'quantity' => 'required|integer',
            'photos' => 'nullable|array',
            'photos.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $data = null;
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $name = time() . '_' . $photo->getClientOriginalName(); // Use '_' instead of '.'
                $photo->move('img', $name); // Make sure 'img' directory exists and has write permissions
                $data[] = $name;
            }
        }

        $product = Product::create([
            'user_id' => auth()->id(), // Assuming you have authenticated user and you want to associate with user_id
            'category_id' => $request->category_id,
            'photo' => json_encode($data),
            'brand' => $request->brand,
            'name' => $request->name,
            'description' => $request->description,
            'details' => $request->details,
            'price' => $request->price,
        ]);

        Stock::create([
            'product_id' => $product->id,
            'size' => $request->size,
            'color' => $request->color,
            'quantity' => $request->quantity,
        ]);

        return response()->json([
            'message' => 'Product created successfully',
            'product' => $product,
        ], 201);
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        if ($product) {
            if ($product->photo != null) {
                foreach (json_decode($product->photo) as $photo) {
                    unlink(public_path() . '\\img\\' . $photo);
                }
            }
            $product->delete();
        }
    }
    public function search(Request $request)
    {
        \Log::info('Search method called');

        $query = $request->input('query');
        \Log::info('Searching for: ' . $query);

        // Tìm kiếm chỉ theo tên sản phẩm
        $product = Product::where('name', 'like', "%$query%")->get();

        \Log::info('Products found: ' . $product->count());

        return response()->json($product);
    }
}
