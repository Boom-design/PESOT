<?php

namespace App\Console\Commands;

use App\Models\Application;
use App\Models\JobseekerWorkExperience;
use App\Support\PesoEmployment;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Put hires made before 2026-09-14 onto the jobseeker's NSRP work experience.
 *
 * From 2026-09-14 a hire writes its own Work Experience row and pauses the
 * jobseeker's applying. Hires recorded before that have no row, so their
 * jobseeker sees no "employed through PESO" note and is never paused. This
 * writes those rows once, the way the hire would have written them.
 *
 * A hire followed by a later application or a later hire is a job that
 * ended — closed as Finished Contract in the month of that next step. The last
 * hire with nothing after it is the job the person still holds.
 *
 * Only hires with no row yet are touched, so running it twice changes nothing.
 *
 * Run: php artisan peso:backfill-hires --dry-run   (list what would change)
 *      php artisan peso:backfill-hires
 */
class BackfillPesoHires extends Command
{
    protected $signature = 'peso:backfill-hires {--dry-run : List what would be written without writing it}';
    protected $description = 'Write hires made before the hire-to-work-experience change onto NSRP work experience';

    public function handle()
    {
        $dry = (bool) $this->option('dry-run');

        $hires = Application::with('job.company', 'jobseeker.nsrp')
            ->where('status', 'hired')
            ->whereNotIn('job_matching_id', JobseekerWorkExperience::whereNotNull('job_matching_id')->select('job_matching_id'))
            ->whereHas('jobseeker.nsrp')
            ->orderBy('hired_at')
            ->get()
            ->groupBy('jobseeker_id');

        if ($hires->isEmpty()) {
            $this->info('Every hire is already on its work experience. Nothing to do.');
            return 0;
        }

        $written = $ended = 0;

        DB::transaction(function () use ($hires, $dry, &$written, &$ended) {
            foreach ($hires as $jobseekerId => $list) {
                $list = $list->values();
                $name = trim(($list[0]->jobseeker->first_name ?? '') . ' ' . ($list[0]->jobseeker->surname ?? ''));

                foreach ($list as $index => $hire) {
                    $hiredAt = Carbon::parse($hire->hired_at ?? $hire->updated_at);

                    // What came after this hire, if anything: another application or the next hire.
                    $nextApplication = Application::where('jobseeker_id', $jobseekerId)
                        ->where('job_matching_id', '!=', $hire->job_matching_id)
                        ->where('created_at', '>', $hiredAt)
                        ->min('created_at');
                    $nextHire = $list->get($index + 1)?->hired_at;

                    $endings = array_filter([
                        $nextApplication ? Carbon::parse($nextApplication) : null,
                        $nextHire ? Carbon::parse($nextHire) : null,
                    ]);
                    $endedAt = $endings ? min($endings) : null;

                    $line = sprintf('%s — %s at %s, hired %s: %s',
                        $name ?: "Jobseeker #$jobseekerId",
                        $hire->job->title ?? 'Job',
                        $hire->job->company->company_name ?? 'Employer',
                        $hiredAt->format('M d, Y'),
                        $endedAt ? 'ended ' . $endedAt->format('M Y') : 'still employed');
                    $this->line($line);

                    if ($dry) {
                        continue;
                    }

                    PesoEmployment::syncHire($hire);
                    $written++;

                    if ($endedAt) {
                        $row   = JobseekerWorkExperience::where('job_matching_id', $hire->job_matching_id)->first();
                        $start = PesoEmployment::startMonth($row);
                        $month = $endedAt->copy()->startOfMonth();
                        if ($start && $month->lt($start)) {
                            $month = $start;
                        }
                        PesoEmployment::end($row, 'finished_contract', $month);
                        $ended++;
                    }
                }
            }
        });

        $this->info($dry
            ? 'Dry run — nothing written.'
            : "Wrote {$written} work experience row(s); {$ended} closed as ended, the rest are current jobs.");

        return 0;
    }
}
