<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Any company_id that no longer points at a real employer is cleared
        // first, otherwise the constraint below cannot be created. The row
        // itself is kept: company_name is a snapshot, so the archive stays
        // readable without the link.
        DB::statement('
            UPDATE job_archives a
            LEFT JOIN employer_nsrp_registrations e
                ON a.company_id = e.employer_nsrp_registrations_id
            SET a.company_id = NULL
            WHERE a.company_id IS NOT NULL
              AND e.employer_nsrp_registrations_id IS NULL
        ');

        // MariaDB gives a TIMESTAMP column with no default an implicit
        // "ON UPDATE current_timestamp()", which would silently rewrite the
        // archive date on any later edit to the row.
        DB::statement('ALTER TABLE job_archives MODIFY archived_at TIMESTAMP NULL DEFAULT NULL');

        Schema::table('job_archives', function (Blueprint $table) {
            // nullOnDelete, not cascade: deleting an employer must not erase
            // the office's placement history. The link drops, the record and
            // its company_name snapshot stay.
            $table->foreign('company_id')
                ->references('employer_nsrp_registrations_id')
                ->on('employer_nsrp_registrations')
                ->nullOnDelete();

            // Both list screens order by archived_at.
            $table->index('archived_at');
        });
    }

    public function down(): void
    {
        Schema::table('job_archives', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropIndex(['archived_at']);
        });
    }
};
