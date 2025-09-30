<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAssetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->hasAnyRole(['Auditor', 'System', 'Super Admin']);
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
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:asset_categories,id',
            'department_id' => 'nullable|exists:departments,id',
            'location' => 'nullable|string|max:255',
            'purchase_date' => 'nullable|date|before_or_equal:today',
            'purchase_price' => 'nullable|numeric|min:0|max:9999999.99',
            'current_value' => 'nullable|numeric|min:0|max:9999999.99',
            'state' => 'required|in:new,in_use,damaged,repaired,disposed,lost',
            'assigned_to_type' => 'nullable|string|max:255',
            'assigned_to_id' => 'nullable|integer|min:1',
            'notes' => 'nullable|string|max:10000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The asset name is required.',
            'purchase_date.before_or_equal' => 'The purchase date cannot be in the future.',
            'purchase_price.numeric' => 'The purchase price must be a valid number.',
            'current_value.numeric' => 'The current value must be a valid number.',
            'state.in' => 'Please select a valid asset state.',
        ];
    }
}