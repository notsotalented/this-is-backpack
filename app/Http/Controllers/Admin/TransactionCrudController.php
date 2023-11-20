<?php

namespace App\Http\Controllers\Admin;

use App\Containers\AppSection\User\Models\Account;
use App\Containers\AppSection\User\Models\Transaction;
use App\Containers\AppSection\User\Models\User;
use App\Http\Requests\TransactionRequest;
use App\Http\Requests\TransactionCreateRequest;
use App\Http\Requests\TransactionUpdateRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class TransactionCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class TransactionCrudController extends CrudController
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
                0 => 'Pending',
                1 => 'Completed',
            ],
            'wrapper' => [
                'element' => 'span',
                'class'   => function ($crud, $column, $entry, $related_key) {
                    if ($column['text'] == 'Completed')
                        return 'badge text-bg-success';
                    return 'badge text-bg-warning';
                },
            ]
        ]);
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

        CRUD::addField([
            'name'       => 'Testing',
            'label'      => 'TestTestTest',
            'type'       => 'text',
            'value'      => '<span class="badge text-bg-warning">Test</span>',
            'attributes' => [
                'required' => true,
            ],
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
        //$this->setupCreateOperation();
        CRUD::setValidation(TransactionUpdateRequest::class);

        CRUD::addField([
            'name'       => 'from_account',
            'label'      => 'From Account',
            'type'       => 'hidden',
            'default'    => function ($crud, $column, $entry, $related_key) {
                return $column['text'];
            },

            'attributes' => [
                'readonly' => 'readonly',
            ],
        ]);

        CRUD::addField([
            'name'       => 'to_account',
            'label'      => 'To Account',
            'type'       => 'hidden',
            'default'    => function ($crud, $column, $entry, $related_key) {
                return $column['text'];
            },
            'attributes' => [
                'readonly' => 'readonly',
            ],
        ]);

        CRUD::addField([
            'name'       => 'type',
            'label'      => 'Type',
            'type'       => 'radio',
            'options'    => [
                'Transfer'          => 'Transfer',
                'Receive'           => 'Receive',
                'Internal Transfer' => 'Internal Transfer',
            ],
            'attributes' => [
                'required' => true,
                'readonly' => true,
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
    }

    /**
     * Define what happens when the Update operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     * @return void
     */
    // protected function setupUpdateOperation()
    // {
    //     $this->setupCreateOperation();
    //     CRUD::setValidation(TransactionCreateRequest::class);
    // }
}
