<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only allow 'admin' or 'owner' roles to manage products
        return Auth::check() && in_array(Auth::user()->role, ['admin', 'owner']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255|unique:products,name',
            'description' => 'nullable|string|max:1000',
            'price' => 'required|integer|min:0', // Use integer as IDR has no decimals
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // For image upload
            'stock' => 'required|integer|min:0', // Default to 0 from DB, but still required in request
            'is_stock_managed' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
