<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Renames the job_matching table primary key from the generic "id" to the
 * descriptive "job_matching_id".
 *
 * No other table holds a foreign key to job_matching, so the column can be
 * renamed directly.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_matching', function (Blueprint $table) {
            $table->renameColumn('id', 'job_matching_id');
        });
    }

    public function down(): void
    {
        Schema::table('job_matching', function (Blueprint $table) {
            $table->renameColumn('job_matching_id', 'id');
        });
    }
};
