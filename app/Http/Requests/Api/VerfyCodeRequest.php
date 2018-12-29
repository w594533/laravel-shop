<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Validator;

class VerfyCodeRequest extends FormRequest
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
        // dd($this->request->all());
        $v = Validator::make($this->request->all(), [
            'email' => 'required|email',
            'games' => 'required|numeric',
        ]);

        // $v->sometimes('phone', 'required|exists:users', function($input){
        //     return $input->type === 'login';
        // });

        // $v->sometimes('phone', 'required|unique:users', function($input){
        //     return $input->type === 'register';
        // });
        return $v;
    }

    public function messages()
    {
        return [
            'phone.exists' => '用户不存在'
        ];
    }
}
