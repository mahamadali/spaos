<?php

namespace Modules\Frontend\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
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
                    'username' => 'required|string|unique:users,username',
                    'first_name' => 'required|string|max:255',
                    'last_name' => 'required|string',
                    'email' => 'required|string|unique:users,email',
                    'mobile' => 'required|string|unique:users,mobile',
                    'password' => 'required|string|min:8|max:14|confirmed',
                    'gender' => 'string',
                    'slug'       => 'required|string|regex:/^[a-z0-9-]+$/|unique:users,slug|max:100',

                ];
                break;
            case 'put':
            case 'patch':
                return [
                    'username' => ['required|string', Rule::unique('users', 'username')->ignore($this->id)->whereNull('deleted_at')],
                    'first_name' => 'required|string|max:255',
                    'last_name' => 'required|string',
                    'email' => ['required', 'string', Rule::unique('users', 'email')->ignore($this->id)->whereNull('deleted_at')],
                    'mobile' => 'required|string|unique:users,mobile',
                    'gender' => 'string',
                     'slug'       => [
                        'required',
                        'string',
                        'regex:/^[a-z0-9-]+$/',
                        Rule::unique('users', 'slug')->ignore($this->id)->whereNull('deleted_at'),
                    ],
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
