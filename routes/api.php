<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Chief\AttendanceController;
use App\Http\Controllers\Chief\UserTaskController;
use App\Http\Controllers\Employee\TaskController;
use App\Http\Controllers\PusherController;
use App\Http\Controllers\UserAccount\LoginController;
use App\Http\Controllers\UserAccount\RegistrationController;
use App\Models\Employee\Task;
use App\Services\Chief\AttendanceService;
use App\Services\Chief\UserTaskService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/password/email', [AuthController::class, 'passwordResetLink']);

route::post('/password/reset', [AuthController::class, 'resetPassword']);
Route::get('/password/reset', [AuthController::class, 'showResetForm']);
Route::get('/password/reset/{token}', function ($token) {
    // Redirect to frontend route with the token and email as query parameters
    $frontendUrl = config('app.frontend_url') . '/update-password?token=' . $token . '&email=' . request('email');
    return redirect($frontendUrl);
})->name('password.reset');


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

//Auth 
Route::post('register', [RegistrationController::class, 'registration']);
Route::get('registration', [AuthController::class, 'showRegistrationForm']);
Route::get('login', [AuthController::class, 'showLoginForm']);
Route::post('login', [AuthController::class, 'login']);
Route::middleware('auth:api')->group(function () {
    Route::get('me', [AuthController::class, 'me']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    route::post('/pusher/auth', [PusherController::class, 'auth']);
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
    Route::get('task/{id}', [TaskController::class, 'task']);
    Route::get('tasks', [TaskController::class, 'read']);
});

Route::middleware(['auth:api', 'userType:chief'])->group(function (){
    Route::get('/userTasks', [UserTaskController::class, 'fetch']);
    Route::get('/attendance', [AttendanceController::class, 'fetch']);
    Route::get('/employees', [AttendanceController::class, 'fetchEmployees']);
});
