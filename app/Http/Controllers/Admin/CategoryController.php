<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use App\Services\CategoryService;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class CategoryController extends Controller
{
    protected CategoryService $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    public function index(): Response
    {
        $categories = $this->categoryService->getCategories(10);
        return Inertia::render('admin/categories/Index', [
            'categories' => $categories,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/categories/Create');
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        try {
            $this->categoryService->createCategory($request->validated());
            return redirect()->route('admin.products.index');
        } catch (\Exception $e) {
            Log::error('Error creating category: ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->back()->withErrors(['message' => 'Failed to create category. Please try again.']);
        }
    }

    public function show(Category $category): Response
    {
        return Inertia::render('admin/categories/Show', [
            'category' => $category,
        ]);
    }

    public function edit(Category $category): Response
    {
        return Inertia::render('admin/categories/Edit', [
            'category' => $category,
        ]);
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse|JsonResponse
    {
        try {
            $this->categoryService->updateCategory($category, $request->validated());
            return redirect()->back();
        } catch (\Exception $e) {
            Log::error('Error updating category: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'Failed to update category. Please try again.'], 500);
        }
    }

    public function destroy(Category $category): RedirectResponse|JsonResponse
    {
        try {
            $this->categoryService->deleteCategory($category);
            return redirect()->back();
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() === '23000') {
                Log::warning('Attempted to delete category with associated products (FK violation): ' . $category->id);
                return response()->json(['message' => 'Cannot delete category: it still has associated products.'], 409);
            }
            Log::error('Database error deleting category: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'A database error occurred while trying to delete the category. Please try again.'], 500);
        } catch (\Exception $e) {
            Log::error('Unexpected error deleting category: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'Failed to delete category due to an unexpected error. Please try again.'], 500);
        }
    }
}
