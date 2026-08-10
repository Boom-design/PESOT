<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Renames the jobseeker_certifications table primary key from the generic "id"
 * to the descriptive "jobseeker_certifications_id". No inbound foreign keys.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jobseeker_certifications', function (Blueprint $table) {
            $table->renameColumn('id', 'jobseeker_certifications_id');
        });
    }

    public function down(): void
    {
        Schema::table('jobseeker_certifications', function (Blueprint $table) {
            $table->renameColumn('jobseeker_certifications_id', 'id');
        });
    }
};
