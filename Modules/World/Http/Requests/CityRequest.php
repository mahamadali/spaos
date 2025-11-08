<?php

namespace Modules\World\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CityRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => ['required', 'string', 'regex:/^\S+.*\S+$|^\S+$/'],
            'country' => ['required'],
            'state_id' => ['required'],
        ];
    }

    public function messages()
    {
        return [
            'name.regex' => 'City name cannot contain only spaces.',
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
