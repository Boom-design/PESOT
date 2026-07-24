<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('jobs', 'job_qualifications');
        Schema::rename('applications', 'job_matching');
    }

    public function down(): void
    {
        Schema::rename('job_qualifications', 'jobs');
        Schema::rename('job_matching', 'applications');
    }
};