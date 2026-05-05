<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEnhancedProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Allow all authenticated users for now
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'product_nickname' => 'nullable|string|max:255',
            'slug' => 'nullable|string|max:255',
            'product_id' => 'nullable|string|max:100',
            'barcode' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'category' => 'required|string|max:100',
            'brand' => 'nullable|string|max:100',
            'material' => 'nullable|string|max:100',
            'printing_type' => 'nullable|string|max:100',
            'buying_price' => 'required|numeric|min:0',
            'retail_base_price' => 'nullable|numeric|min:0',
            'b2b_base_price' => 'nullable|numeric|min:0',
            'retail_visible' => 'nullable|boolean',
            'wholesale_visible' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'customization_allowed' => 'nullable|boolean',
            'weight' => 'nullable|numeric|min:0',
            'dimensions' => 'nullable|string|max:100',
            'availability' => 'nullable|string|in:in_stock,out_of_stock,custom',
            'features' => 'nullable|string',
            'track_stock' => 'nullable|boolean',
            'stock_quantity' => 'required|integer|min:0',
            'stock_unit' => 'required|string|in:pcs,m',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'min_quantity' => 'nullable|integer|min:1',
            'max_quantity' => 'nullable|integer|min:1',
            'meta_title' => 'nullable|string|max:60',
            'meta_description' => 'nullable|string|max:160',

            // Variants validation (Dynamic System)
            'variants' => 'nullable|array',
            'variants.*.category' => 'required_with:variants.*|string|max:255',
            'variants.*.options' => 'required_with:variants.*|array',
            'variants.*.options.*.name' => 'required_with:variants.*.options.*|string|max:255',
            'variants.*.options.*.description' => 'nullable|string',
            'variants.*.options.*.price' => 'nullable|numeric|min:0',
            'variants.*.options.*.color_code' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',

            // Images validation
            'images' => 'nullable|array|max:5',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:2048',

            // Price tiers validation
            'price_tiers' => 'nullable|array',
            'price_tiers.*.customer_type' => 'required_with:price_tiers|in:retail,wholesale',
            'price_tiers.*.min_quantity' => 'required_with:price_tiers|integer|min:1',
            'price_tiers.*.max_quantity' => 'nullable|integer|min:1',
            'price_tiers.*.price_per_unit' => 'required_with:price_tiers|numeric|min:0',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Product name is required.',
            'buying_price.required' => 'Buying price is required.',
            'buying_price.numeric' => 'Buying price must be a valid number.',
            'buying_price.min' => 'Buying price must be at least 0.',
            'stock_quantity.required' => 'Stock quantity is required.',
            'stock_quantity.integer' => 'Stock quantity must be a whole number.',
            'stock_quantity.min' => 'Stock quantity must be at least 0.',
            'variants.*.category.required' => 'Variant category is required.',
            'variants.*.options.required' => 'At least one variant option is required.',
            'variants.*.options.*.name.required' => 'Variant option name is required.',
            'variants.*.options.*.price.numeric' => 'Variant price must be a valid number.',
            'images.max' => 'You can upload maximum 5 images.',
            'images.*.image' => 'Each file must be an image.',
            'images.*.mimes' => 'Images must be in JPEG, PNG, JPG, or WebP format.',
            'images.*.max' => 'Each image must not exceed 2MB.',
            'price_tiers.*.customer_type.required_with' => 'Customer type is required for each price tier.',
            'price_tiers.*.customer_type.in' => 'Customer type must be either retail or wholesale.',
            'price_tiers.*.min_quantity.required_with' => 'Minimum quantity is required for each price tier.',
            'price_tiers.*.min_quantity.integer' => 'Minimum quantity must be a whole number.',
            'price_tiers.*.min_quantity.min' => 'Minimum quantity must be at least 1.',
            'price_tiers.*.max_quantity.integer' => 'Maximum quantity must be a whole number.',
            'price_tiers.*.max_quantity.min' => 'Maximum quantity must be at least 1.',
            'price_tiers.*.price_per_unit.required_with' => 'Price per unit is required for each price tier.',
            'price_tiers.*.price_per_unit.numeric' => 'Price per unit must be a valid number.',
            'price_tiers.*.price_per_unit.min' => 'Price per unit must be at least 0.',
        ];
    }
}
