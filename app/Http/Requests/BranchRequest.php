<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class BranchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        if ($this->isMethod('post')) {
            return [
                'name' => 'required|string|max:255',
                'branch_for' => 'required',
                'contact_number' => 'required|string|unique:branches,contact_number,NULL,id,deleted_at,NULL',
                'contact_email' => 'required|string|unique:branches,contact_email,NULL,id,deleted_at,NULL',
                'address.address_line_1' => 'string',
                'address.address_line_2' => 'string',
                'address.city' => 'string|regex:/^\S+.*\S+$|^\S+$/',
                'address.state' => 'string|regex:/^\S+.*\S+$|^\S+$/',
                'address.country' => 'string|regex:/^\S+.*\S+$|^\S+$/',
                'address.postal_code' => 'string',
                'payment_method' => 'required',
                'status' => 'boolean',
            ];
        }
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $branchId = $this->route('branch');
            return [
                'name' => 'required|string',
                'branch_for' => 'string',
                'contact_number' => 'required|string|unique:branches,contact_number,' . $branchId . ',id,deleted_at,NULL',
                'contact_email' => 'required|string|unique:branches,contact_email,' . $branchId . ',id,deleted_at,NULL',
                'address.address_line_1' => 'string',
                'address.address_line_2' => 'string',
                'address.city' => 'string|regex:/^\S+.*\S+$|^\S+$/',
                'address.state' => 'string|regex:/^\S+.*\S+$|^\S+$/',
                'address.country' => 'string|regex:/^\S+.*\S+$|^\S+$/',
                'address.postal_code' => 'string',
                'payment_method' => 'required',
                'status' => 'boolean',
            ];
        }
        return [];
    }

    public function messages()
    {
        return [
            'address.city.regex' => 'City cannot contain only spaces.',
            'address.state.regex' => 'State cannot contain only spaces.',
            'address.country.regex' => 'Country cannot contain only spaces.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $data = [
            'status' => false,
            'message' => $validator->errors()->first(),
            'all_message' => $validator->errors(),
        ];

        if (request()->wantsJson() || request()->is('api/*')) {
            throw new HttpResponseException(response()->json($data, 422));
        }

        throw new HttpResponseException(
            redirect()->back()->withErrors($validator)->withInput()
        );
    }
}
