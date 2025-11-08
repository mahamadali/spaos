<?php

namespace Modules\Promotion\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PromotionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {

        $rules = [
            'name' => 'required|unique:promotions,name|string|max:255',
        ];

        // Conditionally add unique rule for coupon_code based on coupon_type
        if ($this->input('coupon_type') === 'custom' && auth()->user()->hasRole('admin')) {
            $rules['coupon_code'] = [
                'required',
                'string',
                'max:255',
                'regex:/^\S+.*\S+$|^\S+$/', // Must not be only spaces
                Rule::unique('promotions_coupon', 'coupon_code')->ignore($this->route('promotion')), // Ignore current promotion ID if updating
            ];
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'coupon_code.unique' => 'Coupon code must be unique.',
            'coupon_code.regex' => 'Coupon code cannot contain only spaces.',
            'plan_id.required' => 'Please select at least one plan.',
            'plan_id.array' => 'Plan IDs must be an array.',
            'plan_id.*.integer' => 'Each plan ID must be an integer.',
            'name.unique' => 'This Coupon Name is already taken',

        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
