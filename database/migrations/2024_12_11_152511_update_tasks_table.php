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
        Schema::table('tasks', function (Blueprint $table) {
            // Update the enum column to include new statuses
            DB::statement("ALTER TABLE tasks MODIFY COLUMN status ENUM(
                'In Progress',
                'Done',
                'Suspended',
                'Cancelled'
            ) DEFAULT 'In Progress'");

            // Add new columns
            $table->string('paps')->nullable();
            $table->string('type')->nullable();
            $table->string('task')->nullable();
            $table->integer('no_of_document')->nullable();
            $table->dateTime('time_suspended')->nullable();
            $table->dateTime('time_continued')->nullable();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Revert changes to the status column
            DB::statement("ALTER TABLE tasks MODIFY COLUMN status ENUM(
                'In Progress',
                'Done',
                'Suspended',
                'Cancelled'
            ) DEFAULT 'In Progress'");

            // Drop the added columns
            $table->dropColumn(['paps', 'type', 'task', 'no_of_document', 'time_suspended', 'time_continued']);
        });
    }
};
