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
        Schema::create('routeslips', function (Blueprint $table) {
            $table->id();
                        $table->string('routeslip_no')->unique(); 
            $table->text('r_subject')->nullable();
            $table->integer('assigned_focal_user_id')->nullable();
            $table->string('assigned_focal_name')->nullable();  
            $table->text('r_instructions')->nullable();
            $table->text('reference')->nullable();
            $table->string('status')->nullable();
            $table->boolean('is_read')->default(false);
            $table->text('r_action_taken')->nullable();
            $table->date('r_action_taken_date')->nullable();
            $table->time('r_action_taken_time')->nullable();
            $table->string('urgency')->nullable(); 
            $table->text('r_remarks')->nullable();
            $table->text('r_drafts')->nullable();
            $table->text('r_scanned_copy')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('routeslips');
    }
};
