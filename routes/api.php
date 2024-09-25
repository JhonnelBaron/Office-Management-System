<?php

use App\Http\Controllers\UserAccount\LoginController;
use App\Http\Controllers\UserAccount\RegistrationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/register', [RegistrationController::class, 'registration']);
Route::post('/login', [LoginController::class, 'login']);


Route::get('/getPending', [RegistrationController::class, 'getPending']);
Route::post('/activate/{id}', [RegistrationController::class, 'updateAccStatus']);

