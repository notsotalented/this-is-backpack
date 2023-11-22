<?php

use Illuminate\Support\Facades\Route;

// --------------------------
// Custom Backpack Routes
// --------------------------
// This route file is loaded automatically by Backpack\Base.
// Routes you generate using Backpack\Generators will be placed here.

Route::group([
    'prefix'     => config('backpack.base.route_prefix', 'admin'),
    'middleware' => array_merge(
        (array) config('backpack.base.web_middleware', 'web'),
        (array) config('backpack.base.middleware_key', 'admin')
    ),
    'namespace'  => 'App\Http\Controllers\Admin',
], function () { // custom admin routes
    Route::crud('user', 'UserCrudController');
    Route::crud('article', 'ArticleCrudController');
    Route::crud('category', 'CategoryCrudController');
    Route::crud('tag', 'TagCrudController');
    Route::crud('user-u-u-i-d', 'UserUUIDCrudController');
    Route::crud('permission', 'PermissionCrudController');
    Route::crud('role', 'RoleCrudController');
    Route::crud('account', 'AccountCrudController');
    Route::crud('transaction', 'TransactionCrudController');

    Route::post('handle_payment', 'PaymentController@handlePayment')->name('handle_payment');
}); // this should be the absolute last line of this file

// Compare this snippet from app/Containers/AppSection/User/Models/Transaction.php:
Route::group([
    'prefix'     => config('backpack.base.route_prefix', 'admin'),
    'middleware' => array_merge(
        (array) config('backpack.base.web_middleware', 'web'),
        (array) config('backpack.base.middleware_key', 'admin')
    ),
    'namespace'  => 'App\Http\Controllers\Admin\Ajax',
], function () { // custom admin routes
    Route::get('get_account_by_id_ajax', 'GetAccountByIdAjaxController')->name('get_account_by_id_ajax');
}); // this should be the absolute last line of this file
