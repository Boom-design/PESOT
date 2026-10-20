<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The contact centres leave Real Estate Activities for Section O.
 *
 * The group they used to sit in was "Real Estate, Renting and Business
 * Activities", and Revision 5 split it: real estate kept Section M, and the
 * renting and business activities moved into Sections N and O. The migration
 * before this one could only rename, so every establishment in the old group
 * landed in Real Estate Activities — right for a realty firm, wrong for a
 * contact centre, and the three establishments in that group are all contact
 * centres.
 *
 * Matched on the name, not on the whole group: a realty firm registering
 * later must not be swept along with them. A contact centre is Division 82 of
 * Section O, Business support service activities.
 */
return new class extends Migration
{
    private const FROM = 'Real Estate Activities';
    private const TO   = 'Administrative and Support Service Activities';

    /** The suffixes the demo population gives a BPO. */
    private const NAMES = [
        '%Business Solutions Inc.',
        '%Outsourcing Services Inc.',
        '%Digital Services Corp.',
        '%Contact Center Inc.',
    ];

    public function up(): void
    {
        $this->move(self::FROM, self::TO);
    }

    public function down(): void
    {
        $this->move(self::TO, self::FROM);
    }

    private function move(string $from, string $to): void
    {
        $employers = DB::table('employer_nsrp_registrations')
            ->where('industry_group', $from)
            ->where(function ($q) {
                foreach (self::NAMES as $name) {
                    $q->orWhere('company_name', 'like', $name);
                }
            })
            ->pluck('employer_nsrp_registrations_id');

        if ($employers->isEmpty()) {
            return;
        }

        DB::table('employer_nsrp_registrations')
            ->whereIn('employer_nsrp_registrations_id', $employers)
            ->update(['industry_group' => $to]);

        // The vacancy carries its own copy of the answer, and it is the copy
        // a fair reads when it decides which postings it takes.
        DB::table('job_qualifications')
            ->whereIn('company_id', $employers)
            ->where('industry_group', $from)
            ->update(['industry_group' => $to]);
    }
};
