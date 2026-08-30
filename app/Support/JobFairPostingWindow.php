<?php

namespace App\Support;

use App\Models\Announcement;
use App\Models\Job;
use App\Models\JobFairEmploymentRequest;
use App\Models\JobFairEvent;
use Illuminate\Support\Collection;

// ── Kanus-a makita sa jobseeker ang mga job fair posting.
// ──
// ── Ang usa ka job fair nga posting natawo nga status='closed'. Bisan
// ── ma-approve na siya sa staff, magpabilin siyang tago — walay pulos ang
// ── pag-anunsyo ug bakante nga ang tinuod nga adlaw sa pagpangita kay usa pa
// ── ka bulan ang gilay-on.
// ──
// ── PESO 2026-08-13: ang event kinahanglan i-create dili moubos sa 10 ka adlaw
// ── nga abante. Ang mga posting mo-abli sa TUNGA sa maong lead time — 5 ka
// ── adlaw sa dili pa ang fair — aron bag-o pa ang bakante sa hunahuna sa
// ── jobseeker inig-abot sa adlaw. Kung mo-abli dayon sila pag-create sa event,
// ── 10 ka adlaw silang magtan-aw ug listahan nga dili pa nila maadtoan.
// ──
// ── Usa ra ka lugar ang naghubad niini: ang scheduled command ug ang pag-
// ── approve sa staff pareho niini mangutana, mao nga dili gyud sila magkalahi.
// ── Kung ang approval mo-desisyon nga bukas na ang window ug ang command dili,
// ── ang usa ka posting mahimong bukas nga nag-inusara — o dili gyud mo-abli. ──
class JobFairPostingWindow
{
    // ── Pila ka adlaw sa dili pa ang fair mo-abli ang mga posting. ──
    public static function daysBefore(): int
    {
        return (int) config('peso.jobfair.open_postings_days_before', 5);
    }

    // ── Ang mga event nga sulod na sa window karon. Mahimong sobra sa usa kung
    // ── duol ang petsa sa duha ka fair; usa ra gihapon ka pag-abli ang buhaton
    // ── kay pareho ra man ang listahan sa posting. ──
    public static function eventsInWindow(): Collection
    {
        return JobFairEvent::whereDate('event_date', '>=', today())
            ->whereDate('event_date', '<=', today()->addDays(self::daysBefore()))
            ->where('status', '!=', 'completed')
            ->orderBy('event_date')
            ->get();
    }

    public static function isOpen(): bool
    {
        return self::eventsInWindow()->isNotEmpty();
    }

    public static function nextEvent(): ?JobFairEvent
    {
        return self::eventsInWindow()->first();
    }

    // ── Ang sunod nga fair, bisan layo pa. Gigamit sa teksto nga nagsulti sa
    // ── employer kung kanus-a mo-abli ang iyang posting. ──
    public static function nextScheduledEvent(): ?JobFairEvent
    {
        return JobFairEvent::whereDate('event_date', '>=', today())
            ->where('status', '!=', 'completed')
            ->orderBy('event_date')
            ->first();
    }

    // ── Bukas na ba ang window para NIINING fair?
    // ──
    // ── Lahi ni sa isOpen(), nga nangutana kung naa bay BISAN UNSANG fair sulod
    // ── sa window. Sukad nga ang posting sakop na sa usa ka piho nga fair, ang
    // ── isOpen() dili na sakto sa pag-desisyon: ang gidawat sa Oktubre nga fair
    // ── mo-abli dayon kung ang Septyembre nga fair duol na. ──
    public static function opensFor(JobFairEvent $event): bool
    {
        return !$event->event_date->copy()->subDays(self::daysBefore())->isFuture();
    }

    // ── Usa ka tudling nga isulti sa employer ug sa staff: buhi na ba ang
    // ── posting, kanus-a siya mabuhi, o wala pa gyuy fair nga hulaton. Usa ra
    // ka teksto aron dili magkalahi ang mensahe sa duha ka approval path. ──
    /**
     * @param JobFairEvent|null $event Ang fair nga gisudlan sa posting. Kung
     *                                 wala, ang sunod nga naka-iskedyul.
     */
    public static function liveNote(?JobFairEvent $event = null): string
    {
        // ── Ang posting nga gidawat sa Oktubre nga fair dili mo-abli para sa
        // ── Septyembre. Kung wala giingon kung asa siya na-apil, ang sunod ra
        // ── nga fair ang mahibaw-an nato — sakto kadto kaniadto, kay bisan
        // ── unsang posting mo-abli sa bisan unsang fair. ──
        if ($event) {
            $openDate = $event->event_date->copy()->subDays(self::daysBefore());

            if (!$openDate->isFuture()) {
                return 'It is live now — ' . $event->title . ' is within ' . self::daysBefore() . ' days.';
            }

            return 'It will go live on ' . $openDate->format('M d, Y') . ', '
                   . self::daysBefore() . ' days before ' . $event->title
                   . ' on ' . $event->event_date->format('M d, Y') . '.';
        }

        if (self::isOpen()) {
            return 'It is live now — a job fair is within ' . self::daysBefore() . ' days.';
        }

        $event = self::nextScheduledEvent();

        if (!$event) {
            return 'It will go live ' . self::daysBefore()
                   . ' days before the next job fair event, once PESO schedules one.';
        }

        $openDate = $event->event_date->copy()->subDays(self::daysBefore());

        return 'It will go live on ' . $openDate->format('M d, Y') . ', '
               . self::daysBefore() . ' days before ' . $event->title
               . ' on ' . $event->event_date->format('M d, Y') . '.';
    }

    // ── Ang mga posting nga andam na apan naghulat pa.
    // ──
    // ── withinDeadline(): ang nalabyan na nga posting closed pud, apan nahuman
    // ── na kadto — dili siya naghulat ug event. Kung wala ni, mabanhaw ang
    // ── patay nga posting kada fair. ──
    /**
     * @param JobFairEvent|null $event  Ang fair nga giablihan. Kung wala,
     *                                  ang tanan nga anaa sa window karon.
     */
    public static function pendingPostings(?JobFairEvent $event = null): Collection
    {
        // ── Ang posting kinahanglan sakop na sa maong fair.
        // ──
        // ── Kaniadto walay sala dinhi: bisan unsang job fair nga posting mo-abli
        // ── sa bisan unsang fair. Sakto pa kadto samtang ang posting mosulod sa
        // ── fair nga siya ra. Karon ang Job Fair desk na ang mopili kung asa siya
        // ── mahiluna, mao nga ang posting nga gidawat para sa Oktubre nga fair
        // ── dili na angay mo-abli lima ka adlaw sa dili pa ang Septyembre. ──
        $events = $event ? collect([$event]) : self::eventsInWindow();

        if ($events->isEmpty()) {
            return collect();
        }

        $jobIds = JobFairEmploymentRequest::whereIn('job_fair_id', $events->pluck('job_fair_events_id'))
            ->pluck('job_id');

        if ($jobIds->isEmpty()) {
            return collect();
        }

        return Job::with('company')
            ->whereIn('job_qualifications_id', $jobIds)
            ->where('schedule_type', 'job_fair')
            ->where('posting_status', 'approved')
            ->where('status', 'closed')
            ->withinDeadline()
            ->get();
    }

    // ── I-abli ang tanan nga naghulat, ug sultihi ang employer ug ang
    // ── jobseeker. Mo-return sa gidaghanon nga na-abli.
    // ──
    // ── Ang $event nagpili kung kinsang posting ang abtan, ug nagsulti sad sa
    // ── mensahe. Ang gidawat para sa laing fair magpabilin nga sirado. ──
    public static function openAll(?JobFairEvent $event = null): int
    {
        $postings = self::pendingPostings($event);

        if ($postings->isEmpty()) {
            return 0;
        }

        $event = $event ?: self::nextEvent();

        foreach ($postings as $job) {
            $job->update(['status' => 'open']);

            if ($job->company_id) {
                Announcement::sendToEmployers([
                    'type'           => 'job_fair_posting_opened',
                    'title'          => 'Job Fair Posting Now Live 🎪',
                    'message'        => 'Your job fair posting "' . $job->title . '" is now open and visible to jobseekers'
                                        . ($event ? ', ahead of ' . $event->title . ' on ' . $event->event_date->format('M d, Y') : '') . '.',
                    'reference_type' => 'job',
                    'reference_id'   => $job->job_qualifications_id,
                ], $job->company_id);
            }
        }

        self::notifyJobseekers($postings);

        return $postings->count();
    }

    // ── Ang jobseeker nga ang gustong trabaho pareho sa titulo makadawat ug
    // ── "Matching Job Vacancy Found"; ang uban makadawat ra sa ordinaryo nga
    // ── "New Job Vacancy Posted". Gikuha kini gikan sa storeJobFairEvent aron
    // ── mag-uban ang mensahe ug ang tinuod nga pag-abli. ──
    private static function notifyJobseekers(Collection $postings): void
    {
        $registrations = \App\Models\JobseekerRegistration::whereHas(
                'user', fn($q) => $q->where('status', 'approved')
            )
            ->with('nsrp')
            ->get();

        if ($registrations->isEmpty()) {
            return;
        }

        foreach ($postings as $job) {
            $matched = collect();
            $others  = collect();
            $title   = strtolower($job->title);

            foreach ($registrations as $registration) {
                $preferred = $registration->nsrp->preferred_occupations ?? [];
                $isMatch   = false;

                foreach ($preferred as $occupation) {
                    if (strtolower(trim($occupation)) === $title) {
                        $isMatch = true;
                        break;
                    }
                }

                $isMatch
                    ? $matched->push($registration->jobseeker_registrations_id)
                    : $others->push($registration->jobseeker_registrations_id);
            }

            if ($matched->isNotEmpty()) {
                Announcement::sendToJobseekers([
                    'type'           => 'job_match',
                    'title'          => 'Matching Job Vacancy Found! 💼',
                    'message'        => 'A job vacancy matching your preferred position "' . $job->title . '" from '
                                        . ($job->company->company_name ?? 'an employer') . ' is now available. Would you like to apply?',
                    'reference_type' => 'job',
                    'reference_id'   => $job->job_qualifications_id,
                ], $matched);
            }

            if ($others->isNotEmpty()) {
                Announcement::sendToJobseekers([
                    'type'           => 'job_posted',
                    'title'          => 'New Job Vacancy Posted 💼',
                    'message'        => 'A new job vacancy "' . $job->title . '" from '
                                        . ($job->company->company_name ?? 'an employer') . ' is now available!',
                    'reference_type' => 'job',
                    'reference_id'   => $job->job_qualifications_id,
                ], $others);
            }
        }
    }
}
