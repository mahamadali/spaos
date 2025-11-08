<?php

namespace Modules\Customer\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class customerRequest extends FormRequest
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
                    'first_name' => 'required|string|max:255',
                    'last_name' => 'required|string',
                    'email' => ['required', 'string', Rule::unique('users', 'email')->whereNull('deleted_at')],
                    'mobile' => 'required|string',
                    'gender' => 'string',
                ];
                break;
            case 'put':
            case 'patch':
                $customerId = $this->route('customer');
                return [
                    'first_name' => 'required|string|max:255',
                    'last_name' => 'required|string',
                    'email' => ['required', 'string', Rule::unique('users', 'email')->ignore($customerId)->whereNull('deleted_at')],
                    'mobile' => 'required|string',
                    'gender' => 'string',
                ];
                break;
            default:
                return [];
                break;
        }
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
