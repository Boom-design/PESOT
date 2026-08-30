<?php

namespace App\Support;

use App\Models\Job;

/**
 * The Company Interview half of the Job Vacancy desk's report.
 *
 * That desk owns two things — the vacancies it solicits and the company
 * interviews employers run themselves — and the report only ever answered for
 * the first. An interview that happened, and whether the applicants were taken
 * on or turned away, was not on any report at all.
 *
 * One definition here so the staff's own page and the Admin's copy of it can
 * never show a different list for the same month.
 */
class CompanyInterviewReport
{
    /**
     * One desk at a time: local postings belong to the Job Vacancy desk,
     * overseas ones to the SRA.
     *
     * Filtered on the interview date rather than on when the row was last
     * touched. The question is "what interviews happened in August", and
     * `updated_at` moves whenever anybody edits the posting.
     *
     * `$overseas` picks the desk: false is the Job Vacancy desk's own list,
     * true is the SRA's.
     */
    public static function query(?string $year, ?string $mon, ?string $search = null, bool $overseas = false)
    {
        return Job::with('company')
            ->where('schedule_type', 'company_interview')
            ->whereHas('company', fn($q) => $q->where('is_overseas', $overseas))
            // The month is optional: the Job Vacancy report asks one month at a
            // time, the SRA report lists the whole thing and searches instead.
            ->when($year && $mon, fn($q) => $q
                ->whereYear('preferred_date', $year)
                ->whereMonth('preferred_date', $mon))
            ->when($search, fn($q) => $q->where(function ($w) use ($search) {
                $w->where('title', 'like', "%{$search}%")
                  ->orWhereHas('company', fn($c) => $c->where('company_name', 'like', "%{$search}%"));
            }))
            // No applicant tallies here on purpose. The report answers "which
            // company interviews were held, and was each one approved or
            // declined" — one state per interview. What became of the people
            // who attended is the placement report's question, not this one.
            ->orderBy('preferred_date')
            ->orderBy('title');
    }

    /** The page the report shows — five rows, the same as every other tab. */
    public static function paginate(?string $year, ?string $mon, ?string $search = null, bool $overseas = false)
    {
        return self::query($year, $mon, $search, $overseas)->paginate(5, ['*'], 'page')->withQueryString();
    }
}
