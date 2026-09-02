<?php

namespace App\Console\Commands;

use App\Models\EmployerNsrpRegistration;
use Illuminate\Console\Command;

class ShowEmployerInactivity extends Command
{
    protected $signature = 'employers:inactivity-status'
        . ' {--employer= : Show only this employer, by employer_nsrp_registrations_id}';
    protected $description = 'Show where each employer stands on the inactivity ladder';

    // ── Ang lakang nga ginabasa sa tawo, dili sa database. ──
    //
    // Ang tulo ka timestamp sa inactivity nagsulti sa istorya kung basahon
    // sila nga magkauban, apan ang pagtan-aw kanila sa phpMyAdmin nagpasabot
    // nga ang nagbasa mao ang mo-hubad kanila. Kini ang mo-hubad.
    //
    // PESO, 2026-08-31: gihimo kini para sa depensa. Ang tinker nga usa ka
    // linya nga naghimo sa parehas nga butang naguba sa PowerShell — ang $e
    // gikaon isip PowerShell nga variable sa dili pa siya makaabot sa PHP —
    // ug ang pag-away sa quoting atubangan sa panel dili maayo nga tan-awon.
    public function handle()
    {
        $only = $this->option('employer');

        $employers = EmployerNsrpRegistration::with('employer')
            ->when($only, fn($q) => $q->where('employer_nsrp_registrations_id', $only))
            ->orderBy('employer_nsrp_registrations_id')
            ->get();

        if ($employers->isEmpty()) {
            $this->warn('No employer found.');
            return 0;
        }

        $rows = [];

        foreach ($employers as $employer) {
            $status     = $employer->companyStatus();
            $lastPosted = $employer->jobs()->max('created_at');

            $rows[] = [
                $employer->employer_nsrp_registrations_id,
                mb_strimwidth($employer->company_name ?? '-', 0, 30, '...'),
                $employer->is_overseas ? 'overseas' : 'local',
                optional($employer->employer)->status ?? '-',
                $status['label'],
                $lastPosted ? \Carbon\Carbon::parse($lastPosted)->format('Y-m-d') : 'never',
                $this->stage($employer),
            ];
        }

        $this->table(
            ['ID', 'Company', 'Desk', 'Account', 'Company status', 'Last posted', 'Ladder'],
            $rows
        );

        // Ang usa ka employer nga gipangita gyud angay tan-awon nga tibuok.
        if ($only && $employers->count() === 1) {
            $employer = $employers->first();
            $this->newLine();
            $this->line('  First notice   : ' . ($employer->inactivity_notified_at ?: '—'));
            $this->line('  Second notice  : ' . ($employer->inactivity_second_notified_at ?: '—'));
            $this->line('  Handed to staff: ' . ($employer->inactivity_disable_prompted_at ?: '—'));
            $this->line('  Employer answer: ' . ($employer->inactivity_responded_at ?: '—'));
            if ($employer->inactivity_responded_at) {
                $this->line('    status : ' . ($employer->inactivity_status ?: '—'));
                $this->line('    reason : ' . ($employer->inactivity_response ?: '—'));
            }
            $this->line('  Set to inactive: ' . ($employer->dormant_at ?: '—'));

            $hidden = \App\Models\Job::where('company_id', $employer->employer_nsrp_registrations_id)
                ->whereNotNull('dormant_closed_at')->count();
            $total  = \App\Models\Job::where('company_id', $employer->employer_nsrp_registrations_id)->count();
            $this->line('  Postings hidden: ' . $hidden . ' of ' . $total);
            $this->newLine();
        }

        return 0;
    }

    /** Which rung the employer is on, in the order the sweep walks them. */
    private function stage(EmployerNsrpRegistration $employer): string
    {
        if ($employer->dormant_at) {
            return $employer->inactivity_responded_at ? '5. answered, awaiting staff' : '5. inactive';
        }
        if ($employer->inactivity_responded_at)        return '4. answered';
        if ($employer->inactivity_disable_prompted_at) return '3. with staff';
        if ($employer->inactivity_second_notified_at)  return '2. second notice';
        if ($employer->inactivity_notified_at)         return '1. first notice';

        return '—';
    }
}
