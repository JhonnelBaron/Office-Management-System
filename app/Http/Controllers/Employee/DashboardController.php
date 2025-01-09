<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Services\Employee\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function getUserTasks()
    {
        $tasks = $this->dashboardService->get();
        return response($tasks, $tasks['status']);
    }

    public function getUserAttendance()
    {
        $attendance = $this->dashboardService->getAttendance();
        return response($attendance, $attendance['status']);
    }
}
