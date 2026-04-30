<?php

use App\Events\RouteslipUpdatedEvent;
use App\Http\Controllers\Api\RouteSlipIntegrationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Chief\AttendanceController;
use App\Http\Controllers\Chief\UserTaskController;
use App\Http\Controllers\Employee\DashboardController;
use App\Http\Controllers\Employee\TaskController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PusherController;
use App\Http\Controllers\UserAccount\LoginController;
use App\Http\Controllers\UserAccount\RegistrationController;
use App\Models\Api\Routeslip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::group(['middleware' => 'auth:api'], function () {
    Broadcast::routes();
});
Route::post('/receive-routeslip-update', [RouteSlipIntegrationController::class, 'handleUpdate']);
Route::middleware(['auth:api'])->group(function () {
    Route::get('/fetch-routeslips', [RouteSlipIntegrationController::class, 'fetchRouteslips']);
    Route::get('/notifications', [RouteSlipIntegrationController::class, 'getNotificationCount']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
});
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
Route::post('login-photo', [FileController::class, 'store']);
Route::post('timeout', [AuthController::class, 'timeout']);
Route::middleware('auth:api')->group(function () {
    Route::get('me', [AuthController::class, 'me']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    route::post('/pusher/auth', [PusherController::class, 'auth']);
});

Route::middleware(['auth:api', 'userType:employee'])->group(function () {
    // Route::get('/employee', [EmployeeController::class, 'dashboard']);
    Route::get('user', [TaskController::class, 'getUser']);
    Route::post('addTask', [TaskController::class, 'store']);
    Route::post('updateTask/{id}', [TaskController::class, 'edit']);
    Route::get('task/{id}', [TaskController::class, 'task']);
    Route::get('tasks', [TaskController::class, 'read']);
    Route::get('dashboard/tasks', [DashboardController::class, 'getUserTasks']);
    Route::get('dashboard/attendance', [DashboardController::class, 'getUserAttendance']);
});

Route::middleware(['auth:api', 'userType:chief'])->group(function (){
    Route::get('/userTasks', [UserTaskController::class, 'fetch']);
    Route::get('/attendance', [AttendanceController::class, 'fetch']);
    Route::get('/employees', [AttendanceController::class, 'fetchEmployees']);
    Route::get('/hours', [UserTaskController::class, 'fetchHours']);
    Route::get('/tasks-count', [UserTaskController::class, 'getCounts']);
});


Route::post('/facebook/webhook', function (Request $request) {
    $data = $request->all();

    // Log the data to check what Facebook is sending
    Log::info('Messenger Webhook:', $data);

    return response()->json(['status' => 'success']);
});

Route::get('/facebook/webhook', function (Request $request) {
    $verifyToken = env('FB_WEBHOOK_VERIFY_TOKEN');

    if ($request->query('hub_mode') === 'subscribe' && 
        $request->query('hub_verify_token') === $verifyToken) {
        return response($request->query('hub_challenge'), 200);
    }

    return response('Verification failed', 403);
});


// Route::get('/test-broadcast/{userId}', function ($userId) {
//     // Kumuha ng kahit anong sample data mula sa DB
//     $routeslip = Routeslip::first(); 

//     if (!$routeslip) {
//         return "Walang routeslip data sa database.";
//     }

//     // I-trigger ang event
//     // Note: Gamitin ang 'broadcast()' helper para dumaan sa Queue
//     broadcast(new RouteslipUpdatedEvent($routeslip, $userId));

//     return "Broadcast triggered para sa User ID: " . $userId;
// });