<?php

namespace App\Http\Requests;

use App\Containers\AppSection\User\Models\Account;
use Illuminate\Foundation\Http\FormRequest;

class TransactionUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {

        // only allow updates if the user is logged in
        return backpack_auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        //If the request has information (i.e while doing the operation - Creating)
        if (isset($this->to_account)) {
            //Skip to default behavior if the request is not 'Receive'
            if ($this->type == 'Receive') {
                return [
                    'is_completed' => 'required',
                    'money' => 'required|numeric|min:0',
                ];
            }
        }

        //Default behavior, or when the request has no information (i.e access the update-page).
        return [
            'from_account' => 'required|exists:accounts_of_user,id',
            'to_account' => 'required|exists:accounts_of_user,id',
            'type' => 'required',
            'money' => 'required|numeric|min:0',
        ];
    }

    /**
     * Get the validation attributes that apply to the request.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            //
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (isset($this->to_account) && $this->type == 'Receive') {
                $validator->errors()->add('money', __('Cần ít nhất ' . $this->money .' VND trong tài khoản!'));
            }
        });
    }

    private function hasMaxImages()
    {
        // return Account::find($this->from_account)->money < $this->money;

        return false;
    }

    /**
     * Get the validation messages that apply to the request.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'money.min' => "Số không hợp lệ!",
            'is_completed.required' => 'Xác thực đi!',
        ];
    }
}
