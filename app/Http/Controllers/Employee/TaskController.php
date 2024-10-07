<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\Employee\TaskRequest;
use App\Http\Requests\Employee\UpdateTaskRequest;
use App\Services\Employee\TaskService;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    protected $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    public function store(TaskRequest $request)
    {
        $task = $this->taskService->add($request->validated());
        return response($task, $task['status']);
    }

    public function edit(UpdateTaskRequest $request, $id)
    {
        $task = $this->taskService->update($id, $request->validated());
        return response($task, $task['status']);
    }

    public function read()
    {
        $task = $this->taskService->get();
        return response($task, $task['status']);
    }

    
}
