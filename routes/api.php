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

Route::middleware(['auth:api', 'role:admin'])->group(function () {
    Route::post('/activate/{id}', [RegistrationController::class, 'updateAccStatus']);
    Route::get('/getPending', [RegistrationController::class, 'getPending']);
});

Route::middleware(['auth:api', 'role:employee'])->group(function () {

});
Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth:api');

Route::group(['middleware' => 'auth:api'], function () {
   
});

// Route::middleware(['auth', 'role:admin'])->group(function () {
//     Route::get('/admin', [AdminController::class, 'dashboard']);
//     // Other admin routes
// });

// Route::middleware(['auth', 'role:chief'])->group(function () {
//     Route::get('/chief', [ChiefController::class, 'dashboard']);
//     // Other chief routes
// });

// Route::middleware(['auth', 'role:employee'])->group(function () {
//     Route::get('/employee', [EmployeeController::class, 'dashboard']);
//     // Other employee routes
// });
