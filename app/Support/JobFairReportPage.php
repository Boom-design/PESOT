<?php

namespace App\Support;

/**
 * Everything the Job Fair Reports page needs, for whoever opens it.
 *
 * Built in one place because three screens render the same Blade: the Job Fair
 * desk's Reports, the SRA's overseas view of it, and the admin's copy. The
 * admin's copy used to build its own variables and fell behind — each tab the
 * desk gained a variable for came out on the admin screen as "Undefined
 * variable". Now the admin gets exactly what the desk gets.
 */
class JobFairReportPage
{
    /**
     * @param string $staffRole        whose page this is ('job_fair', 'sra')
     * @param bool   $isSraJobFairView the SRA's overseas-only view of the reports
     * @param string $reportView       the SRA's view selector ('staff' or 'jobfair')
     */
    public static function data(string $staffRole, bool $isSraJobFairView, string $reportView = 'staff'): array
    {
        // Ang Attendance ang default. Kini ang unang gipangita sa desk
        // human sa fair — kinsa ang nitunga — ug ang Post Job Fair Summary
        // usa ka tapos nga dokumento, dili ang unang pangutana.
        $tab      = request('tab', 'attendance');
        if ($isSraJobFairView && $tab === 'placement') $tab = 'attendance';
        $eventId  = request('event_id');
        $allEvents = \App\Models\JobFairEvent::orderByDesc('event_date')->get();
        $event     = $eventId ? \App\Models\JobFairEvent::find($eventId) : null;

        // ── Ang matag listahan sa ubos naa na sa JobFairReport.
        // ──
        // ── Dinhi sila kaniadto, ug ang download nga gipangayo sa Job Fair
        // ── staff kinahanglan mokopya unta sa matag query. Karon usa ra
        // ── ka lugar ang naghubad: ang page mo-paginate, ang CSV mokuha sa
        // ── tanan, ug dili gyud sila magkalahi ug ihap. ──
        $eventJobIds = \App\Support\JobFairReport::eventJobIds($eventId ? (int) $eventId : null);

        // ── TAB 1: ATTENDANCE ──
        // ──
        // ── Usa ra ka attendance nga tab sa tibuok sistema. Kaniadto duha:
        // ── usa sa Job Fair Events (ang trabahoan, uban ang Mark Attended)
        // ── ug usa dinhi (ang rekord, ang miabot ra) — ug managlahi ug ihap
        // ── ang duha, mao nga nagduha-duha ang staff kung asa ang tinuod.
        // ──
        // ── Ang $attendanceState ang nagpuli sa duha ka tab: "joined" mao
        // ── ang panahon sa fair, "attended" mao ang ipasa sa DOLE. Ang CSV
        // ── mosunod sa parehas nga pagpili. ──
        $attendanceFilter = request('attendance_filter', 'all');
        $attendanceState  = request('attendance_state', $isSraJobFairView ? 'attended' : 'joined');
        if (!array_key_exists($attendanceState, \App\Support\JobFairReport::STATES)) {
            $attendanceState = 'joined';
        }
        $attendanceSearch = request('attendance_search');
        // Who the employers decided on: those rows read as attended.
        $attendanceDecided = ($tab === 'attendance' && $eventId)
            ? \App\Support\JobFairReport::decidedJobseekerIds((int) $eventId)
            : [];
        $registrations = null;
        $totalRegistered = $totalAttended = 0;

        if ($tab === 'attendance' && $eventId) {
            if ($isSraJobFairView) $attendanceFilter = 'overseas';

            $registrations = \App\Support\JobFairReport::attendanceQuery(
                    (int) $eventId, $attendanceFilter, $attendanceState, $attendanceSearch
                )
                ->latest()->paginate(10)->withQueryString();

            $totals = \App\Support\JobFairReport::attendanceTotals((int) $eventId);
            $totalRegistered = $totals['registered'];
            $totalAttended   = $totals['attended'];
        }

        // ── TAB 2: LIST OF LOCAL/OVERSEAS COMPANIES FOR JOB FAIR ──
        $companiesLocal       = collect();
        $companiesOverseas    = collect();
        $companyVacancyTotals = ['local' => 0, 'overseas' => 0];

        if ($tab === 'companies' && $eventId) {
            $confirmed = \App\Support\JobFairReport::confirmedCompanies((int) $eventId);

            // ── Lima kada panid, ug lain ang page name sa matag listahan.
            // ──
            // ── Ang duha ka listahan gitapok kaniadto, tibuok. Sa fair nga
            // ── naay 300 ka lokal nga kompanya, ang overseas naa sa ubos sa
            // ── 300 ka laray — walay makakita niini gawas kung mo-scroll
            // ── siya sa tibuok. Ug kung usa ra ang page name, ang pagbalhin
            // ── sa usa ka listahan mobalhin pud sa lain. ──
            $paginate = function ($rows, string $pageName) {
                $page = \Illuminate\Pagination\Paginator::resolveCurrentPage($pageName);

                return (new \Illuminate\Pagination\LengthAwarePaginator(
                    $rows->forPage($page, 5)->values(),
                    $rows->count(),
                    5,
                    $page,
                    [
                        'path'     => \Illuminate\Pagination\Paginator::resolveCurrentPath(),
                        'pageName' => $pageName,
                    ]
                ))->withQueryString();
            };

            // Ang TOTAL sa papel kay sa tibuok listahan, dili sa lima ka
            // laray nga makita karon. Gikwenta sa dili pa ma-paginate.
            $companyVacancyTotals = [
                'local'    => (int) $confirmed->filter(fn($p) => !($p->employer->is_overseas ?? false))->sum('vacancies'),
                'overseas' => (int) $confirmed->filter(fn($p) => $p->employer->is_overseas ?? false)->sum('vacancies'),
            ];

            $companiesLocal = $paginate(
                $confirmed->filter(fn($p) => !($p->employer->is_overseas ?? false))->values(),
                'local_page'
            );
            $companiesOverseas = $paginate(
                $confirmed->filter(fn($p) => $p->employer->is_overseas ?? false)->values(),
                'overseas_page'
            );
        }

        // ── TAB 3: LIST OF FURTHER INTERVIEW (waiting status) ──
        $furtherInterview = null;
        if ($tab === 'further_interview' && $eventId) {
            $furtherInterview = \App\Support\JobFairReport::furtherInterviewQuery($eventJobIds, $isSraJobFairView)
                ->paginate(10)->withQueryString();
        }

        // ── TAB 4: HOTS — hired for a job brought to this event (dili na i-match ang eksaktong petsa, kay ang job mismo naka-scope na sa event via eventJobIds) ──
        $hots = null;
        if ($tab === 'hots' && $eventId && $event) {
            $hots = \App\Support\JobFairReport::hotsQuery($eventJobIds, $isSraJobFairView)
                ->paginate(10)->withQueryString();
        }

        // ── TAB 5: POST JOB FAIR SUMMARY REPORT ──
        $summaryParticipants = collect();
        $summaryTotals = ['vacancies' => 0, 'interviewed' => 0, 'male' => 0, 'female' => 0, 'qualified' => 0, 'hired' => 0];

        // Ang linya sa ubos sa papel: pila ka tawo ang niapil sa fair,
        // gibahin sa lokal ug overseas. Lahi ni sa kolum sa ibabaw — didto
        // ang aplikasyon ang gi-ihap, dinhi ang tawo.
        $summaryRegistrants = ['local' => 0, 'overseas' => 0];

        if ($tab === 'summary' && $eventId) {
            $summaryParticipants = \App\Support\JobFairReport::summaryRows((int) $eventId, $isSraJobFairView);
            $summaryTotals       = \App\Support\JobFairReport::summaryTotals($summaryParticipants);
            $summaryRegistrants  = \App\Support\JobFairReport::registrantTotals((int) $eventId);
        }

        // ── TAB 6: TOTAL COMPANIES WITH VACANCIES (per Industry Group) ──
        $industryLocal    = collect();
        $industryOverseas = collect();

        if ($tab === 'industry' && $eventId) {
            $industryTotals   = \App\Support\JobFairReport::industryTotals($eventJobIds);
            $industryLocal    = $industryTotals['local'];
            $industryOverseas = $industryTotals['overseas'];
        }

        // ── TAB: TOP EMPLOYERS — kinsa ang nagdala ug pinakadaghang bakante
        // ── niining maong fair. Iya na sa usa ka event, dili na kada bulan:
        // ── ang pangutana kay kinsa ang nagdala ug trabaho ngadto sa fair.
        // ──
        // ── PESO Job Fair staff, 2026-09-02: dili na siya nagsalig sa
        // ── pagpili ug event. Kung walay gipili, ang ranggo sa tanang fair
        // ── — mao kana ang "Top 10 Employers" nga gipangayo. Kung naay
        // ── gipili nga fair, ang ranggo niadto lang. ──
        // ── Duha ka lamesa ang naa sa papel, ug walay usa nila naghisgot
        // ── ug employer: ang gipangita nga TRABAHO, ug ang bahin sa matag
        // ── INDUSTRIYA. Ang ulohan mao ang run down: pila ka kompanya ug
        // ── pila ka bakante, gibahin sa lokal ug overseas. ──
        // Ang listahan sa bakante nga gipasa sa siyudad. Parehas nga
        // datos sa Participating Companies, lahi ang pangutana: dinhi ang
        // bakante ang gi-ihap, dili ang tawo nga hikapon.
        $vacancyList = collect();
        if ($tab === 'vacancy_list') {
            $vacancyList = \App\Support\JobFairReport::localVacancyList($event, $isSraJobFairView);
        }

        $topOccupations = collect();
        $industryShares = ['rows' => [], 'total' => 0, 'unclassified' => ['quantity' => 0, 'share' => 0]];
        $runDown        = null;

        if ($tab === 'top_employers') {
            $topOccupations = \App\Support\JobFairReport::topOccupations($event, $isSraJobFairView);
            $industryShares = \App\Support\JobFairReport::industryShares($event, $isSraJobFairView);
            $runDown        = \App\Support\JobFairReport::runDownTotals($event, $isSraJobFairView);
        }

        // ── TAB 7: COMPANY PLACEMENT REPORT (local only, hired AFTER event date) ──
        $placementReport = null;
        if ($tab === 'placement' && $eventId && $event) {
            $placementReport = \App\Support\JobFairReport::placementQuery($eventJobIds, $event)
                ->paginate(15)->withQueryString();
        }

        // ── TAB 9: ANG KAUGALINGON NGA REPORT SA STAFF ──
        // ── Gitipigan lang ug gipakita. Walay bisan usa sa mga numero sa
        // ── ibabaw nga nagbasa niini. ──
        // ── Ang tanan nga na-import, dili kadto ra sa usa ka fair.
        // ──
        // ── PESO Job Fair staff, 2026-09-02: kining tab wala na sa
        // ── dropdown sa event. Ang report nga gi-import iya gihapon sa
        // ── usa ka fair — gipakita ang ngalan sa fair sa matag laray —
        // ── apan ang listahan mao ang "unsa ang akong gi-upload", ug kana
        // ── matubag nga walay pagpili. ──
        $importedReports = collect();
        if ($tab === 'imported') {
            $importedReports = \App\Models\JobFairImportedReport::with(['uploader', 'jobFair'])
                ->when($eventId, fn($q) => $q->where('job_fair_id', $eventId))
                ->latest()
                ->get();
        }

        // ── Nahuman na nga posting — milabay ang deadline o napuno ang
        // ── slots. Buhi gihapon ang Job row, mao nga bukas ang full details. ──
        $archivedJobs = \App\Models\Job::with('company')
            ->inactive()
            ->withCount([
                'applications as hired_count' => fn($q) => $q->where('status', 'hired'),
            ])
            ->latest()
            ->paginate(5, ['*'], 'archived_page')
            ->withQueryString();

        return compact(
            'staffRole', 'tab', 'allEvents', 'event', 'eventId',
            'registrations', 'attendanceFilter', 'attendanceState', 'attendanceSearch', 'attendanceDecided',
            'totalRegistered', 'totalAttended',
            'companiesLocal', 'companiesOverseas', 'companyVacancyTotals',
            'furtherInterview', 'hots',
            'summaryParticipants', 'summaryTotals', 'summaryRegistrants',
            'industryLocal', 'industryOverseas',
            'placementReport', 'isSraJobFairView', 'reportView',
            'topOccupations', 'industryShares', 'runDown', 'vacancyList',
            'importedReports', 'archivedJobs'
        );
    }
}
