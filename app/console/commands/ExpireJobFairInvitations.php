<?php

namespace App\Console\Commands;

use App\Models\Announcement;
use App\Models\JobFairEvent;
use App\Models\JobFairParticipant;
use Illuminate\Console\Command;

class ExpireJobFairInvitations extends Command
{
    protected $signature = 'jobfair:expire-invitations';
    protected $description = 'Lapse job fair invitations no employer has answered within the confirmation window';

    // ── PESO Job Fair staff, 2026-08-23: "pag 1 week dili mo accept si employer
    // ── mangita syag lahi na employer."
    // ──
    // ── Ang paglapse dili silot. Usa siya ka signal sa staff: ang event usa ka
    // ── bulan nga abante, mao nga sa ikapito nga adlaw naa pay tulo ka semana
    // ── nga mangita ug lain. Ang employer nga mitubag ug ulahi dawaton gihapon
    // ── — tan-awa ang CompanyWebController::respondJobFair. Ang tumong mao ang
    // ── mapuno ang mga slot, dili ang pagsalikway sa gustong moapil. ──
    public function handle()
    {
        $window = (int) config('peso.jobfair.confirm_window_days');
        $cutoff = now()->subDays($window);

        $events = JobFairEvent::whereDate('event_date', '>=', today())
            ->where('status', '!=', 'completed')
            ->get();

        $totalLapsed = 0;

        foreach ($events as $event) {
            $participants = JobFairParticipant::where('job_fair_id', $event->job_fair_events_id)
                ->where('confirmation_status', 'pending')
                ->whereNotNull('invited_at')
                ->where('invited_at', '<=', $cutoff)
                ->get();

            if ($participants->isEmpty()) {
                continue;
            }

            JobFairParticipant::whereIn('job_fair_participants_id',
                    $participants->pluck('job_fair_participants_id'))
                ->update(['confirmation_status' => 'expired']);

            // ── Ang mensahe nagsulti sa tinuod: nalapse ang imbitasyon, apan
            // ── bukas pa ang pultahan. Kung dili ni isulti, ang employer nga
            // ── nakalimot lang mag-akong wala na siyay kapaingnan. ──
            Announcement::sendToEmployers([
                'type'           => 'job_fair_invitation_expired',
                'title'          => 'Job Fair Invitation Lapsed',
                'message'        => 'Your invitation to ' . $event->title . ' on '
                                    . $event->event_date->format('M d, Y')
                                    . ' was not answered within ' . $window . ' days, so the office is'
                                    . ' inviting other employers. You may still confirm if you want to'
                                    . ' join — open the invitation and press Confirm.',
                'reference_type' => 'job_fair',
                'reference_id'   => $event->job_fair_events_id,
            ], $participants->pluck('employer_id'));

            $this->info('"' . $event->title . '" — ' . $participants->count() . ' invitation(s) lapsed.');
            $totalLapsed += $participants->count();
        }

        $this->info("Lapsed {$totalLapsed} job fair invitation(s).");
        return 0;
    }
}
