<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateContactRequest extends FormRequest
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
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|min:3|max:255',
            'note' => 'max:255',
            'phones' => 'array|required',
            'phones.*' => 'required|min:11|numeric',


        ];
    }

    public function messages()
    {
        return [
            'name:min' => 'Название должно содержать более 3-ех символов',
            'phones.required' => 'Поле Телефон обязательно для заполнения',
            'phones.*.regex'  => 'Не верный формат номер телефона. Телефон должен быть в формате +7XXXXXXXXXX'
        ];
    }
}
