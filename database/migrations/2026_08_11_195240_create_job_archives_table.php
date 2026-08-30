<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_archives', function (Blueprint $table) {
            $table->id('job_archives_id');
            $table->unsignedBigInteger('company_id')->nullable();
            $table->string('company_name')->nullable();
            $table->string('job_title');
            $table->string('schedule_type')->nullable();
            $table->unsignedInteger('slots')->default(0);
            $table->unsignedInteger('hired_count')->default(0);
            $table->unsignedInteger('applicants_count')->default(0);
            $table->date('posted_month');
            $table->timestamp('archived_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_archives');
    }
};
