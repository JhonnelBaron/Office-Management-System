<?php

namespace App\Http\Controllers\UserAccount;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\LoginRequest;
use App\Services\Account\LoginService;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    protected $loginService;

    public function __construct(LoginService $loginService)
    {
        $this->loginService = $loginService;
    }

    public function login(Request $request)
    {
        $login = $this->loginService->login($request->all());

        return response($login, $login['status']);
    }

    public function logout()
    {
        $logout = $this->loginService->logout();
        return $logout; 
    }
}
