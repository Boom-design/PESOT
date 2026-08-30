<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Give the rows that already exist a permit year to be judged by.
     *
     * Before this, the expiry date was typed by hand, so the closest thing to a
     * permit year on file is the year that date falls in. It is a guess, and it
     * is deliberately a conservative one: the row keeps working exactly as it
     * did, and the office corrects it the next time the employer uploads a
     * permit. Nothing is overwritten — only the new empty column is filled.
     */
    public function up(): void
    {
        DB::table('employer_requirements')
            ->whereNull('business_permit_year')
            ->whereNotNull('business_permit_expires_at')
            ->update([
                'business_permit_year' => DB::raw('YEAR(business_permit_expires_at)'),
            ]);
    }

    public function down(): void
    {
        // Nothing to undo: the column itself is dropped by the migration that
        // added it, and there is no earlier value here to put back.
    }
};
