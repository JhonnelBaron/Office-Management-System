<?php

namespace App\Services\Employee;

use App\Models\Employee\Task;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Support\Carbon;
use Tymon\JWTAuth\Facades\JWTAuth;

class TaskService
{
    public function add(array $payload)
    {
        $user = JWTAuth::parseToken()->authenticate(); 
        $payload['user_id'] = $user->id;
        $payload['status'] = 'In Progress';
        $payload['date_added'] = now();

        $task = Task::create($payload);
        return [
            'data' => $task,
            'status' => 201,
            'message' => 'New tasks added successfully!'
        ];
        
    }

    public function get()
    {
        $tasks = Task::orderBy('created_at', 'desc')->get();
        return [
            'tasks' => $tasks,
            'message' => 'Tasks retrieved successfully',
            'status' => 200,
        ];
    }

    public function update(int $id, $payload)
    {
        $task = Task::find($id);
        if (!$task) {
            return $this->errorResponse('Task not existed!');
        }

        if (isset($payload['status']) && $payload['status'] === 'Done') {
            // Set current datetime for date_finished
            $payload['date_finished'] = Carbon::now();

            // Calculate hours worked (assuming date_added exists)
            if ($task->date_added) {
                $dateAdded = Carbon::parse($task->date_added);
                $payload['hours_worked'] = $dateAdded->diffInHours(Carbon::now());
            } else {
                $payload['hours_worked'] = 0; // In case date_added is missing or null
            }
        }

        $task->update($payload);
        return [
            'data' => $task,
            'message' => 'Task updated successfully!',
            'status' => 200,
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