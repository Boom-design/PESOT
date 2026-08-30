<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * A read marker that belongs to the admin alone.
 *
 * `announcements.is_read` belongs to the person the row was addressed to — the
 * jobseeker, the employer, or the staff member on that desk. The admin reads
 * over their shoulder: the bell and the new sidebar counters look at every
 * notice in the office, not at rows written for the admin (there are none).
 *
 * Marking those rows `is_read` would clear the red dot in somebody else's bell
 * for a notice they never opened, so the admin gets a separate column instead.
 * Nothing the admin does here can change what another account still has to see.
 *
 * Existing rows are backfilled as already seen. The office has been running for
 * weeks; starting the counters at a hundred and thirty-nine would say nothing
 * except that the column is new.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->timestamp('admin_seen_at')->nullable()->after('is_read');
        });

        DB::table('announcements')->update(['admin_seen_at' => now()]);
    }

    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropColumn('admin_seen_at');
        });
    }
};
