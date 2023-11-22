<?php

namespace App\Http\Controllers\Admin;

use App\Containers\AppSection\User\Models\Account;
use App\Containers\AppSection\User\Models\Transaction;
use App\Containers\AppSection\User\Models\User;
use App\Http\Requests\TransactionCreateRequest;
use App\Http\Requests\TransactionUpdateRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

use App\Http\Controllers\Admin\PaymentController;

/**
 * Class TransactionCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class TransactionCrudController extends PaymentController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(Transaction::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/transaction');
        CRUD::setEntityNameStrings('transaction', 'transactions');
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        //CRUD::setFromDb(); // set columns from db columns.

        /**
         * Columns can be defined using the fluent syntax:
         * - CRUD::column('price')->type('number');
         */

        CRUD::addColumn([
            'name'      => 'from_account',
            'label'     => 'From Account',
            'type'      => 'select',
            'entity'    => 'getFrom',
            'model'     => Account::class,
            'attribute' => 'name',
            'wrapper'   => [
                'href' => function ($crud, $column, $entry, $related_key) {
                    return backpack_url('account/' . $related_key . '/show');
                },
            ],
        ]);

        CRUD::column('type');

        CRUD::column('money');

        CRUD::addColumn([
            'name'      => 'to_account',
            'label'     => 'To Account',
            'type'      => 'select',
            'entity'    => 'getTo',
            'model'     => Account::class,
            'attribute' => 'name',
            'wrapper'   => [
                'href' => function ($crud, $column, $entry, $related_key) {
                    return backpack_url('account/' . $related_key . '/show');
                },
            ],
        ]);

        CRUD::addColumn([
            'name'    => 'is_completed',
            'label'   => 'Status',
            'type'    => 'boolean',
            'options' => [
                0 => 'Error',
                1 => 'Completed',
            ],
            'wrapper' => [
                'element' => 'span',
                'class'   => function ($crud, $column, $entry, $related_key) {
                    if ($column['text'] == 'Completed')
                        return 'badge text-bg-success';
                    return 'badge text-bg-danger';
                },
            ]
        ]);

        $pa_id = \Arr::pluck(User::find(\Auth::user()->id)->ownAccounts, 'id');
        CRUD::addBaseClause(function ($query) use ($pa_id) {
            $query->whereIn('from_account', $pa_id);
            $query->orWhereIn('to_account', $pa_id);
        });
    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(TransactionCreateRequest::class);
        //CRUD::setFromDb(); // set fields from db columns.

        /**
         * Fields can be defined using the fluent syntax:
         * - CRUD::addField('price')->type('number');
         */

        $personal_accounts = User::find(\Auth::user()->id)->ownAccounts;
        //Array processing
        $pa_name  = \Arr::pluck($personal_accounts, 'name');
        $pa_id    = \Arr::pluck($personal_accounts, 'id');
        $pa_money = \Arr::pluck($personal_accounts, 'money');

        CRUD::addField([
            'suffix'     => 'VND',
            'name'       => 'from_account',
            'label'      => 'From Account',
            'default'    => $pa_id[0] ?? null,
            'type'       => 'radio',
            'options'    => array_combine(
                $pa_id,
                array_map(function ($value1, $value2) {
                    return $value1 . ' <span class="badge text-bg-success">' . $value2 . ' VND</span>';
                }, $pa_name, $pa_money),
            ),
            'attributes' => [
                'required' => true,
            ],
        ]);

        CRUD::addField([
            'name'       => 'type',
            'label'      => 'Type',
            'type'       => 'radio',
            'default'    => 'Transfer',
            'options'    => [
                'Transfer'          => 'Transfer',
                'Receive'           => 'Receive',
                'Internal Transfer' => 'Internal Transfer',
            ],
            'attributes' => [
                'required' => true,
            ],
        ]);

        CRUD::addField([
            'name'       => 'money',
            'label'      => 'Money',
            'prefix'     => 'VND',
            'type'       => 'number',
            'attributes' => [
                'required' => true,
                'min'      => 0,
            ],
        ]);

        CRUD::addField([
            'name'           => 'to_account',
            'label'          => 'To Account',
            // 'hint'           => 'Use the ID of the account',
            'attributes'     => [
                'required' => true,
            ],
            'type'           => 'get_account_by_id_ajax',
            'view_namespace' => file_exists(resource_path('views/vendor/backpack/crud/fields/get_account_by_id_ajax')),
        ]);

        CRUD::addSaveAction([
            'name'         => 'save_and_pay', // name of the button
            'redirect'     => function ($crud, $request, $itemId) {
                return $this->handlePayment($request, $itemId) ? $crud->route : (
                    $crud->route . '/' . $itemId . '/edit'
                );
            }, // what's the redirect URL, where the user will be taken after saving?

            // OPTIONAL:
            'button_text'  => 'Payment and Save', // override text appearing on the button

            'visible'      => function ($crud) {
                return true;
            }, // customize when this save action is visible for the current operation
            'referrer_url' => function ($crud, $request, $itemId) {
                return $crud->route;
            }, // override http_referrer_url
            'order'        => 1, // change the order save actions are in
        ]);

    }

    /**
     * Define what happens when the Update operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     * @return void
     */
    protected function setupUpdateOperation()
    {
        CRUD::setValidation(TransactionUpdateRequest::class);

        CRUD::addField([
            'tab'   => 'Information',
            'label' => 'From',
            'type'  => 'hidden',
            'name'  => 'from_account',
        ]);

        CRUD::addField([
            'tab'   => 'Information',
            'label' => 'To',
            'type'  => 'hidden',
            'name'  => 'to_account',
        ]);

        CRUD::addField([
            'tab'        => 'Information',
            'label'      => 'From',
            'type'       => 'select',
            'name'       => 'from_acc',
            'entity'     => 'getFrom',
            'hint'       => 'Use the ID of the account',
            'attribute'  => 'name',
            'attributes' => [
                'disabled' => true,
            ],
        ]);

        CRUD::addField([
            'tab'        => 'Information',
            'label'      => 'To',
            'type'       => 'select',
            'name'       => 'to_acc',
            'entity'     => 'getTo',
            'attribute'  => 'name',
            'attributes' => [
                'disabled' => true,
            ],
        ]);

        CRUD::addField([
            'tab'        => 'Information',
            'name'       => 'type',
            'type'       => 'text',
            'attributes' => [
                'required' => true,
                'readonly' => true,
            ],
        ]);

        CRUD::addField([
            'tab'        => 'Information',
            'name'       => 'money',
            'label'      => 'Money',
            'prefix'     => 'VND',
            'type'       => 'number',
            'attributes' => [
                'required' => true,
                'min'      => 0,
            ],
        ]);

        CRUD::addField([
            'tab'        => 'Confirmation',
            'name'       => 'is_completed',
            'label'      => 'Confirmation',
            'type'       => 'radio',
            'hint'       => 'Hãy suy nghĩ kĩ, bút sa gà chết. Không đổi được đâu',
            'options'    => [
                //0 => 'Not confirmed',
                1 => 'Đã xác nhận',
            ],
            'attributes' => [
                'required' => true,
            ],
        ]);

    }
}
