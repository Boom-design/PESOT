<?php

use App\Models\EmployerNsrpRegistration;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Rewrite every stored industry group into PSIC Revision 5 wording.
 *
 * The PESO manager, 2026-10-20: the office answers to the PSA classification,
 * and the dropdown was still the older PSIC wording from the NSRP paper form.
 * Changing the constant alone would have left three columns holding answers
 * that are no longer offered, and the strict in_array() in
 * JobFairEvent::wantsIndustry() would then invite nobody for those employers.
 *
 * Three places hold the answer:
 *   - employer_nsrp_registrations.industry_group — what the establishment said
 *   - job_qualifications.industry_group          — copied onto each vacancy
 *   - job_fair_events.target_industries          — a JSON list on the event
 *
 * Only the ten renamed groups are touched. Mining and Quarrying,
 * Manufacturing, Construction, Education and Overseas Manpower Services are
 * worded the same in Revision 5 and are left alone.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (EmployerNsrpRegistration::RETIRED_INDUSTRY_GROUPS as $old => $new) {
            $this->rename($old, $new);
        }
    }

    /**
     * Back to the old wording.
     *
     * The five groups that did not change are not in the map, so they are not
     * touched on the way down either. A section new in Revision 5 — Section D,
     * E, J, K, N, O, S or U — has no older wording to go back to; an
     * establishment that picked one after this migration ran keeps it, and the
     * registration form will refuse it until someone picks again. Rolling this
     * back is a repair step, not a supported state.
     */
    public function down(): void
    {
        foreach (EmployerNsrpRegistration::RETIRED_INDUSTRY_GROUPS as $old => $new) {
            $this->rename($new, $old);
        }
    }

    private function rename(string $from, string $to): void
    {
        DB::table('employer_nsrp_registrations')
            ->where('industry_group', $from)
            ->update(['industry_group' => $to]);

        DB::table('job_qualifications')
            ->where('industry_group', $from)
            ->update(['industry_group' => $to]);

        // target_industries is a JSON array, so the rename happens one event
        // at a time: a string replace on the column would also hit a longer
        // group that contains this one as a substring.
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
};
