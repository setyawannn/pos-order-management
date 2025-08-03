<?php
// app/Http/Requests/StoreOrderRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'required|string|max:20',
            'order_type' => ['required', Rule::in(['dine_in', 'take_away'])],
            'table_number' => 'required_if:order_type,dine_in|nullable|string|max:10',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1|max:99',
            'items.*.notes' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'customer_name.required' => 'Customer name is required.',
            'customer_email.required' => 'Email is required.',
            'customer_email.email' => 'Please enter a valid email address.',
            'customer_phone.required' => 'Phone number is required.',
            'order_type.required' => 'Please select an order type.',
            'order_type.in' => 'Order type must be either dine in or take away.',
            'table_number.required_if' => 'Table number is required for dine in orders.',
            'items.required' => 'At least one item must be selected.',
            'items.min' => 'At least one item must be selected.',
            'items.*.product_id.required' => 'Product selection is required.',
            'items.*.product_id.exists' => 'Selected product does not exist.',
            'items.*.quantity.required' => 'Quantity is required for each item.',
            'items.*.quantity.min' => 'Quantity must be at least 1.',
            'items.*.quantity.max' => 'Quantity cannot exceed 99.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('customer_phone')) {
            $this->merge([
                'customer_phone' => preg_replace('/[^0-9+]/', '', $this->customer_phone)
            ]);
        }

        if ($this->has('customer_email')) {
            $this->merge([
                'customer_email' => strtolower(trim($this->customer_email))
            ]);
        }
    }
}
