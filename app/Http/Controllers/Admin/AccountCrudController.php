<?php

namespace App\Http\Controllers\Admin;

use App\Containers\AppSection\User\Models\Account;
use App\Containers\AppSection\User\Models\User;
use App\Http\Requests\AccountRequest;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class AccountCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class AccountCrudController extends CrudController
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
        CRUD::setModel(Account::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/account');
        CRUD::setEntityNameStrings('account', 'accounts');
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
        $this->crud->addColumns([
            [
                'name' => 'name',
                'label' => 'Name',
                'type' => 'text',
            ],
            [
                'name' => 'money',
                'label' => 'Money (VND)',
                'type' => 'number',
            ],
            [
                'name' => 'type',
                'label' => 'Type',
                'type' => 'text',
            ],
            [
                'label' => 'Belongs To',
                'type' => 'select',
                'name' => 'belongs_to',
                'entity' => 'getOwner',
                'attribute' => 'name',
                'model' => 'App\Containers\AppSection\User\Models\User',
                'wrapper' => [
                    'href' => function ($crud, $column, $entry, $related_key) {
                        return backpack_url('user/' . $related_key . '/show');
                    },
                ],
            ],
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
        CRUD::setValidation(AccountRequest::class);
        //CRUD::setFromDb(); // set fields from db columns.

        /**
         * Fields can be defined using the fluent syntax:
         * - CRUD::field('price')->type('number');
         */

        CRUD::field([
            'name' => 'belongs_to',
            'label' => 'Belongs To',
            'type' => 'hidden',
            'default' => \Auth::user()->id,

            'attributes' => [
                'readonly' => 'readonly',
            ],
        ]);

        CRUD::field([
            'name' => 'fake_name',
            'label' => 'Belongs To',
            'type' => 'text',
            'default' => \Auth::user()->name,
            'attributes' => [
                'readonly' => 'readonly',
            ],
        ]);

        CRUD::field([
            'name' => 'name',
            'label' => 'Name',
            'type' => 'text',
            'attributes' => [
                'required' => true,
            ],
        ]);

        CRUD::field([
            'name' => 'type',
            'label' => 'Type',
            'type' => 'radio',
            'options' => [
                'Cash' => 'Cash',
                'Bank' => 'Bank',
                'Credit' => 'Credit',
                'eWallet'=> 'eWallet',
            ],
            'attributes' => [
                'required' => true,
                'inline' => true,
            ]
        ]);

        CRUD::field([
            'name' => 'money',
            'label' => 'Money (VND)',
            'type' => 'number',
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
        $this->setupCreateOperation();
    }
}
