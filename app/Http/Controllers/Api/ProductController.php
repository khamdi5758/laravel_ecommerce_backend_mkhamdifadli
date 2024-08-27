<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Carbon\Carbon;

class ProductController extends Controller
{

    private $sekarang;
    public function __construct()
    {
        $now = Carbon::now();
        $nowind = $now->setTimezone('Asia/Jakarta');
        $this->sekarang = $nowind->format('ymdHis');
    }
    //index
    public function index(Request $request)
    {
        $products = Product::where('seller_id', $request->user()->id)->with('seller')->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Products',
            'data' => $products,
        ]);
    }

    //store
    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string',
            'description' => 'string',
            'price' => 'required',
            'stock' => 'required|integer',
            'image' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $image = null;
        if ($images = $request->file('image')) {
            // $image = $request->file('image')->store('assets/product', 'public');
            $destinationPath = 'products/';
            $file_name = $this->sekarang . '.' . request()->image->getClientOriginalExtension();
            $images->move($destinationPath, $file_name);
            $image = $destinationPath.$file_name;
        }

        $product = Product::create([
            'seller_id' => $request->user()->id,
            'category_id' => $request->category_id,
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'stock' => $request->stock,
            'image' => $image,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Product created',
            'data' => $product,
        ], 201);
    }

    //update
    public function update(Request $request, $id)
    {
        $request->validate([
            // 'category_id' => 'required|exists:categories,id',
            'name' => 'required|string',
            'description' => 'string',
            'price' => 'required',
            'stock' => 'required|integer',
            'image' => 'image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'status' => 'error',
                'message' => 'Product not found',
            ], 404);
        }

        if ($image = $request->file('image')) {
            $destinationPath = 'products/';
            $file_name = $this->sekarang . '.' . $image->getClientOriginalExtension();
            $image->move($destinationPath, $file_name);
    
            $pathimgold = $product->image;
            if (file_exists($pathimgold)) {
                @unlink($pathimgold);
            }
            $foto = $destinationPath.$file_name;
        } else {
            $foto = $product->image;
        }

        $product->update([
            'category_id' => $request->category_id ??  $request->categoryId,
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'stock' => $request->stock,
            'image' => $foto,
        ]);

        // if ($request->hasFile('image')) {
        //     $image = $request->file('image')->store('assets/product', 'public');
        //     $product->image = $image;
        //     $product->save();
        // }
       
        

        return response()->json([
            'status' => 'success',
            'message' => 'Product updated',
            'data' => $product,
        ]);
    }

    //destroy
    public function destroy($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'status' => 'error',
                'message' => 'Product not found',
            ], 404);
        }


        $pathimgold = $product->image;
        if (file_exists($pathimgold)) {
            @unlink($pathimgold);
        }
        $product->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Product deleted',
        ]);
    }
}
