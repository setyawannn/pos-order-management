<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check() && in_array(Auth::user()->role, ['admin', 'owner']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $productId = $this->route('product')->id;

        return [
            'category_id' => 'required|exists:categories,id',
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products')->ignore($productId),
            ],
            'description' => 'nullable|string|max:1000',
            'price' => 'required|integer|min:0',
            'image' => 'nullable|sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'should_remove_image' => 'sometimes|boolean',
            'stock' => 'required|integer|min:0',
            'is_stock_managed' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
