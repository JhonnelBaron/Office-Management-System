<?php

namespace App\Services\Account;

use App\Mail\AccountActivatedMail;
use App\Models\User;
use App\Utilities\Utils;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class RegistrationService
{
    public function register(array $payload)
    {
        $payload['user_type'] = $payload['user_type'] ?? 'employee';
        $payload['status'] = $payload['status'] ?? 'pending';

        $payload['password'] = Hash::make($payload['password']);
        
        $register = User::create($payload);

        return [
            'data' => $register,
            'status' => 201,
            'message' => 'Account created successfully!'
        ];
    }

    public function getRegister(array $request)
    {
        $paginate = empty($request['paginate']) ? 15 : Utils::setPaginate($request['paginate']);
        $pendingAcc = User::where('status', 'pending')
        ->orderBy('created_at', 'desc')
                    ->when(!empty($request['search']), function ($query) use ($request){
                        $searchTerm = '%' . $request['search'] . '%';
                        $query->where('last_name', 'LIKE', $searchTerm)
                        ->orWhere('first_name', 'LIKE', $searchTerm)
                        ->orWhere('middle_name', 'LIKE', $searchTerm)
                        ->orWhere('extension_name', 'LIKE', $searchTerm)
                        ->orWhere('contact_no', 'LIKE', $searchTerm)
                        ->orWhere('email', 'LIKE', $searchTerm);
              })
              ->paginate($paginate);

        return[
            'registered' => $pendingAcc,
            'message' => 'newly registered accounts',
            'status' => 200,
        ];
    }

    public function activateAcc(int $id)
    {
        $acc = User::find($id);
        if (!$acc){
            return $this->errorResponse('Account not found!');
        }
        $acc->status = 'active';
        $acc->save();

        Mail::to($acc->email)->send(new AccountActivatedMail($acc));

        return [
            'message' => 'Account activated successfully.',
            'status' => 200
        ];
    }
  
    public function removeAcc(int $id)
    {
        $acc = User::find($id);
        if (!$acc){
            return $this->errorResponse('Account does not exist!');
        }

        $acc->delete();
        return [
            'message' => 'Account removed successfully!',
            'status' => 200
        ];

    }

    private function errorResponse($message): array
    {
        return [
            'status' => '400',
            'message' => $message,
        ];
    }
}