<?php

namespace App\Http\Requests;

use App\Containers\AppSection\User\Models\Account;
use App\Containers\AppSection\User\Models\Transaction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Request;
use Psy\Exception\ErrorException;

class TransactionUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        //Advanced
        if(isset($this->from_account)) {
            $login = backpack_auth()->check();

            //Case: Transfer (both) versus Receive
            if ($this->type == 'Transfer' || $this->type == 'Internal Transfer') {
                $owner = Account::findOrFail($this->from_account)->getOwner()->first();
            }
            else if ($this->type == 'Receive') {
                $owner = Account::findOrFail($this->to_account)->getOwner()->first();
            }

            //Compare target id with current logged in user id
            $checkOwner = $owner->id == \Auth::user()->id;

            return $login && $checkOwner;
            // dd($login && $checkOwner);
        }


        //Default
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
            $exists = Transaction::find($this->id);
            if($exists && $exists->is_completed == 1 && $this->is_completed == 0) {
                $validator->errors()->add('is_completed', 'Bịp thế xác nhận chuyển tiền xong suy nghĩ lại à?');
            }

            if (isset($this->to_account) && $this->type == 'Receive' && $this->money > Account::find($this->to_account)->money) {
                $validator->errors()->add('money', __('Cần ít nhất ' . $this->money .' VND trong tài khoản!'));
            }

            /*TO-DO*/
            //Relocate money, temporary leave in here.
            if (isset($this->to_account) && $exists->is_completed == 0 && $this->is_completed == 1) {
                $from = Account::findOrFail($this->from_account);
                $to = Account::findOrFail($this->to_account);

                if ($this->type == 'Transfer' || $this->type == 'Internal Transfer') {

                    $from->money -= $this->money;
                    $to->money += $this->money;
                }
                elseif ($this->type == 'Receive') {

                    $from->money += $this->money;
                    $to->money -= $this->money;

                }
                else {
                    $validator->errors()->add('type', 'Loại thao tác không hợp lệ!');
                }

                $from->save();
                $to->save();

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
