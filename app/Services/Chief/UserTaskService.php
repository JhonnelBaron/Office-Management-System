<?php

namespace App\Services\Chief;

use App\Models\Employee\Task;

class UserTaskService
{
    public function get()
    {
        $tasks = Task::orderBy('created_at', 'desc')
        ->with(['user' => function($query) {
            $query->select('id', 'first_name', 'last_name');
        }])
        ->get();
        return [
            'tasks' => $tasks,
            'message' => 'Tasks retrieved successfully',
            'status' => 200,
        ];
    }
}