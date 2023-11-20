<?php

namespace App\Http\Requests;

use App\Containers\AppSection\User\Models\Account;
use Illuminate\Foundation\Http\FormRequest;

class TransactionCreateRequest extends FormRequest
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
        if (isset($this->from_account)) {
            //Skip to default behavior if the request is 'Receive'
            if ($this->type == 'Transfer' || $this->type == 'Internal Transfer') {
                return [
                    'from_account' => 'required|exists:accounts_of_user,id',
                    'to_account' => 'required|exists:accounts_of_user,id',
                    'type' => 'required',
                    'money' => 'required|numeric|min:0|max:' . Account::find($this->from_account)->money,
                ];
            }
        }

        //Default behavior, or when the request has no information (i.e access the create-page).
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

    /**
     * Get the validation messages that apply to the request.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'from_account.exists' => 'Tìm hổng thấy!',
            'to_account.exists' => 'Tìm hổng ra, có lộn không!?',
            'money.max' => 'Không đủ tiền!',
        ];
    }
}
