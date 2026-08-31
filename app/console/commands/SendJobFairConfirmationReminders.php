<?php

namespace App\Console\Commands;

use App\Models\Announcement;
use App\Models\JobFairEvent;
use App\Models\JobFairParticipant;
use App\Support\JobFairAudience;
use Illuminate\Console\Command;

class SendJobFairConfirmationReminders extends Command
{
    protected $signature = 'jobfair:send-confirmation-reminders';
    protected $description = 'Remind employers who have not responded once a job fair event is close and still short of confirmations';

    // ── Kaniadto usa ka buton ni sa Employer Participants tab. Gikuha siya:
    // ── ang staff dili angay magbantay kung kanus-a mo-abot ang petsa, ug ang
    // ── manual ra nga nabilin sa opisina kay ang SMS sa Notification nav.
    // ──
    // ── Kaniadto giihap ang window gikan sa adlaw sa event — lima ka adlaw sa
    // ── wala pa. Ulahi na kaayo: ang DOLE cutoff naa sa ikanapulo nga adlaw,
    // ── mao nga ang reminder mo-abot human na maipasa ang roster.
    // ──
    // ── Karon giihap gikan sa deadline sa imbitasyon mismo. Ang employer nga
    // ── na-invite kagahapon ug ang na-invite lima ka adlaw na ang milabay
    // ── managlahi ug nahibiling adlaw, ug ang ilaha ang mahinungdanon — mao
    // ── nay mawala kung dili sila motubag. ──
    public function handle()
    {
        $window = (int) config('peso.jobfair.reminder_days_before');
        $confirmWindow = (int) config('peso.jobfair.confirm_window_days');

        // Ang imbitasyon nga na-invite sa wala pa niini nga gutlo naa na sulod
        // sa katapusang $window ka adlaw sa iyang orasan.
        $remindFrom = now()->subDays(max($confirmWindow - $window, 0));

        $events = JobFairEvent::whereDate('event_date', '>=', today())
            ->where('status', '!=', 'completed')
            ->get();

        $totalSent = 0;

        foreach ($events as $event) {
            // Igo na ang ni-confirm. Wala nay gukdon.
            if (JobFairAudience::gateMet($event)) {
                continue;
            }

            // Ang adlaw mismo sa event dili na angay gukdon — walay pulos ang
            // pagpahinumdom sa buntag nga naa na ang tawo sa venue o wala.
            if ($event->daysUntil() <= 0) {
                continue;
            }

            // Kinsa nay nakadawat na ug reminder para niini nga event. Adlaw-
            // adlaw ni modagan, mao nga kung wala ni, lima ka adlaw nga sunod-
            // sunod ang parehas nga mensahe sa parehas nga employer.
            $alreadyReminded = Announcement::where('reference_type', 'job_fair')
                ->where('reference_id', $event->job_fair_events_id)
                ->where('type', 'job_fair_reminder')
                ->whereNotNull('employer_id')
                ->pluck('employer_id');

            $employerIds = JobFairParticipant::where('job_fair_id', $event->job_fair_events_id)
                ->where('confirmation_status', 'pending')
                ->whereNotNull('invited_at')
                ->where('invited_at', '<=', $remindFrom)
                ->pluck('employer_id')
                ->reject(fn($id) => $alreadyReminded->contains($id))
                ->values();

            if ($employerIds->isEmpty()) {
                continue;
            }

            Announcement::sendToEmployers([
                'type'           => 'job_fair_reminder',
                'title'          => 'Job Fair Reminder ⏰',
                'message'        => 'Your invitation to ' . $event->title . ' on '
                                    . $event->event_date->format('M d, Y') . ' lapses in '
                                    . $window . ' day' . ($window === 1 ? '' : 's')
                                    . '. Please confirm your participation before then, or the office'
                                    . ' will invite other employers.',
                'reference_type' => 'job_fair',
                'reference_id'   => $event->job_fair_events_id,
            ], $employerIds);

            $this->info('"' . $event->title . '" — ' . $employerIds->count()
                . ' employer(s) reminded, ' . JobFairAudience::confirmedCount($event)
                . ' of ' . JobFairAudience::threshold() . ' confirmed.');

            $totalSent += $employerIds->count();
        }

        $this->info("Sent {$totalSent} job fair confirmation reminder(s).");
        return 0;
    }
}
