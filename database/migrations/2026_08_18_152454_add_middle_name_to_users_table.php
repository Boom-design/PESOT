<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The jobseeker registration form has always asked for a middle name and has
 * always thrown it away — there was nowhere to put it. The person then had to
 * type it a second time on the NSRP form.
 *
 * Nullable on purpose: staff accounts, employer accounts and every account
 * created before this migration have no middle name, and plenty of people
 * genuinely have none.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('middle_name')->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('middle_name');
        });
    }
};
