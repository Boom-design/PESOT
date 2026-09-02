<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Where the venue actually is.
 *
 * PESO Job Fair staff, 2026-09-02: `venue` holds the name of the place — "SM
 * City CDO Event Center" — which is enough for the office but not for the
 * jobseeker being told to go there. The street address is a second line, so it
 * gets a second column rather than being crammed into the name.
 *
 * Nullable: every event created before today has a name and no address, and
 * the office is not going back to fill them in.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_fair_events', function (Blueprint $table) {
            $table->string('venue_address')->nullable()->after('venue');
        });
    }

    public function down(): void
    {
        Schema::table('job_fair_events', function (Blueprint $table) {
            $table->dropColumn('venue_address');
        });
    }
};
