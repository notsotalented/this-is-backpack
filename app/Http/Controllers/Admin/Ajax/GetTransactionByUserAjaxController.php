<?php
namespace App\Http\Controllers\Admin\Ajax;

use App\Containers\AppSection\User\Models\Transaction;
use App\Containers\AppSection\User\Models\User;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Illuminate\Http\Request;

class GetTransactionByUserAjaxController extends CrudController
{
    public function __invoke(Request $request)
    {
        $pa_ids       = \Arr::pluck(User::find(\Auth::user()->id)->ownAccounts, 'id');
        $transactions = Transaction::where(function ($query) use ($pa_ids) {
            $query->whereIn('from_account', $pa_ids)
                ->orWhereIn('to_account', $pa_ids);
        })
            ->whereYear('created_at', $request->input('year'))
            ->whereMonth('created_at', $request->input('month'))
            ->where('is_completed', $request->input('is_completed'))
            ->where('type', $request->input('type'))
            ->get();

        if ($transactions) {
            return response()->json([
                'status' => 'success',
                'data'   => $transactions
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'data'   => null
            ]);
        }
    }
}
?>