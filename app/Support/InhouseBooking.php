<?php

namespace App\Support;

use App\Models\InhouseSchedule;
use App\Models\Job;
use Carbon\Carbon;

// ── Kung mahimo bang i-book ang usa ka adlaw sa PESO Office para sa in-house
// ── nga interview, ug ngano dili.
// ──
// ── Duha ka lugar ang nangutana niini: ang employer gikan sa iyang dashboard
// ── (CompanyWebController::requestJob) ug ang staff gikan sa walk-in nga
// ── counter (StaffWebController::storeWalkinEmployer). Kung magkalahi sila ug
// ── tubag, ang usa ka adlaw mahimong doble ang book — usa gikan sa online, usa
// ── gikan sa counter, ug walay makakita niini hangtod sa maong buntag. ──
class InhouseBooking
{
    /** Pila ka kompanya ang kasyahan sa PESO Office kada adlaw. */
    public static function dailyLimit(): int
    {
        return (int) config('peso.schedule.inhouse_daily_companies');
    }

    // ── Ang adlaw nga gikuha na sa usa ka schedule sa opisina mismo (holiday,
    // ── job fair, tigum). Wala ni labot sa limit sa kompanya. ──
    public static function blockedWindowError(?string $start, ?string $end): ?string
    {
        if (!$start) return null;

        $end     = $end ?: $start;
        $blocked = OfficeCalendar::blockedWithin($start, $end);

        if (empty($blocked)) return null;

        if ($start === $end) {
            return OfficeCalendar::reason($start);
        }

        $days = collect($blocked)
            ->map(fn($iso) => Carbon::parse($iso)->format('M d, Y'))
            ->implode(', ');

        return count($blocked) === 1
            ? $days . ' is taken by a PESO office schedule. '
              . 'Please pick a range that does not include it.'
            : $days . ' are taken by PESO office schedules. '
              . 'Please pick a range that does not include them.';
    }

    // ── Ang laray nga nag-okupar sa usa ka adlaw.
    // ──
    // ── Ang blangko nga `preferred_date_end` kay USA ra ka adlaw — mao nay
    // ── porma nga gipadala sa porma sa employer ug sa walk-in nga counter
    // ── alang sa usa ka adlaw nga pangayo (PESO, 2026-08-24). Kaniadto kini
    // ── gibasa nga walay katapusan, mao nga ang usa ka booking sa Aug 13
    // ── nag-okupar sa tanang adlaw human niini ug ang tibuok kalendaryo
    // ── nagpakita nga puno. ──
    private static function coversDay(string $date): \Closure
    {
        return function ($query) use ($date) {
            $query->whereDate('preferred_date', '<=', $date)
                  ->where(function ($q) use ($date) {
                      $q->whereDate('preferred_date_end', '>=', $date)
                        ->orWhere(function ($q2) use ($date) {
                            $q2->whereNull('preferred_date_end')
                               ->whereDate('preferred_date', '=', $date);
                        });
                  });
        };
    }

    // ── Pila ka KOMPANYA ang naka-book na sa PESO Office niining adlawa.
    // ──
    // ── Ang schedule-only nga request ug ang in-house nga posting parehas nga
    // ── nag-okupar sa lawak, mao nga giihap silang duha — apan ang usa ka
    // ── employer nga naay duha ka rekord usa ra gihapon ka kompanya nga
    // ── mo-abot, mao nga giusa sila (PESO, 2026-08-24). ──
    public static function companiesOn(string $date): int
    {
        $covers = self::coversDay($date);

        $scheduleEmployers = InhouseSchedule::where('venue_type', 'peso_office')
            ->whereIn('status', ['pending', 'accepted'])
            ->where($covers)
            ->pluck('employer_id');

        $jobEmployers = Job::where('schedule_type', 'inhouse')
            ->where('venue_type', 'peso_office')
            ->whereIn('posting_status', ['pending', 'approved'])
            ->where($covers)
            ->pluck('company_id');

        return $scheduleEmployers->merge($jobEmployers)->unique()->count();
    }

    public static function isFull(string $date): bool
    {
        return self::companiesOn($date) >= self::dailyLimit();
    }

    // ── WALA PA MAHUMAN NGA DESISYON (2026-08-14): gipangutana pa sa project
    // ── manager kung unsaon ni sa usa ka range. Kung puno na ang Aug 13–17,
    // ── sirado ba ang tibuok window para sa uban, o igo ra nga naa'y usa ka
    // ── adlaw nga libre?
    // ──
    // ── Samtang naghulat, ang labing luwas nga pagbasa ang gigamit: kung bisan
    // ── usa ka adlaw sulod sa range puno na, balibaran ang range. Walay
    // ── overbooking, ug ang nangayo masultihan kung asang adlawa puno.
    // ──
    // ── Ang ubang duha ka pagbasa kung mausab ang desisyon:
    // ──   (b) dawaton kung naa'y bisan usa ka adlaw nga libre;
    // ──   (c) ayaw pagtsek dinhi — sa pag-confirm sa staff na lang.
    // ── Dinhi ra ni nga method mausab; walay lain nga naghisgot sa limit. ──
    public static function capacityError(?string $start, ?string $end): ?string
    {
        if (!$start) return null;

        $limit  = self::dailyLimit();
        $cursor = Carbon::parse($start);
        $last   = Carbon::parse($end ?: $start);

        while ($cursor->lte($last)) {
            $date = $cursor->toDateString();

            if (self::companiesOn($date) >= $limit) {
                // Ang limit kay sa PESO Office ra — lawak man ni, ug pila ra
                // ang kasyahan. Ang laing venue kay ilaha nang kaugalingon,
                // mao nga wala'y limit didto. Kadaghanan gusto sa maong petsa,
                // dili sa maong lawak, mao nga ang venue ang unang gitanyag
                // nga solusyon.
                return Carbon::parse($date)->format('M d, Y')
                     . ' is fully booked at PESO Office — ' . $limit . ' of ' . $limit . ' companies. '
                     . 'To keep these dates, set the venue to Other Venue and give your own address; '
                     . 'there is no limit outside the PESO Office. Otherwise pick another range.';
            }

            $cursor->addDay();
        }

        return null;
    }

    // ── Ang puno nga adlaw sa PESO Office sulod sa sunod nga unom ka bulan,
    // ── para i-disable sa calendar picker.
    // ──
    // ── Duha ka butang ang gibantayan dinhi:
    // ──
    // ── 1. Ang usa ka booking kay range: mo-okupar siya sa KADA adlaw sulod
    // ──    niini, dili sa sinugdanan ra. Kung wala pa mabuklad, ang Aug 24–28
    // ──    mahimong Aug 24 ra, ug ang sunod nga employer makasulod sa 25–28
    // ──    nga puno na diay.
    // ──
    // ── 2. Ang range nga nagsugod na sa milabay nga adlaw naa pa gihapon.
    // ──    Kung ang gipangita mao ra ang nagsugod sulod sa window, ang Aug
    // ──    20–30 nga booking mawala sa kalendaryo karong Aug 24.
    // ──
    // ── Ang pag-ihap kada adlaw parehas gyud sa companiesOn(), aron ang
    // ── kalendaryo ug ang pagsusi sa pag-submit dili gyud magkalahi: ang adlaw
    // ── nga mapili unta sa tawo apan balibaran ra sa server kay sayop nga
    // ── kalendaryo, dili gyud siya sayop nga porma. ──
    public static function bookedDates(): array
    {
        $windowStart = now()->startOfDay();
        $windowEnd   = now()->addMonths(6)->endOfDay();

        $overlaps = function ($query) use ($windowStart, $windowEnd) {
            $query->whereDate('preferred_date', '<=', $windowEnd)
                  ->where(function ($q) use ($windowStart) {
                      $q->whereDate('preferred_date_end', '>=', $windowStart)
                        ->orWhere(function ($q2) use ($windowStart) {
                            $q2->whereNull('preferred_date_end')
                               ->whereDate('preferred_date', '>=', $windowStart);
                        });
                  });
        };

        $schedules = InhouseSchedule::where('venue_type', 'peso_office')
            ->whereIn('status', ['pending', 'accepted'])
            ->where($overlaps)
            ->get(['preferred_date', 'preferred_date_end', 'employer_id']);

        $jobs = Job::where('schedule_type', 'inhouse')
            ->where('venue_type', 'peso_office')
            ->whereIn('posting_status', ['pending', 'approved'])
            ->where($overlaps)
            ->get(['preferred_date', 'preferred_date_end', 'company_id']);

        // Giusa ang duha ka gigikanan, parehas sa companiesOn().
        $byDay = [];

        $spread = function ($rows, string $idColumn, string $bucket) use (&$byDay, $windowStart, $windowEnd) {
            foreach ($rows as $row) {
                // copy(): ang Carbon::max() mobalik sa mismong instance, dili
                // ug kopya — kung dili kopyahon, ang addDay() sa ubos mo-usab sa
                // $windowStart mismo ug mabuang ang sunod nga laray.
                $cursor = Carbon::parse($row->preferred_date)->max($windowStart)->copy();
                $last   = $row->preferred_date_end
                    ? Carbon::parse($row->preferred_date_end)
                    : Carbon::parse($row->preferred_date);

                if ($last->gt($windowEnd)) $last = $windowEnd->copy();

                while ($cursor->lte($last)) {
                    $byDay[$cursor->toDateString()][$bucket][] = $row->{$idColumn};
                    $cursor->addDay();
                }
            }
        };

        $spread($schedules, 'employer_id', 'schedule');
        $spread($jobs, 'company_id', 'job');

        $limit = self::dailyLimit();

        return collect($byDay)
            ->filter(function ($buckets) use ($limit) {
                $employers = array_merge($buckets['schedule'] ?? [], $buckets['job'] ?? []);
                return count(array_unique($employers)) >= $limit;
            })
            ->keys()
            ->sort()
            ->values()
            ->all();
    }

    // ── Ang tubag sa live nga pagsusi samtang nagpili pa ang tawo ug petsa.
    // ── Parehas ang porma bisan ang employer o ang staff ang nangutana, mao
    // ── nga usa ra ka JavaScript ang naghubad niini. ──
    public static function availability(?string $start, ?string $end, string $venueType = 'peso_office'): array
    {
        if (!$start) {
            return ['occupied' => false, 'count' => 0];
        }

        $end = $end ?: $start;

        // Ang PESO nga miting mopugong bisan asa nga venue — ang staff ang wala
        // ang libre, dili ang lawak. Ug bisan usa ka adlaw igo na sa pagbabag sa
        // tibuok range: mo-reserba man ang nangayo sa kada adlaw sulod niini.
        $officeBlocked = OfficeCalendar::blockedWithin($start, $end);
        $officeNote    = null;

        if ($officeBlocked) {
            $days = collect($officeBlocked)
                ->map(fn($iso) => Carbon::parse($iso)->format('M d'))
                ->implode(', ');

            $officeNote = count($officeBlocked) === 1
                ? $days . ' is taken by a PESO office schedule.'
                : $days . ' are taken by PESO office schedules.';
        }

        if ($venueType !== 'peso_office') {
            return [
                'occupied'    => false,
                'count'       => 0,
                'office_note' => $officeNote,
                'office_full' => count($officeBlocked) > 0,
            ];
        }

        // Ang kataas-taasang gamit nga adlaw sulod sa window — kana ang mosulti
        // kung mahuman ba ang request o dili.
        $limit  = self::dailyLimit();
        $cursor = Carbon::parse($start);
        $last   = Carbon::parse($end);
        $peak   = 0;
        $full   = [];

        while ($cursor->lte($last)) {
            $total = self::companiesOn($cursor->toDateString());
            $peak  = max($peak, $total);
            if ($total >= $limit) $full[] = $cursor->format('M d');
            $cursor->addDay();
        }

        return [
            'occupied'    => count($full) > 0,
            'count'       => $peak,
            'limit'       => $limit,
            'full_days'   => $full,
            'office_note' => $officeNote,
            'office_full' => count($officeBlocked) > 0,
        ];
    }
}
