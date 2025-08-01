<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\UploadedFile; // Import UploadedFile for type hinting

class ProductService
{
    /**
     * Get products with pagination and category eager loaded.
     */
    public function getProducts(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = Product::with('category');

        if (isset($filters['search']) && $filters['search']) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('description', 'like', '%' . $filters['search'] . '%');
            });
        }
        if (isset($filters['category_id']) && $filters['category_id'] !== null) {
            $query->where('category_id', $filters['category_id']);
        }
        if (array_key_exists('is_active', $filters) && $filters['is_active'] !== null) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->paginate($perPage);
    }

    /**
     * Create a new product.
     */
    public function createProduct(array $data): Product
    {
        // For creation, if 'image' is provided and is a file, store it. Otherwise, it's null.
        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            $imagePath = $data['image']->store('products', 'public');
            $data['image'] = Storage::url($imagePath);
        } else {
            $data['image'] = null; // Ensure it's explicitly null if no file uploaded
        }

        // Remove should_remove_image as it's not a DB field for creation
        unset($data['should_remove_image']);

        $product = Product::create($data);
        $this->clearProductCache();
        return $product;
    }

    /**
     * Find a product by ID with eager loaded category.
     */
    public function findProduct(int $id): ?Product
    {
        return Product::with('category')->find($id);
    }

    /**
     * Update an existing product.
     */
    public function updateProduct(Product $product, array $data): bool
    {
        // IMPORTANT: Handle image logic based on what was sent from the frontend
        $updateImageField = false; // Flag to indicate if 'image' column should be updated

        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            // Case 1: New file uploaded. Delete old, store new.
            if ($product->image) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $product->image));
            }
            $imagePath = $data['image']->store('products', 'public');
            $data['image'] = Storage::url($imagePath);
            $updateImageField = true;
        } elseif (isset($data['should_remove_image']) && $data['should_remove_image'] === true) {
            // Case 2: User explicitly clicked 'Remove Image'. Delete old, set to null.
            if ($product->image) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $product->image));
            }
            $data['image'] = null;
            $updateImageField = true;
        } else {
            // Case 3: 'image' field not sent (user didn't touch file input)
            // OR 'image' was sent but was not an UploadedFile and should_remove_image is false (this covers no new file, no explicit removal)
            // In these cases, we do NOT want to update the 'image' column, preserving its existing value.
            unset($data['image']); // Remove from $data so Eloquent doesn't try to update it
        }

        // Remove the `should_remove_image` flag as it's not a database column
        unset($data['should_remove_image']);

        $updated = $product->update($data); // Directly update with the modified $data array
        if ($updated) {
            $this->clearProductCache();
        }
        return $updated;
    }

    /**
     * Delete a product.
     */
    public function deleteProduct(Product $product): bool
    {
        $deleted = $product->delete();
        if ($deleted) {
            if ($product->image) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $product->image));
            }
            $this->clearProductCache();
        }
        return $deleted;
    }

    /**
     * Clear product related caches.
     */
    protected function clearProductCache(): void
    {
        Cache::forget('customer_menu');
    }
}
