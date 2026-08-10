<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Renames the job_fair_employment_requests table primary key from the generic
 * "id" to the descriptive "job_fair_employment_requests_id". No inbound
 * foreign keys.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_fair_employment_requests', function (Blueprint $table) {
            $table->renameColumn('id', 'job_fair_employment_requests_id');
        });
    }

    public function down(): void
    {
        Schema::table('job_fair_employment_requests', function (Blueprint $table) {
            $table->renameColumn('job_fair_employment_requests_id', 'id');
        });
    }
};
