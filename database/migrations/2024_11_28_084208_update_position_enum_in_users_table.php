<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            DB::statement("ALTER TABLE users MODIFY COLUMN position ENUM(
                'Project Support Staff I',
                'Project Support Staff II',
                'Project Support Staff III',
                'Admin Support Staff',
                'Chief TESD Specialist',
                'Supervising TESD Specialist',
                'System Admin',
                'TESD Specialist I',
                'TESD Specialist II',
                'Senior TESD Specialist'
            ) NULL");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            DB::statement("ALTER TABLE users MODIFY COLUMN position ENUM(
                'Project Support Staff I',
                'Project Support Staff II',
                'Project Support Staff III',
                'Admin Support Staff',
                'Chief TESD Specialist',
                'Supervising TESD Specialist',
                'System Admin',
                'TESD Specialist I',
                'TESD Specialist II',
                'Senior TESD Specialist'
            ) NULL");
        });
    }
};
