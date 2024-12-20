<?php

namespace App\Services\Chief;

use App\Models\Login;
use App\Models\User;

class AttendanceService
{
    public function getEmployees()
    {
        $employees = User::where('user_type', 'employee')
                    ->orderBy('first_name', 'asc')
                    ->get(['id', 'first_name', 'last_name']);
        return [
            'employees' => $employees,
            'message' => 'Employees retrieved successfully',
            'status' => 200
        ];
    }

    public function get()
    {
        $attendance = Login::whereHas('user', function($query) {
            $query->where('user_type', 'employee');
        })
        // ->with(['user' => function($query){
        //     $query->select('id', 'first_name', 'last_name');
        // }])
        ->orderBy('time_in', 'asc')
        ->get();

        return [
            'attendance' => $attendance,
            'message' => 'Attendance records retrieved successfully',
            'status' => 200,
        ];
    }
}