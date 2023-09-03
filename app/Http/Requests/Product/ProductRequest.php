<?php

namespace App\Http\Requests\Product;

use App\Rules\ValidQuantity;
use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|unique:products|min:3|max:255',
            'quantity' => ['required', new ValidQuantity],
            'amount' =>'required|numeric|min:0'
        ];
    }
}
