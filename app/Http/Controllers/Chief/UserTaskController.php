<?php

namespace App\Http\Controllers\Chief;

use App\Http\Controllers\Controller;
use App\Services\Chief\UserTaskService;
use Illuminate\Http\Request;

class UserTaskController extends Controller
{
    protected $userTaskService;

    public function __construct(UserTaskService $userTaskService)
    {
        $this->userTaskService = $userTaskService;
    }

    public function fetch()
    {
        $tasks = $this->userTaskService->get();
        return response($tasks, $tasks['status']);
    }

    public function fetchHours()
    {
        $tasks = $this->userTaskService->getHours();
        return response($tasks, $tasks['status']);
    }

    public function getCounts()
    {
        $tasks = $this->userTaskService->getTaskCounts();
        return response($tasks, $tasks['status']);
    }
}
