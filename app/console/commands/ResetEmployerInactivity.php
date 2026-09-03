<?php

namespace App\Console\Commands;

use App\Models\EmployerNsrpRegistration;
use Illuminate\Console\Command;

class ResetEmployerInactivity extends Command
{
    protected $signature = 'employers:inactivity-reset'
        . ' {--employer= : Reset only this employer, by employer_nsrp_registrations_id}';
    protected $description = 'Clear the inactivity timestamps so the ladder can be walked again';

    // ── Ang pagbalik sa sugod. ──
    //
    // Ang sweep dili mosulat kaduha sa parehas nga employer — kana ang tibuok
    // punto sa mga timestamp — mao nga ang ikaduhang demo dili modagan gawas
    // kung limpyohan una ang mga kolum. Kini ang naglimpyo kanila.
    //
    // Ang account mismo wala gihilabti. Kung na-inactive na siya, ang
    // pag-abli pag-usab kay desisyon sa staff sa Inactive Employer Account nga tab,
    // dili usa ka butang nga hilom nga buhaton sa usa ka command.
    public function handle()
    {
        $only = $this->option('employer');

        $employers = EmployerNsrpRegistration::query()
            ->when($only, fn($q) => $q->where('employer_nsrp_registrations_id', $only))
            ->where(fn($q) => $q
                ->whereNotNull('inactivity_notified_at')
                ->orWhereNotNull('inactivity_second_notified_at')
                ->orWhereNotNull('inactivity_disable_prompted_at')
                ->orWhereNotNull('inactivity_responded_at'))
            ->get();

        if ($employers->isEmpty()) {
            $this->info('Nothing to reset — no employer is on the ladder.');
            return 0;
        }

        // Ang tibuok lista usa ka dako nga butang nga buhaton nga walay
        // pangutana; ang usa ka employer nga gihinganlan kay gipangayo na.
        if (!$only && !$this->confirm(
            'Reset ' . $employers->count() . ' employer(s)? Pass --employer= to do just one.'
        )) {
            $this->line('Nothing was changed.');
            return 0;
        }

        foreach ($employers as $employer) {
            $employer->update([
                'inactivity_notified_at'         => null,
                'inactivity_second_notified_at'  => null,
                'inactivity_disable_prompted_at' => null,
                'inactivity_responded_at'        => null,
                'inactivity_status'              => null,
                'inactivity_response'            => null,
            ]);

            $this->info($employer->company_name . ' — back to the start of the ladder.');
        }

        $this->info('Reset ' . $employers->count() . ' employer(s). The account itself was not touched.');

        if ($employers->contains(fn($e) => $e->dormant_at)) {
            $this->warn('Some of these accounts are still inactive. Switch them back on from'
                . ' Employers → Inactive Employer Account; that is the staff\'s decision, not this command\'s.');
        }

        return 0;
    }
}
