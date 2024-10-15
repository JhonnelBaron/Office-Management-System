<?php

namespace App\Http\Controllers\Chief;

use App\Http\Controllers\Controller;
use App\Services\Chief\AttendanceService;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    protected $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    public function fetch()
    {
        $attendance = $this->attendanceService->get();
        return response($attendance, $attendance['status']);
    }
}
