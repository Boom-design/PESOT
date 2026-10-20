<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * "Overseas Manpower Services" leaves the industry list.
 *
 * It was never a PSIC group. The office asked for the dropdown to come from
 * the PSA detailed structure, and twenty-two of the twenty-three entries did
 * — this one we had written ourselves, and it was the one line the office
 * could not point at a source for.
 *
 * The PSA does classify the work: sub-class 78102, Overseas employment
 * recruitment and placement activities, under Division 78, Employment
 * Activities, of Section O. So the agencies move to Section O, where a
 * contact centre already sits.
 *
 * Nothing is lost by the move. An agency was never told apart from a local
 * employer by this answer — is_overseas does that, and every query that cares
 * already reads it. The DOLE report's overseas heading reads it too now.
 */
return new class extends Migration
{
    private const FROM = 'Overseas Manpower Services';
    private const TO   = 'Administrative and Support Service Activities';

    public function up(): void
    {
        $this->move(self::FROM, self::TO);
    }

    /**
     * Putting it back cannot be done by the group alone.
     *
     * Section O holds the contact centres as well, and sending all of them
     * back would hand four local employers an answer written for overseas
     * agencies. is_overseas is what separates them, so that is what this
     * reads — the same column the report now reads.
     */
    public function down(): void
    {
        $agencies = DB::table('employer_nsrp_registrations')
            ->where('industry_group', self::TO)
            ->where('is_overseas', true)
            ->pluck('employer_nsrp_registrations_id');

        $this->apply($agencies, self::TO, self::FROM);
    }

    private function move(string $from, string $to): void
    {
        $employers = DB::table('employer_nsrp_registrations')
            ->where('industry_group', $from)
            ->pluck('employer_nsrp_registrations_id');

        $this->apply($employers, $from, $to);

        // An event may have been looking for the retired group. Left alone it
        // would match nobody, because JobFairEvent::wantsIndustry() compares
        // the stored strings exactly.
        foreach (DB::table('job_fair_events')->whereNotNull('target_industries')->get() as $event) {
            $targets = json_decode($event->target_industries ?? 'null', true);
            if (!is_array($targets) || !in_array($from, $targets, true)) {
                continue;
            }

            $targets = array_values(array_unique(array_map(
                fn($group) => $group === $from ? $to : $group,
                $targets
            )));

            DB::table('job_fair_events')
                ->where('job_fair_events_id', $event->job_fair_events_id)
                ->update(['target_industries' => json_encode($targets)]);
        }
    }

    private function apply($employers, string $from, string $to): void
    {
        if ($employers->isEmpty()) {
            return;
        }

        DB::table('employer_nsrp_registrations')
            ->whereIn('employer_nsrp_registrations_id', $employers)
            ->update(['industry_group' => $to]);

        // The vacancy keeps its own copy, and that copy is what a fair reads
        // when it decides which postings it takes.
        DB::table('job_qualifications')
            ->whereIn('company_id', $employers)
            ->where('industry_group', $from)
            ->update(['industry_group' => $to]);
    }
};
