<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class CategoryService
{
    /**
     * Get all categories with pagination.
     */
    public function getCategories(int $perPage = 15): LengthAwarePaginator
    {
        return Category::paginate($perPage);
    }

    /**
     * Get all categories without pagination (for dropdowns etc.).
     * Cached for performance.
     */
    public function getAllCategoriesForSelect(): Collection
    {
        return Cache::remember('all_categories_for_select', now()->addHours(1), function () {
            return Category::select('id', 'name')->orderBy('name')->get();
        });
    }

    /**
     * Create a new category.
     */
    public function createCategory(array $data): Category
    {
        $category = Category::create($data);
        $this->clearCategoryCache(); // Bust cache
        return $category;
    }

    /**
     * Find a category by ID.
     */
    public function findCategory(int $id): ?Category
    {
        return Category::find($id);
    }

    /**
     * Update an existing category.
     */
    public function updateCategory(Category $category, array $data): bool
    {
        $updated = $category->update($data);
        if ($updated) {
            $this->clearCategoryCache(); // Bust cache
        }
        return $updated;
    }

    /**
     * Delete a category.
     */
    public function deleteCategory(Category $category): bool
    {
        $deleted = $category->delete();
        if ($deleted) {
            $this->clearCategoryCache(); // Bust cache
        }
        return $deleted;
    }

    /**
     * Clear category related caches.
     */
    protected function clearCategoryCache(): void
    {
        Cache::forget('all_categories_for_select');
    }
}
