<?php
namespace App\Http\Controllers\Admin;

use App\Containers\AppSection\User\Models\Account;
use App\Http\Requests\TransactionRequest;

use Backpack\CRUD\app\Http\Controllers\CrudController;


class PaymentController extends CrudController
{
    public function checkAccountBalanceBeforePayment(TransactionRequest $request)
    {
        try {
            $from_account = $request->input('from_account');
            $money        = $request->input('money');
            $account      = Account::find($from_account);

            return $account->money > $money;
        } catch (\Throwable $th) {
            // rollback transaction if something goes wrong (to avoid inconsistent states)
            \DB::rollback();
            throw $th;
        }
    }

    public function subMoneyFromAccount($id, $money)
    {
        try {

            $account        = Account::find($id);
            $account->money -= $money;
            $account->save();

            return true;
        } catch (\Throwable $th) {
            // rollback transaction if something goes wrong (to avoid inconsistent states)
            \DB::rollback();
            return false;
        }
    }

    public function addMoneyToAccount($id, $money)
    {
        try {
            $account        = Account::find($id);
            $account->money += $money;
            $account->save();

            return true;
        } catch (\Throwable $th) {
            // rollback transaction if something goes wrong (to avoid inconsistent states)
            \DB::rollback();
            return false;
        }
    }

    public function handlePayment(TransactionRequest $request)
    {
        $from_account = $request->input('from_account');
        $to_account   = $request->input('to_account');
        $money        = $request->input('money');
        $type         = $request->input('type');

        if ($type == 'Tranfer' || $type == 'Internal Tranfer') {
            if ($this->checkAccountBalanceBeforePayment($request)) {
                if ($this->subMoneyFromAccount($from_account, $money))
                    if ($this->addMoneyToAccount($to_account, $money))
                        return true;
            }
        }

        return false;
    }
}
?>