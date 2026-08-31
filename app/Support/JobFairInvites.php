<?php

namespace App\Support;

use App\Models\Announcement;
use App\Models\EmployerNsrpRegistration;
use App\Models\JobFairEvent;
use App\Models\JobFairParticipant;
use App\Models\User;
use Illuminate\Support\Collection;

// ── Kinsa ang dawaton sa usa ka job fair event, ug kanus-a.
// ──
// ── Tulo ka managlahi nga gutlo ang nangutana sa parehas nga pangutana:
// ──   1. paghimo sa event — kinsa ang i-invite karon dayon;
// ──   2. pag-approve sa requirements sa usa ka employer — asa siya angay
// ──      i-apil, kay karon pa siya nahimong eligible;
// ──   3. pagdugang sa kwota sa usa ka event — kinsa ang bag-o nga naay lugar.
// ──
// ── Kaniadto, ang una ra ang naay code — usa ka loop sulod sa
// ── storeJobFairEvent — mao nga ang employer nga ni-rehistro human niadto
// ── wala gyud makadawat ug invitation para niadto nga event. Ang bugtong
// ── paagi kay tangtangon ang event ug himoon pag-usab, ug mawala ang tanan
// ── nga na-confirm na.
// ──
// ── Walay buton dinhi. Ang opisina nakasulti na sa gidaghanon nga ilang
// ── gikinahanglan sa paghimo sa event; samtang kulang pa kadto, mosulod ang
// ── bag-o. Ang bugtong manual sa tibuok job fair kay ang SMS. ──
class JobFairInvites
{
    // ── Gipangita ba niini nga event kining tipo sa employer?
    // ──
    // ── Kaniadto hasRoomFor kini, ug nag-ihap siya sa na-confirm batok sa
    // ── kwota. PESO Job Fair staff, 2026-08-23: "walay maximum sa job fair
    // ── event kay depende na sa sponsor sa job fair." Ang gidaghanon dili na
    // ── utlanan, mao nga wala nay lugar nga mahurot ug wala nay iihap. Ang
    // ── nahibilin nga pangutana kay ang tipo: local ba, overseas ba, o
    // ── pareho. ──
    public static function catersTo(JobFairEvent $event, bool $isOverseas): bool
    {
        return $event->catersTo($isOverseas);
    }

    // ── Angay ba i-invite kining employer sa maong event?
    // ──
    // ── Duha ka gutlo ang nangutana niini — ang paghimo sa event ug ang pag-
    // ── approve sa requirements — mao nga usa ra ka lugar ang naghubad, kay
    // ── kung magkalahi sila, ang employer nga na-approve human sa event
    // ── makadawat ug imbitasyon nga dili gyud unta para niya. ──
    public static function isEligible(JobFairEvent $event, EmployerNsrpRegistration $employer): bool
    {
        return self::catersTo($event, (bool) $employer->is_overseas)
            && $event->wantsIndustry($employer->industry_group);
    }

    // ── Ang mga event nga wala pa nagsugod. Ang natapos na nga fair walay
    // ── pulos nga dad-an ug bag-ong employer. ──
    public static function upcomingEvents(): Collection
    {
        return JobFairEvent::whereDate('event_date', '>=', today())
            ->where('status', '!=', 'completed')
            ->orderBy('event_date')
            ->get();
    }

    // ── Asa niining employer angay i-apil karon. Gilaktawan ang event nga
    // ── participant na siya — bisan unsa pa ang iyang gitubag didto. ──
    public static function eventsOpenTo(EmployerNsrpRegistration $employer): Collection
    {
        // ── Ang overseas nga employer dili awtomatiko nga masulod.
        // ── PESO SRA, 2026-08-26: si SRA ang mopili kung kinsang ahensya ang
        // ── dad-on sa fair, human siya mangayo ug permiso sa pangulo sa PESO.
        // ── Kung mo-invite ang sistema para niya, wala nay bili ang iyang
        // ── pagpili — na-invite na silang tanan sa wala pa siya makatan-aw. ──
        if ($employer->is_overseas) {
            return collect();
        }

        $alreadyIn = JobFairParticipant::where('employer_id', $employer->employer_nsrp_registrations_id)
            ->pluck('job_fair_id');

        return self::upcomingEvents()
            ->reject(fn(JobFairEvent $event) => $alreadyIn->contains($event->job_fair_events_id))
            ->filter(fn(JobFairEvent $event) => self::isEligible($event, $employer))
            ->values();
    }

    // ── Ang tanan nga eligible para niini nga event karon.
    // ──
    // ── Parehas gyud nga sukdanan sa gigamit sukad: approved nga account UG
    // ── approved nga requirements. Ang employer nga wala pay dokumento dili
    // ── makapost ug bakante, mao nga ang invitation kaniya kay imbitasyon nga
    // ── dili niya magamit. ──
    public static function eligibleFor(JobFairEvent $event): Collection
    {
        $wantsLocal    = self::catersTo($event, false);
        $wantsOverseas = self::catersTo($event, true);

        if (!$wantsLocal && !$wantsOverseas) {
            return collect();
        }

        // ── Ang giihap kay ESTABLISEMENTO, dili account.
        // ──
        // ── PESO IT, 2026-08-26: usa ka HR mahimong maghawid ug duha ka
        // ── kompanya sa usa ka email. Ang mo-adto sa fair kay ang kompanya,
        // ── ug ang matag usa naay kaugalingong papel ug kaugalingong
        // ── industriya. Kung ang account ang giihap, ang ikaduhang kompanya
        // ── dili gyud ma-invite bisan siya pa ang mas haom sa fair. ──
        return EmployerNsrpRegistration::query()
            ->whereHas('employer', fn($q) => $q->where('role', 'company')->where('status', 'approved'))
            ->whereHas('requirement', fn($q) => $q->where('status', 'approved'))
            ->where(function ($q) use ($wantsLocal, $wantsOverseas) {
                if ($wantsLocal)    $q->orWhere('is_overseas', false);
                if ($wantsOverseas) $q->orWhere('is_overseas', true);
            })
            // ── Ang fair mahimong mangayo ug piho nga industriya.
            // ──
            // ── PESO Job Fair staff, 2026-08-23: sa titulo pa lang sa job fair
            // ── ug kung unsa iyang gipangita, mao na ang employer nga padad-an
            // ── — dili tanan nga na-approve.
            // ──
            // ── Walay gipili nga industriya = tanan, mao gihapon ang daan nga
            // ── batasan. Kung naa, ang kompanya nga wala pa nakabutang sa iyang
            // ── industriya DILI maapil: wala man siya nagsulti kung unsa siya,
            // ── walay ikatandi. ──
            ->when($event->target_industries,
                fn($q) => $q->whereIn('industry_group', $event->target_industries))
            ->get();
    }

    /**
     * Ang eligible nga i-invite sa sistema mismo, walay tawo nga mopili.
     *
     * Lokal ra. Ang overseas gilaktawan dinhi tinuyo: si SRA ang mopili kanila
     * sa Invite Overseas Agencies nga panel, human siya mangayo ug permiso sa pangulo
     * sa PESO. Ang pagpili nga gibuhat na sa sistema dili na pagpili.
     */
    public static function autoEligibleFor(JobFairEvent $event): Collection
    {
        return self::eligibleFor($event)
            ->reject(fn(EmployerNsrpRegistration $employer) => (bool) $employer->is_overseas)
            ->values();
    }

    // ── Ang eligible nga employer nga wala pa sa listahan sa event.
    // ──
    // ── PESO Job Fair staff, 2026-08-23: kung dili motubag ang employer,
    // ── "mangita silag lahi na employer, pero manufacturer ra japon" — ang
    // ── gipangita sa fair dili mausab tungod lang kay naay wala mitubag.
    // ── Mao nga gigamit dinhi ang parehas gyud nga sukdanan sa eligibleFor():
    // ── cater UG target_industries. Ang paghatag ug hotel nga agency para sa
    // ── Manufacturing nga fair mao untay pagsulti nga usba ang fair, ug dili
    // ── kana ang gipangayo.
    // ──
    // ── Sagad blangko kini nga listahan, ug husto kana: ang tanan sulod sa
    // ── industriya na-invite na sa unang adlaw. Ang mahulog dinhi kay ang
    // ── employer nga bag-o lang na-klasipika o bag-o lang na-approve. ──
    /**
     * @param bool|null $overseas  null = tanan; false = lokal ra; true = overseas ra.
     *                             Ang Job Fair staff mangayo ug lokal, ang SRA
     *                             ug overseas — duha ka desk, duha ka listahan,
     *                             usa ka sukdanan.
     */
    public static function notYetInvited(JobFairEvent $event, ?bool $overseas = null): Collection
    {
        $alreadyIn = JobFairParticipant::where('job_fair_id', $event->job_fair_events_id)
            ->pluck('employer_id');

        return self::eligibleFor($event)
            ->reject(fn($employer) => $alreadyIn->contains($employer->employer_nsrp_registrations_id))
            ->when($overseas !== null, fn($list) => $list->filter(
                fn($employer) => (bool) $employer->is_overseas === $overseas
            ))
            ->sortBy(fn($employer) => mb_strtolower((string) $employer->company_name))
            ->values();
    }

    /**
     * Tell the jobseekers this fair is happening. Returns how many were told.
     *
     * PESO, 2026-08-26: the fair becomes real to a jobseeker the moment there
     * is something on it to come for. That moment is the Job Fair desk posting
     * the first vacancy onto the fair, so the announcement goes out there.
     *
     * Once per fair, and only once. Posting ten vacancies is one decision to
     * hold a fair, not ten — jobseekers_invited_at is what stops the tenth
     * posting from sending a tenth copy of the same notice. The vacancies
     * themselves announce separately when they go live at T-minus 5.
     *
     * Who is told: the jobseeker whose classification the fair can serve. A
     * fair that takes overseas agencies only is nothing a Local jobseeker can
     * act on, and telling them is noise they cannot use. "Both" is always told.
     */
    public static function inviteJobseekers(JobFairEvent $event): int
    {
        if ($event->jobseekers_invited_at !== null) {
            return 0;
        }

        $wantedTypes = collect(['both'])
            ->when($event->catersTo(false), fn($t) => $t->push('local'))
            ->when($event->catersTo(true),  fn($t) => $t->push('overseas'))
            ->all();

        $registrationIds = \App\Models\JobseekerRegistration::whereHas(
                'user', fn($q) => $q->where('status', 'approved')
            )
            ->whereHas('nsrp', fn($q) => $q->whereIn('type', $wantedTypes))
            ->pluck('jobseeker_registrations_id');

        // Gitiman-an bisan walay nakadawat: ang fair gi-anunsyo na, ug ang
        // sunod nga posting dili na angay mosulay pag-usab. Kung wala pay
        // jobseeker karon, ang T-minus 5 nga pag-abli mao nay mo-abot kanila.
        $event->forceFill(['jobseekers_invited_at' => now()])->save();

        if ($registrationIds->isEmpty()) {
            return 0;
        }

        Announcement::sendToJobseekers([
            'type'           => 'job_fair_announced',
            'title'          => 'Job Fair Coming 🎪',
            'message'        => 'PESO is holding ' . $event->title . ' on '
                                . $event->event_date->format('M d, Y')
                                . ($event->event_time ? ' at ' . \Carbon\Carbon::parse($event->event_time)->format('g:i A') : '')
                                . ', ' . $event->venue . '. Employers are being lined up now — '
                                . 'open PESO Events to see the fair and join it.',
            'reference_type' => 'job_fair',
            'reference_id'   => $event->job_fair_events_id,
        ], $registrationIds);

        return $registrationIds->count();
    }

    // ── I-apil ang mga employer sa event ug pahibaloa sila. Mo-return sa
    // ── gidaghanon nga tinuod nga na-apil.
    // ──
    // ── Ang participant na nga daan gilaktawan, ug usa ra ka announcement ang
    // ── ipadala — para sa mga bag-o ra. Ang pagpindot pag-usab, o ang pag-save
    // ── pag-usab sa edit form, dili maghatag ug doble nga invitation. ──
    public static function invite(
        JobFairEvent $event,
        Collection $employers,
        ?int $invitedByStaffId = null,
        ?string $permissionNote = null
    ): int {
        if ($employers->isEmpty()) {
            return 0;
        }

        $alreadyIn = JobFairParticipant::where('job_fair_id', $event->job_fair_events_id)
            ->pluck('employer_id');

        $new = $employers
            ->reject(fn($employer) => $alreadyIn->contains($employer->employer_nsrp_registrations_id))
            ->unique('employer_nsrp_registrations_id')
            ->values();

        if ($new->isEmpty()) {
            return 0;
        }

        foreach ($new as $employer) {
            JobFairParticipant::create([
                'job_fair_id'         => $event->job_fair_events_id,
                'employer_id'         => $employer->employer_nsrp_registrations_id,
                'confirmation_status' => 'pending',
                // Blangko kung ang sistema mismo ang nag-invite sumala sa
                // lagda sa event. Napuno kung naay tawo nga nagpili.
                'invited_by'          => $invitedByStaffId,
                'permission_note'     => $permissionNote,
                // Ang orasan magsugod dinhi, dili sa created_at: ang employer
                // nga gidugang sa staff human sa unang hugpong naay kaugalingon
                // nga semana, ug ang created_at dili kana masulti kung ma-invite
                // siya pag-usab.
                'invited_at'          => now(),
            ]);
        }

        $deadline = now()->addDays((int) config('peso.jobfair.confirm_window_days'));

        Announcement::sendToEmployers([
            'type'           => 'job_fair_invitation',
            'title'          => 'Job Fair Invitation 🎉',
            'message'        => 'You are invited to join ' . $event->title . ' on '
                                . $event->event_date->format('M d, Y') . ' at ' . $event->venue
                                . '. Please confirm by ' . $deadline->format('M d, Y')
                                . ' — after that the office invites other employers.',
            'reference_type' => 'job_fair',
            'reference_id'   => $event->job_fair_events_id,
        ], $new->pluck('employer_nsrp_registrations_id'));

        return $new->count();
    }

    // ── Usa ka employer, tanan nga event nga naay lugar para niya. Gigamit sa
    // ── pag-approve sa requirements. ──
    public static function inviteToOpenEvents(EmployerNsrpRegistration $employer): int
    {
        $invited = 0;

        foreach (self::eventsOpenTo($employer) as $event) {
            $invited += self::invite($event, collect([$employer]));
        }

        return $invited;
    }
}
