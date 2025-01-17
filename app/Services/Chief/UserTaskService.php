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

    public function getHours()
    {
        $data = Task::whereHas('user', function($query) {
                $query->where('user_type', 'employee');
            })
            ->where('status', 'Done')
            ->select('id', 'user_id', 'status', 'date_added', 'date_finished', 'hours_worked')
            ->orderByRaw('CAST(hours_worked AS DECIMAL(10,2)) ASC') 
            ->get();
    
        // Transform hours_worked to hours and minutes
        $transformedData = $data->map(function($task) {
            $totalMinutes = $task->hours_worked * 60;
            $hours = floor($totalMinutes / 60);
            $minutes = round($totalMinutes % 60);
            $task->hours_worked_formatted = "{$hours}h {$minutes}m";
            return $task;
        });
    
        return [
            'data' => $transformedData,
            'message' => 'Hours worked records retrieved successfully',
            'status' => 200,
        ];
    }

    public function getTaskCounts()
    {
        return [
            'message'=> 'Tasks Count Retrieved Successfully',
            'status' => 200,
            'tasksCreatedToday' => $this->countTasksCreatedToday(),
            'doneTasks' => $this->countDoneTasks(),
            'suspendedTasks' => $this->countSuspendedTasks(),
            'inProgressTasks' => $this->countInProgressTasks(),
        ];
    }

    private function countTasksCreatedToday()
    {
        $tasks = Task::with(['user:id,first_name,last_name'])
                    ->whereDate('created_at', today())->get();

        $tasks->each(function ($task) {
            $task->links = $task->documentLinks->pluck('document_link');
        });

        return[
            'created_count' => $tasks->count(),
            'created_tasks' => $tasks,
        ];
        
    }

    private function countDoneTasks()
    {
        $tasks = Task::with(['user:id,first_name,last_name'])
                    ->where('status', 'Done')
                    ->whereDate('created_at', today())->get();
        $tasks->each(function ($task) {
            $task->links = $task->documentLinks->pluck('document_link'); 
        });
            
        return[
            'done_count' => $tasks->count(),
            'done_tasks' => $tasks,
        ];
                    
    }

    private function countSuspendedTasks()
    {
        $tasks = Task::with(['user:id,first_name,last_name'])
                    ->where('status', 'Suspended')
                    ->whereDate('created_at', today())->get();
        $tasks->each(function ($task) {
            $task->links = $task->documentLinks->pluck('document_link');
        });
            
        return[
            'suspended_count' => $tasks->count(),
            'suspended_tasks' => $tasks,
        ];
                    
    }

    private function countInProgressTasks()
    {
        $tasks = Task::with(['user:id,first_name,last_name'])
                    ->where('status', 'In Progress')
                    ->whereDate('created_at', today())->get();
        $tasks->each(function ($task) {
            $task->links = $task->documentLinks->pluck('document_link');
        });
            
        return[
            'inprogress_count' => $tasks->count(),
            'inprogress_tasks' => $tasks,
        ];
                    
    }
    
}