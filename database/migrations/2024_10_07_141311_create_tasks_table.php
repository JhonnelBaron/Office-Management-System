<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->text('title')->nullable();
            $table->longText('description')->nullable();
            $table->longText('link')->nullable();
            $table->enum('status', ['In Progress', 'Done'])->default('In Progress');
            $table->dateTime('date_added')->nullable();
            $table->dateTime('date_finished')->nullable();
            $table->string('hours_worked')->nullable();
            $table->string('pending_days')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
