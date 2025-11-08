<?php

namespace Modules\Tax\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class taxRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        switch (strtolower($this->getMethod())) {
            case 'post':
                return [
                    'title' => ['required', 'string', function ($attribute, $value, $fail) {
                        if (trim($value) === '') {
                            $fail('Title cannot contain only spaces.');
                        }
                    }],
                    'type' => 'required|string',
                    'value' => 'required',
                    'module_type' => 'required|string|in:products,services',
                ];
                break;
            case 'put':
            case 'patch':
                $branchId = $this->route('branch');

                return [
                    'title' => ['required', 'string', function ($attribute, $value, $fail) {
                        if (trim($value) === '') {
                            $fail('Title cannot contain only spaces.');
                        }
                    }],
                    'type' => 'required|string',
                    'value' => 'required',
                    'module_type' => 'required|string|in:products,services',
                ];
                break;
            default:
                return [];
                break;
        }
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'title.regex' => 'Title cannot contain only spaces.',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }
}
