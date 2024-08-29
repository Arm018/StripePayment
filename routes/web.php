<?php

use App\Http\Controllers\PaymentController;
use App\Http\Controllers\StripeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('checkout', [StripeController::class, 'checkout'])->name('checkout');
Route::post('pay-existing-order', [StripeController::class, 'payExistingOrder'])->name('pay.existing.order');
Route::post('session', [StripeController::class, 'session'])->name('session');
Route::get('success', [StripeController::class, 'success'])->name('success');
Route::post('webhook', [StripeController::class, 'webhook'])->name('webhook');
Route::get('pending', [StripeController::class, 'pending'])->name('orders.pending');



