<?php

namespace App\Services;

use App\Events\RouteslipUpdatedEvent;
use App\Models\Api\Routeslip;
use App\Models\User;
use App\Notifications\RouteslipUpdated;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class RouteslipService
{
    public function syncRouteslipData(array $data)
    {
        // 1. User Matching Logic (Focal Person)
        $rawFocal = $data['focal'] ?? '';
        $focalName = trim(explode(',', $rawFocal)[0]);
        
        $user = User::where(function($query) use ($focalName) {
            $query->where('first_name', 'LIKE', "%{$focalName}%")
                  ->orWhere('last_name', 'LIKE', "%{$focalName}%")
                  ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$focalName}%"]);
        })->first();

        // 2. Data Insertion / Update
        $routeslip = Routeslip::updateOrCreate(
            ['routeslip_no' => $data['routeslip_no']], 
            [
                'r_subject'              => $data['subject'] ?? 'No Subject',
                'assigned_focal_user_id' => $user ? $user->id : null,
                'assigned_focal_name'    => $data['focal'], 
                'r_instructions'         => $data['instruction'] ?? 'N/A',
                'reference'              => $data['reference'] ?? '', 
                'r_remarks'              => $data['remarks'] ?? 'N/A',
                'status'                 => $data['status'] ?? 'Pending',
                'is_read'                => false,
                'r_action_taken'         => $this->mapStatus($data['action_taken'] ?? ''),
                'r_action_taken_date'    => Carbon::now()->toDateString(), 
                'r_action_taken_time'    => Carbon::now()->toTimeString(), 
                'urgency'                => $data['urgency'] ?? 'Normal',
                'r_drafts'               => $data['drafts'] ?? null,
                'r_scanned_copy'         => $data['scanned_copy'] ?? null,
            ]
        );

        // 3. Notification Logic
        if ($user) {
            $user->notify(new RouteslipUpdated($routeslip));
            broadcast(new RouteslipUpdatedEvent($routeslip, $user->id))->toOthers();
            Log::info("Notification sent to User ID: {$user->id} for RS#{$routeslip->routeslip_no}");
        }

        return $routeslip;
    }

    private function mapStatus($nativeStatus) 
    {
        $status = trim($nativeStatus);
        if ($status == 'Completed') return 'Finished'; 
        if ($status == 'Pending') return 'Awaiting Action';
        return $status; 
    }

    public function getRouteslipsByRole($user)
    {
        try {
            $query = Routeslip::query();

            // Admin sees all, others see only their assigned routeslips
            if ($user->userType !== 'admin') { 
                $query->where('assigned_focal_user_id', $user->id);
            }

            $data = $query->orderBy('created_at', 'desc')->paginate(10);

            return [
                'status' => 200,
                'data' => $data
            ];
        } catch (\Exception $e) {
            return [
                'status' => 500,
                'message' => 'Error fetching routeslips: ' . $e->getMessage()
            ];
        }
    }

    public function getPendingCount($userId)
    {
        return Routeslip::where('assigned_focal_user_id', $userId)
                        ->where('is_read', false) 
                        ->count();
    }

    public function errorResponse($message, $code = 400): array
    {
        return [
            'status' => 'error',
            'message' => $message,
            'code' => $code
        ];
    }
}