<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Employee\TaskController;
use App\Http\Controllers\UserAccount\LoginController;
use App\Http\Controllers\UserAccount\RegistrationController;
use App\Models\Employee\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



// Route::post('/login', [LoginController::class, 'login']);

Route::middleware(['auth:api', 'role:admin'])->group(function () {
    Route::get('/admin', function() {
        return response()->json([
            'status' => 200,
            'message' => 'Welcome to the Admin Dashboard'
        ]);
    });
    Route::post('/activate/{id}', [RegistrationController::class, 'updateAccStatus']);
    Route::get('/getPending', [RegistrationController::class, 'getPending']);
});

Route::middleware(['auth:api', 'role:employee'])->group(function () {
    Route::get('/employee', function() {
        return response()->json([
            'status' => 200,
            'message' => 'Welcome to the Employee Dashboard'
        ]);
    });
});

Route::middleware(['auth:api', 'role:chief'])->group(function () {
    Route::get('/chief', function() {
        return response()->json([
            'status' => 200,
            'message' => 'Welcome to the Chief Dashboard'
        ]);
    });
});
// Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth:api');

// Route::group(['middleware' => 'auth:api'], function () {
   
// });

// Route::get('/login', function () {
//     if (Auth::check()) {
//         // Redirect based on user role
//         $user = Auth::user();
//         return redirect($user->user_type); // This should redirect to the appropriate dashboard
//     }
//     return view('auth.login'); // Render the login view if not authenticated
// });

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

//Auth 
Route::post('register', [RegistrationController::class, 'registration']);
Route::get('registration', [AuthController::class, 'showRegistrationForm']);
Route::get('login', [AuthController::class, 'showLoginForm']);
Route::post('login', [AuthController::class, 'login']);
Route::middleware('auth:api')->group(function () {
    Route::get('me', [AuthController::class, 'me']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
});

// Route::middleware(['auth:api', 'userType:admin'])->group(function () {
//     Route::get('/admin', [AdminController::class, 'dashboard']);
// });

// Route::middleware(['auth:api', 'userType:chief'])->group(function () {
//     Route::get('/chief', [ChiefController::class, 'dashboard']);
// });

Route::middleware(['auth:api', 'userType:employee'])->group(function () {
    // Route::get('/employee', [EmployeeController::class, 'dashboard']);
    Route::post('addTask', [TaskController::class, 'store']);
    Route::post('updateTask/{id}', [TaskController::class, 'edit']);
    Route::get('tasks', [TaskController::class, 'read']);
});