<?php

use App\Http\Controllers\API\AuthController;
// use App\Http\Controllers\API\TestController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\ItemCategoryController;
use App\Http\Controllers\API\ItemController;
use App\Http\Controllers\API\ModifierController;
use App\Http\Controllers\API\ModifierGroupController;
use App\Http\Controllers\API\SectionController;
// use App\Http\Middleware\Authenticate;
// use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\TableController;
use App\Http\Controllers\API\TaxFeeController;
use App\Http\Controllers\API\CustomerController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\KOTController;
use App\Http\Controllers\API\RoleController;
use App\Models\ItemCategory;
use App\Models\Section;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware(['guest'])->group(function () {
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/forgot-password', [AuthController::class, 'forgot_password']);
Route::post('/password/reset', [AuthController::class, 'reset_password'])->name('password.reset');
// });







// add in [] in middleware group to implement
Route::middleware(['auth:api'])->group(function () {

    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/userdata', [AuthController::class, 'me']);
    Route::get('/roles', [RoleController::class, 'getRoles']);
    Route::get('/allkots', [KOTController::class, 'all_kots']);
    Route::get('/categorylist', [ItemCategoryController::class, 'getList']);
    Route::get('/waiting-tokens', [SectionController::class, 'waiting_token']);
    Route::get('/modifier-group-list', [ModifierController::class, 'getList']);
    Route::get('/dashboard/{filter}', [OrderController::class, 'dashboard_data']);
    Route::get('/sectionstable/{id}', [TableController::class, 'index_by_section']);
    Route::get('/export-excel/{filter}', [OrderController::class, 'exportToExcel']);


    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::post('/upload-image', [ItemController::class, 'image']);
    Route::post('/update-profile', [UserController::class, 'update_user']);
    Route::post('/order/{id}', [OrderController::class, 'complete_order']);
    Route::post('/complete-kots', [KOTController::class, 'complete_kots']);
    Route::post('/progress-kots', [KOTController::class, 'progress_kots']);
    Route::post('/update-role/{id}', [RoleController::class, 'update_role']);
    Route::post('/update-password', [UserController::class, 'update_password']);
    Route::post('/customer/search', [CustomerController::class, 'search_customer']);
    Route::post('/customer-feedback', [OrderController::class, 'customerFeedback']);
    Route::post('/customer/assign-table', [CustomerController::class, 'assign_table']);
    Route::post('/customer/waiting-token', [CustomerController::class, 'create_waiting_token']);
    Route::post('/customer/update-waiting-token', [CustomerController::class, 'update_waiting_token']);


    Route::put('/order', [OrderController::class, 'cancel_order']);
    Route::put('/tax-fees-toggle/{id}', [TaxFeeController::class, 'toggle']);


    Route::resource('/kots', KOTController::class);
    Route::resource('/item', ItemController::class);
    Route::resource('/user', UserController::class);
    Route::resource('/order', OrderController::class);
    Route::resource('/table', TableController::class);
    // Route::resource('/roles', [RoleController::class]); // commented
    Route::resource('/section', SectionController::class);
    Route::resource('/tax-fees', TaxFeeController::class);
    Route::resource('/modifier', ModifierController::class);
    Route::resource('/customers', CustomerController::class);
    Route::resource('/category', ItemCategoryController::class);
    Route::resource('/modifier-group', ModifierGroupController::class);



    // Route::get('/me', [AuthController::class, 'me']);
    // Route::get('/categorylist', [ItemCategoryController::class, 'getList']);
    // // Route::get('/modifier-mapper', [ModifierController::class, 'getMapper']);
    // Route::get('/modifier-group-list', [ModifierController::class, 'getList']);
    // Route::post('/logout', [AuthController::class, 'logout']);
    // Route::post('/refresh', [AuthController::class, 'refresh']);
    // Route::resource('/item', ItemController::class);
    // Route::resource('/user', UserController::class);
    // Route::resource('/category', ItemCategoryController::class);
    // Route::resource('/modifier-group', ModifierGroupController::class);
    // Route::resource('/modifier', ModifierController::class);
    // Route::resource('/section', SectionController::class);
    // Route::resource('/table', TableController::class);
    // Route::resource('/tax-fees', TaxFeeController::class);
    // Route::resource('/order', OrderController::class);
    // Route::resource('/customers', CustomerController::class);
    // Route::get('/sectionstable/{id}', [TableController::class, 'index_by_section']);
    // Route::get('/waiting-tokens', [SectionController::class, 'waiting_token']);
    // Route::put('/tax-fees-toggle/{id}', [TaxFeeController::class, 'toggle']);
    // Route::post('/upload-image', [ItemController::class, 'image']);
    // Route::delete('/upload-image/{image}', [ItemController::class, 'removeImage']);
    // Route::post('/customer/assign-table', [CustomerController::class, 'assign_table']);
    // Route::post('/customer/waiting-token', [CustomerController::class, 'create_waiting_token']);
    // Route::post('/order/{id}', [OrderController::class, 'complete_order']);
    // Route::put('/order', [OrderController::class, 'cancel_order']);
    // Route::post('/customer/search', [CustomerController::class, 'search_customer']);
    // Route::post('/update-password', [UserController::class, 'update_password']);
    // Route::post('/update-profile', [UserController::class, 'update_user']);
    // Route::get('/userdata', [AuthController::class, 'me']);
    // Route::get('/dashboard/{filter}', [OrderController::class, 'dashboard_data']);
    // Route::post('/customer-feedback', [OrderController::class, 'customerFeedback']);
    // Route::resource('/kots', KOTController::class);
    // Route::get('/allkots',[KOTController::class, 'all_kots']);
    // Route::get('/roles', [RoleController::class, 'getRoles']);
    // // Route::resource('/roles', [RoleController::class]);
    // Route::post('/update-role/{id}',[RoleController::class, 'update_role']);
    // Route::post('/customer/update-waiting-token', [CustomerController::class, 'update_waiting_token']);


    // Route::get('/export-excel/{filter}', [OrderController::class, 'exportToExcel']);



    // routes for searching

    Route::get('user/search/{search}', [UserController::class, 'search_user']);
    Route::get('item/search/{search}', [ItemController::class, 'search_item']);
    Route::get('table/search/{search}', [TableController::class, 'search_table']);
    Route::get('tax-fees/search/{search}', [TaxFeeController::class, 'search_tax']);
    Route::get('customer/search/{search}', [CustomerController::class, 'search_customer_by_name']);
    Route::get('modifier/search/{search}', [ModifierController::class, 'search_modifier']);
});
