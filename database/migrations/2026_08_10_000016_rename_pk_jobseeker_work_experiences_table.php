<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Renames the jobseeker_work_experiences table primary key from the generic
 * "id" to the descriptive "jobseeker_work_experiences_id". No inbound foreign
 * keys.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jobseeker_work_experiences', function (Blueprint $table) {
            $table->renameColumn('id', 'jobseeker_work_experiences_id');
        });
    }

    public function down(): void
    {
        Schema::table('jobseeker_work_experiences', function (Blueprint $table) {
            $table->renameColumn('jobseeker_work_experiences_id', 'id');
        });
    }
};
