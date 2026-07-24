<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_fair_employment_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_fair_id')->constrained('job_fair_events')->onDelete('cascade');
            $table->foreignId('employer_id')->constrained('employer_nsrp_registrations')->onDelete('cascade');
            $table->foreignId('job_id')->constrained('jobs')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['job_fair_id', 'job_id']); // dili pwede i-duplicate ang parehas nga job sa parehas nga event
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_fair_employment_requests');
    }
};