<?php

namespace App\Http\Controllers\UserAccount;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\RegistrationRequest;
use App\Services\Account\RegistrationService;
use Illuminate\Http\Request;

class RegistrationController extends Controller
{
    protected $registrationService;

    public function __construct(RegistrationService $registrationService)
    {
        $this->registrationService = $registrationService;
    }

    public function registration(RegistrationRequest $request)
    {
        $registration = $this->registrationService->register($request->validated());

        return response($registration, $registration['status']);
    }

    public function getPending(Request $request)
    {
        $pending = $this->registrationService->getRegister($request->query());

        return response($pending, $pending['status']);
    }

    public function updateAccStatus($id)
    {
        $acc = $this->registrationService->activateAcc($id);

        return response($acc, $acc['status']);
    }
}
