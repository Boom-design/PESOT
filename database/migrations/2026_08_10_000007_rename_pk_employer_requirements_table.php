<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Renames the employer_requirements table primary key from the generic "id" to
 * the descriptive "employer_requirements_id".
 *
 * No other table holds a foreign key to employer_requirements, so the column
 * can be renamed directly.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employer_requirements', function (Blueprint $table) {
            $table->renameColumn('id', 'employer_requirements_id');
        });
    }

    public function down(): void
    {
        Schema::table('employer_requirements', function (Blueprint $table) {
            $table->renameColumn('employer_requirements_id', 'id');
        });
    }
};
