<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductAdd extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
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
            'price' => 'required|numeric|min:0',
            'qty' => 'required|numeric|min:0',
            'product_category' => 'required|string|max:255',
            'product_category_id' => 'required',
            'stock_status' => 'required|string|max:255',
            'availability_status' => 'required|string|max:255',
            'product_description' => 'required|string',
            // 'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'The name field is required.',
            'name.max' => 'The name may not be greater than 255 characters.',
            'price.required' => 'The price field is required.',
            'price.numeric' => 'The price must be a number.',
            'price.min' => 'The price must be at least 0.',
            'qty.required' => 'The price field is required.',
            'qty.numeric' => 'The price must be a number.',
            'qty.min' => 'The price must be at least 0.',
            // 'product_category.required' => 'The product category field is required.',
            'product_category_id.required' => 'The product category field is required.',
            // 'product_category.max' => 'The product category may not be greater than 255 characters.',
            'stock_status.required' => 'The stock status field is required.',
            'stock_status.max' => 'The stock status may not be greater than 255 characters.',
            'availability_status.required' => 'The availability status field is required.',
            'availability_status.max' => 'The availability status may not be greater than 255 characters.',
            'product_description.required' => 'The product description field is required.',
            // 'image.required' => 'The image field is required.',
            // 'image.image' => 'The file must be an image.',
            // 'image.mimes' => 'The image must be a file of type: jpeg, png, jpg, gif.',
            // 'image.max' => 'The image may not be greater than 2048 kilobytes.',
        ];
    }
}
