<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Services\ProductService;
use App\Services\CategoryService;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    protected ProductService $productService;
    protected CategoryService $categoryService;

    public function __construct(ProductService $productService, CategoryService $categoryService)
    {
        $this->productService = $productService;
        $this->categoryService = $categoryService;
    }

    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'category_id', 'is_active']);

        if (isset($filters['category_id'])) {
            $filters['category_id'] = (int) $filters['category_id'];
        }
        if (isset($filters['is_active'])) {
            $filters['is_active'] = match ($filters['is_active']) {
                'true' => true,
                'false' => false,
                default => null,
            };
        }

        $products = $this->productService->getProducts(10, $filters);
        $categories = $this->categoryService->getAllCategoriesForSelect();

        return Inertia::render('admin/products/Index', [
            'products' => $products,
            'categories' => $categories,
            'filters' => $filters,
        ]);
    }

    public function create(): Response
    {
        $categories = $this->categoryService->getAllCategoriesForSelect();
        return Inertia::render('admin/products/Create', [
            'categories' => $categories,
        ]);
    }

    public function store(StoreProductRequest $request): RedirectResponse|JsonResponse
    {
        try {
            $this->productService->createProduct($request->validated());
            return redirect()->route('admin.products.index');
        } catch (\Exception $e) {
            Log::error('Error creating product: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'Failed to create product. Please try again.'], 500);
        }
    }

    public function show(Product $product): Response
    {
        $product->load('category');
        return Inertia::render('admin/products/Show', [
            'product' => $product,
        ]);
    }

    public function edit(Product $product): Response
    {
        $product->load('category');
        $categories = $this->categoryService->getAllCategoriesForSelect();
        return Inertia::render('admin/products/Edit', [
            'product' => $product,
            'categories' => $categories,
        ]);
    }
    public function update(UpdateProductRequest $request, Product $product): RedirectResponse|JsonResponse
    {
        try {
            $this->productService->updateProduct($product, $request->validated());
            return redirect()->back();
        } catch (\Exception $e) {
            Log::error('Error updating product: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'Failed to update product. Please try again.'], 500);
        }
    }

    public function destroy(Product $product): RedirectResponse|JsonResponse
    {
        try {
            $this->productService->deleteProduct($product);
            return redirect()->back();
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() === '23000') {
                Log::warning('Attempted to delete product with associated order items (FK violation): ' . $product->id);
                return response()->json(['message' => 'Cannot delete product: it has associated order history. Consider marking it inactive instead.'], 409);
            }
            Log::error('Database error deleting product: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'A database error occurred while trying to delete the product. Please try again.'], 500);
        } catch (\Exception $e) {
            Log::error('Unexpected error deleting product: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'Failed to delete product due to an unexpected error. Please try again.'], 500);
        }
    }
}
