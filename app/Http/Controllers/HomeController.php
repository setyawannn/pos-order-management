<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index()
    {
        $products = Product::with('category')
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'category_id' => $product->category_id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'price' => (float) $product->price,
                    'image' => $product->image,
                    'stock' => $product->stock,
                    'is_stock_managed' => $product->is_stock_managed,
                    'is_active' => $product->is_active,
                    'category' => $product->category ? [
                        'id' => $product->category->id,
                        'name' => $product->category->name,
                    ] : null,
                ];
            });

        $categories = Category::orderBy('name')->get();

        return Inertia::render('user/home/Index', [
            'products' => $products,
            'categories' => $categories,
        ]);
    }
}
