<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\TodoController;


Route::controller(AuthController::class)->group(function () {
    Route::post('login', 'login');
    Route::post('register', 'register');
    Route::get('logout', 'logout');

});

Route::controller(PaymentController::class)->prefix('payments')->group(function () {
    Route::post('/','store');
    Route::get('/','index');
    Route::get('/{id}','show');
}); 
