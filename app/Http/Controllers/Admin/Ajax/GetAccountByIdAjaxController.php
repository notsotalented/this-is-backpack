<?php
namespace App\Http\Controllers\Admin\Ajax;

use App\Containers\AppSection\User\Models\Account;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Illuminate\Http\Request;

class GetAccountByIdAjaxController extends CrudController
{
    public function __invoke(Request $request)
    {
        $id      = $request->input('id');
        $account = Account::find($id);
        if ($account) {
            return response()->json([
                'status' => 'success',
                'data'   => $account
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
