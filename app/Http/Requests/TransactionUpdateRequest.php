<?php

namespace App\Http\Requests;

use App\Containers\AppSection\User\Models\Account;
use App\Containers\AppSection\User\Models\Transaction;
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
        //Note: isset($this->to_account) - True/false contributes to validate the form submitted.
        //      while $exist != NULL contributes to the update of the request (i.e Request confirmation).
        $validator->after(function ($validator) {
            $exists = Transaction::find($this->id);
            //Try to change other things via form
            if ($exists) {
                $keysToGet = ["id", "from_account", "to_account", "type", "money"];
                //Array processing
                $from_db = array_intersect_key($exists->toArray(), array_flip($keysToGet));
                $from_form = array_intersect_key($this->all(), array_flip($keysToGet));
                //Data comparison
                if ($from_db != $from_form) {
                    $validator->errors()->add('data_corrupted', 'Dữ liệu đã bị thay đổi, giao dịch không hợp lệ!');
                }
            }


            //Try to revert is_completed 1->0 via form request
            if($exists && $exists->is_completed == 1 && $this->is_completed == 0) {
                $validator->errors()->add('is_completed', 'Không thể hủy xác nhận giao dịch đã hoàn thành');
            }
            //CIH check before progress with the operation (insufficient money)
            if (isset($this->to_account) && $this->type == 'Receive' && $this->money > Account::find($this->to_account)->money) {
                $validator->errors()->add('money', __('Cần ít nhất ' . $this->money .' VND trong tài khoản!'));
            }

            //Relocate money, temporary leave in here.
            //Check valid action: is_completed = 0 in DB and is_completed = 1 in request
            if ($exists->is_completed == 0 && $this->is_completed == 1) {
                //Transfer type
                if ($this->type == 'Transfer' || $this->type == 'Internal Transfer') {
                    //Operation on from_account
                    $from = Account::findOrFail($this->from_account);
                    $from->money -= $this->money;
                    $from->save();
                    //Operation on to_account
                    $to = Account::findOrFail($this->to_account);
                    $to->money += $this->money;
                    $to->save();
                }
                //Receive type
                elseif ($this->type == 'Receive') {
                    //Operation on from_account
                    $from = Account::findOrFail($this->from_account);
                    $from->money += $this->money;
                    $from->save();
                    //Operation on to_account
                    $to = Account::findOrFail($this->to_account);
                    $to->money -= $this->money;
                    $to->save();
                }
                else {
                    $validator->errors()->add('type', 'Loại thao tác không hợp lệ!');
                }

            }

        });
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
