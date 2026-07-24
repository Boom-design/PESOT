<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inhouse_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inhouse_schedule_id')->constrained('inhouse_schedules')->onDelete('cascade');
            $table->foreignId('jobseeker_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamps();
            $table->unique(['inhouse_schedule_id', 'jobseeker_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inhouse_participants');
    }
};