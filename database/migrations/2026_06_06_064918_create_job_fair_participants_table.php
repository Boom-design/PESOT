<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_fair_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_fair_id')->constrained('job_fair_events')->onDelete('cascade');
            $table->foreignId('employer_id')->constrained('users')->onDelete('cascade');
            $table->enum('confirmation_status', ['pending', 'confirmed', 'declined'])->default('pending');
            $table->integer('total_interviewed')->default(0);
            $table->integer('total_vacancies')->default(0);
            $table->integer('male_count')->default(0);
            $table->integer('female_count')->default(0);
            $table->integer('total_hired')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_fair_participants');
    }
};