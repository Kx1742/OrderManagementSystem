<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/customer', [OrderController::class, 'showCustomerView']);
Route::post('/customer/order', [OrderController::class, 'addNormalOrder']);

Route::get('/vip', [OrderController::class, 'showVipView']);
Route::post('/vip/order', [OrderController::class, 'addVipOrder']);

Route::get('/manager', [OrderController::class, 'showManagerView']);
Route::post('/manager/bot', [OrderController::class, 'addBot']);
Route::delete('/manager/bot', [OrderController::class, 'removeBot']);