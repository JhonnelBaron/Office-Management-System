<?php

namespace App\Services\Employee;

use App\Models\Employee\Task;
use App\Models\Login;
use Carbon\Carbon;
use Tymon\JWTAuth\Facades\JWTAuth;

class DashboardService
{
    private function errorResponse($message): array
    {
        return [
            'status' => '400',
            'message' => $message,
        ];
    }

    public function get()
    {    
        $user = JWTAuth::parseToken()->authenticate();

        return [
            'user_id' => $user->id,
            'user' => $user->first_name.' '.$user->last_name,
            'tasks_today' => $this->countTasksCreatedToday($user),
            'tasks_this_cutoff' => $this->countTasksThisCutoff($user),
            'tasks_done' => $this->countTasksWithStatus('Done', $user),
            'tasks_suspended' => $this->countTasksWithStatus('Suspended', $user),
            'tasks_in_progress' => $this->countTasksWithStatus('In Progress', $user),
            'message' => 'Tasks retrieved successfully',
            'status' => 200,
        ];
    }

    // Count tasks created today
    private function countTasksCreatedToday($user)
    {
        return Task::where('user_id', $user->id)
                    ->whereDate('created_at', today())
                    ->count();
    }

    // Count tasks created in the current cutoff
    private function countTasksThisCutoff($user)
    {
        $date = now();
        $startOfCutoff = null;
        $endOfCutoff = null;

        // Determine the cutoff period based on the current date
        if ($date->day >= 1 && $date->day <= 10) {
            $startOfCutoff = now()->subMonth()->day(26);  // Previous month cutoff start
            $endOfCutoff = now()->day(10);  // Current month cutoff end
        } elseif ($date->day >= 11 && $date->day <= 25) {
            $startOfCutoff = now()->day(11);  // Current month cutoff start
            $endOfCutoff = now()->day(25);  // Current month cutoff end
        } else {
            $startOfCutoff = now()->day(26);  // Current month cutoff start
            $endOfCutoff = now()->addMonth()->day(10);  // Next month cutoff end
        }

        return Task::where('user_id', $user->id)
                    ->whereBetween('created_at', [$startOfCutoff, $endOfCutoff])
                    ->count();
    }

    // Count tasks with a specific status
    private function countTasksWithStatus($status, $user)
    {
        return Task::where('user_id', $user->id)
                    ->where('status', $status)
                    ->count();
    }

    public function getAttendance()
    {
        $user = JWTAuth::parseToken()->authenticate();
    
        $logInfo = Login::where('user_id', $user->id)
            ->orderBy('date', 'desc')
            ->select('time_in', 'date') // Only select 'time_in' and 'date' from the database
            ->get();
    
        return [
            'LogInfo' => $logInfo->map(function ($log) {
                return [
                    'time_in' => $log->time_in, // Accessor handles formatting
                    'date' => $log->date,       // Accessor handles formatting
                    'day' => $log->day_of_week, // New computed attribute
                ];
            }),
            'message' => 'Your Log Information has been retrieved successfully',
            'status' => 200,
        ];
    }
    
    
}