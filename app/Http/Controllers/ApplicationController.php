<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Job;
use App\Models\JobseekerRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApplicationController extends Controller
{
    // ── Helper ──
    private function authJobseeker()
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'jobseeker') return null;
        return $user;
    }

    // ── APPLY FOR JOB ──
    public function apply($jobId)
    {
        $jobseeker = $this->authJobseeker();
        if (!$jobseeker) return redirect()->route('login');

        $registration = JobseekerRegistration::where('user_id', $jobseeker->users_id)->first();
        if (!$registration) {
            return redirect()->route('jobseeker.nsrp')
                ->with('info', 'Please complete your NSRP Registration Form first before applying.');
        }

        // ── Job::active() ang basihan, dili ang status ra. Tulo ka rason nga
        // ── dili na dawaton ang aplikasyon, ug ang status ra nakakita sa usa:
        // ──   1. gisira ang posting
        // ──   2. milabay na ang deadline — ang row magpabilin nga 'open'
        // ──      hangtod modagan ang jobs:expire-monthly sa sunod nga buntag
        // ──   3. napuno na ang slots sa tibuok posting group — walay
        // ──      nagsira niini, ang scope ra ang nakahibalo
        // ── Ang 404 dili igo dinhi: ang jobseeker nag-klik ug buton nga iyang
        // ── nakita, ug angay siyang masultihan kung nganong wala na siya. ──
        $job = Job::find($jobId);

        if (!$job) {
            abort(404);
        }

        if (!Job::active()->where('job_qualifications_id', $jobId)->exists()) {
            $reason = match (true) {
                $job->is_expired => 'The deadline for this vacancy has passed.',
                $job->group_hired >= (int) $job->slots => 'All slots for this vacancy have been filled.',
                default => 'This vacancy is no longer accepting applications.',
            };

            return redirect()->route('jobseeker.jobs')->with('error', $reason);
        }

        // Check if already applied
        $existing = Application::where('jobseeker_id', $registration->jobseeker_registrations_id)
            ->where('job_id', $jobId)->first();

        if ($existing) {
            return redirect()->route('jobseeker.jobs.show', $jobId)
                ->with('error', 'You have already applied for this job.');
        }

        // Compute match percentage
        $breakdown = $this->evaluateMatch($jobseeker->users_id, $job);
        $matchPercentage = $breakdown['percentage'];

        $application = Application::create([
            'jobseeker_id'           => $registration->jobseeker_registrations_id,
            'job_id'                 => $jobId,
            'status'                 => 'pending',
            'match_percentage'       => $matchPercentage,
            'inhouse_participation'  => $job->schedule_type === 'inhouse' ? 'pending' : null,
            'company_interview_participation'   => $job->schedule_type === 'company_interview' ? 'pending' : null,
        ]);

        // ── Notify ang employer nga naay bag-ong applicant ──
        $applicantName = trim(($registration->first_name ?? '') . ' ' . ($registration->surname ?? ''));
        \App\Models\Announcement::sendToEmployers([
            'type'           => 'new_applicant',
            'title'          => 'New Job Applicant 📨',
            'message'        => ($applicantName ?: 'A jobseeker') . ' applied for "' . $job->title . '".',
            'reference_type' => 'job',
            'reference_id'   => $job->job_qualifications_id,
        ], $job->company_id);

        // ── In-house: prompt DAYON ra kung 5 days na lang o layo pa ang nabilin sa preferred_date; ──
        // ── kung layo pa (>5 days), mag-hulat sa scheduled reminder (5 days before) ──
        if ($job->schedule_type === 'inhouse') {
            if ($job->isInhousePromptDue()) {
                $application->update(['inhouse_participation_notified_at' => now()]);
                return redirect()->route('jobseeker.jobs.show', $jobId)
                    ->with('success', 'Application submitted successfully!')
                    ->with('show_inhouse_prompt', $application->job_matching_id);
            }
            return redirect()->route('jobseeker.jobs.show', $jobId)
                ->with('success', 'Application submitted successfully!');
        }

        // ── Company Interview: prompt dayon, walay 5-day rule — ipakita ang company name + address ──
        if ($job->schedule_type === 'company_interview') {
            return redirect()->route('jobseeker.jobs.show', $jobId)
                ->with('success', 'Application submitted successfully!')
                ->with('show_company_interview_prompt', $application->job_matching_id);
        }

        return redirect()->route('jobseeker.jobs.show', $jobId)
            ->with('success', 'Application submitted successfully!');
    }

    // ── STAFF: APPLY FOR JOB ON BEHALF OF A WALK-IN JOBSEEKER (walay account/email) ──
    public function applyByStaff(Request $request, $registrationId)
    {
        $staffUser = Auth::user();
        if (!$staffUser || $staffUser->role !== 'staff') return redirect()->route('login');

        $request->validate([
            'job_id' => 'required|exists:job_qualifications,job_qualifications_id',
        ]);

        $registration = JobseekerRegistration::findOrFail($registrationId);
        // Parehas nga lagda sa walk-in nga gi-apply sa staff: ang nalabyan ug
        // ang napuno nga bakante dili na dawaton.
        $job = Job::active()->where('job_qualifications_id', $request->job_id)->first();

        if (!$job) {
            return back()->with('error',
                'That vacancy is no longer open — its deadline has passed or every slot is filled.');
        }

        $existing = Application::where('jobseeker_id', $registration->jobseeker_registrations_id)
            ->where('job_id', $job->job_qualifications_id)->first();

        if ($existing) {
            return back()->with('error', 'This jobseeker has already applied for this job.');
        }

        $breakdown = $this->computeMatchBreakdownByRegistrationId($registration->jobseeker_registrations_id, $job);

        Application::create([
            'jobseeker_id'          => $registration->jobseeker_registrations_id,
            'job_id'                => $job->job_qualifications_id,
            'status'                => 'pending',
            'match_percentage'      => $breakdown['percentage'],
            // ── Walk-in jobseekers naa na mismo sa office pag-apply — auto-accepted dayon ang participation, dili na kinahanglan pa i-confirm (kay walay account/paagi sila ma-respond sa prompt) ──
            'inhouse_participation' => $job->schedule_type === 'inhouse' ? 'accepted' : null,
            'company_interview_participation'  => $job->schedule_type === 'company_interview' ? 'accepted' : null,
        ]);

        $applicantName = trim(($registration->first_name ?? '') . ' ' . ($registration->surname ?? ''));
        \App\Models\Announcement::sendToEmployers([
            'type'           => 'new_applicant',
            'title'          => 'New Job Applicant 📨',
            'message'        => ($applicantName ?: 'A jobseeker') . ' applied for "' . $job->title . '".',
            'reference_type' => 'job',
            'reference_id'   => $job->job_qualifications_id,
        ], $job->company_id);

        return back()->with('success', 'Application submitted for ' . ($applicantName ?: 'the jobseeker') . '!');
    }

    // ── RESPOND SA IN-HOUSE INTERVIEW PARTICIPATION PROMPT ──
    // ── Ang pag-decline dili permanente. Pilot testing 2026-08-13: mahimong
    // ── namali ra ug click ang jobseeker, o naay klase niadtong adlawa nga
    // ── nakansela. Ang rekord magpabilin aron makita sa employer kung pila ang
    // ── ni-decline — mao nay basihan sa opisina sa pag-plano sa venue — apan
    // ── makabalik gihapon ang tawo samtang buhi pa ang posting. ──
    private function guardParticipationChange(Application $application, string $response)
    {
        if ($response !== 'accepted') {
            return null;
        }

        $job = Job::withGroupHiredCount()->find($application->job_id);

        if (!$job) {
            return back()->with('error', 'This posting is no longer available.');
        }

        // Kaugalingon nga mensahe: ang lifecycle_block_reason gisulat para sa
        // employer nga mag-edit, dili para sa jobseeker nga mo-apil.
        $reason = match ($job->lifecycle_status) {
            'active', 'waiting' => null,
            'filled'  => 'All ' . $job->slots . ' slot(s) for this position have been filled.',
            'expired' => 'This posting closed on ' . optional($job->deadline)->format('M d, Y') . '.',
            default   => 'The employer has closed this posting.',
        };

        return $reason ? back()->with('error', $reason) : null;
    }

    public function respondInhouseParticipation(Request $request, $id)
    {
        $jobseeker = $this->authJobseeker();
        if (!$jobseeker) return redirect()->route('login');

        $request->validate([
            'response' => 'required|in:accepted,declined',
        ]);

        $registration = JobseekerRegistration::where('user_id', $jobseeker->users_id)->first();

        $application = Application::where('job_matching_id', $id)
            ->where('jobseeker_id', $registration->jobseeker_registrations_id ?? 0)
            ->firstOrFail();

        if ($guard = $this->guardParticipationChange($application, $request->response)) {
            return $guard;
        }

        $rejoined = $application->inhouse_participation === 'declined' && $request->response === 'accepted';

        $application->update(['inhouse_participation' => $request->response]);

        $msg = $request->response === 'accepted'
            ? ($rejoined
                ? 'You are back in for the in-house interview. Check your Schedules page for details.'
                : 'You have confirmed your participation in the in-house interview! Check your Schedules page for details.')
            : 'You have declined to participate in the in-house interview. You can still change your mind while the posting is open.';

        return back()->with('success', $msg);
    }

    // ── RESPOND SA COMPANY INTERVIEW PARTICIPATION PROMPT ──
    public function respondCompanyInterviewParticipation(Request $request, $id)
    {
        $jobseeker = $this->authJobseeker();
        if (!$jobseeker) return redirect()->route('login');

        $request->validate([
            'response' => 'required|in:accepted,declined',
        ]);

        $registration = JobseekerRegistration::where('user_id', $jobseeker->users_id)->first();

        $application = Application::where('job_matching_id', $id)
            ->where('jobseeker_id', $registration->jobseeker_registrations_id ?? 0)
            ->firstOrFail();

        // ── Kaniadto gi-DELETE ang row dinhi. Duha ka sayop kadto: mawala ang
        // ── rekord nga naay ni-decline (nga gikinahanglan sa employer sa
        // ── pag-plano), ug lahi kaayo siya sa in-house nga wala mo-delete.
        // ── Karon parehas na sila: magpabilin ang rekord, ug makabalik ang
        // ── jobseeker samtang buhi pa ang posting. ──
        if ($guard = $this->guardParticipationChange($application, $request->response)) {
            return $guard;
        }

        $rejoined = $application->company_interview_participation === 'declined' && $request->response === 'accepted';

        $application->update(['company_interview_participation' => $request->response]);

        if ($request->response === 'declined') {
            return back()->with('success',
                'You have declined this job posting. You can still change your mind while it is open.');
        }

        return back()->with('success', $rejoined
            ? 'You are back in for this job posting!'
            : 'You have confirmed your interest in this job posting!');
    }

    // ── PUBLIC wrapper — percentage ra (gigamit sa mga listing/table nga wala kinahanglan og breakdown) ──
    public function computeMatchPublic($jobseekerId, Job $job): float
    {
        return $this->evaluateMatch($jobseekerId, $job)['percentage'];
    }

    // ── PUBLIC wrapper — kompleto nga breakdown (gigamit sa Job Detail page) ──
    public function computeMatchBreakdownPublic($jobseekerId, Job $job): array
    {
        return $this->evaluateMatch($jobseekerId, $job);
    }

    // ── COMPUTE MATCH — nag-build og detalyado nga breakdown samtang gina-score ──
    private function evaluateMatch($jobseekerId, Job $job): array
    {
        $registration = JobseekerRegistration::with('nsrp.workExperiences', 'nsrp.certifications')
            ->where('user_id', $jobseekerId)->first();

        if (!$registration) {
            return ['percentage' => 0, 'criteria' => []];
        }

        return $this->evaluateMatchForRegistration($registration, $job);
    }

    // ── PUBLIC wrapper — para sa walk-in jobseekers (walay user_id), gamit registration id diretso ──
    public function computeMatchBreakdownByRegistrationId($registrationId, Job $job): array
    {
        $registration = JobseekerRegistration::with('nsrp.workExperiences', 'nsrp.certifications')->find($registrationId);
        if (!$registration) {
            return ['percentage' => 0, 'criteria' => []];
        }
        return $this->evaluateMatchForRegistration($registration, $job);
    }

    private function evaluateMatchForRegistration(JobseekerRegistration $registration, Job $job): array
    {
        if (!$registration->nsrp) {
            return ['percentage' => 0, 'criteria' => []];
        }

        $nsrp = $registration->nsrp;

        $score = 0;
        $total = 0;
        $criteria = [];

        $addCriterion = function ($label, $weight, $matched, $note = null) use (&$score, &$total, &$criteria) {
            $total += $weight;
            if ($matched) $score += $weight;
            $criteria[] = [
                'label'   => $label,
                'matched' => $matched,
                'note'    => $note,
            ];
        };

        // ── Preferred Occupation — importante nga criterion, dako ang weight ──
        $preferredOccupations = $nsrp->preferred_occupations ?? [];
        if (!empty($preferredOccupations)) {
            $matched = false;
            foreach ($preferredOccupations as $occ) {
                if (strtolower(trim($occ)) === strtolower(trim($job->title))) {
                    $matched = true;
                    break;
                }
            }
            $addCriterion('Preferred Occupation: ' . $job->title, 25, $matched,
                $matched ? null : 'Not listed in your preferred occupations');
        }

        // ── Sex Preference ──
        if ($job->sex_preference && $job->sex_preference !== 'Any') {
            $matched = strtolower($registration->sex ?? '') === strtolower($job->sex_preference);
            $addCriterion('Sex: ' . $job->sex_preference, 10, $matched,
                $matched ? null : 'Your record: ' . ($registration->sex ?? 'N/A'));
        }

        // ── Civil Status ──
        if ($job->civil_status && $job->civil_status !== 'Any') {
            $matched = strtolower($registration->civil_status ?? '') === strtolower($job->civil_status);
            $addCriterion('Civil Status: ' . $job->civil_status, 8, $matched,
                $matched ? null : 'Your record: ' . ($registration->civil_status ?? 'N/A'));
        }

        // ── Religion ──
        if ($job->religion && strtolower(trim($job->religion)) !== 'any') {
            $matched = strtolower(trim($registration->religion ?? '')) === strtolower(trim($job->religion));
            $addCriterion('Religion: ' . $job->religion, 7, $matched,
                $matched ? null : 'Your record: ' . ($registration->religion ?: 'N/A'));
        }

        // ── Educational Level ──
        if ($job->education_required && $job->education_required !== 'Any') {
            $eduStatus = $this->getEducationLevelStatus($nsrp->education ?? []);
            $eduMap = [
                'Elementary'         => 1,
                'High School'        => 2,
                'Senior High'        => 3,
                'Tertiary / College' => 4,
                'Graduate Studies'   => 5,
            ];
            $requiredRank = $eduMap[$job->education_required] ?? 0;
            $weight = 15;
            if ($eduStatus['completed'] >= $requiredRank) {
                $addCriterion('Education: ' . $job->education_required, $weight, true);
            } elseif ($eduStatus['attempted'] >= $requiredRank) {
                $total += $weight;
                $score += $weight * 0.5;
                $criteria[] = [
                    'label'   => 'Education: ' . $job->education_required,
                    'matched' => 'partial',
                    'note'    => 'You reached this level but did not complete it (undergrad)',
                ];
            } else {
                $addCriterion('Education: ' . $job->education_required, $weight, false, 'You have not reached this level yet');
            }
        }

        // ── Course / Major ──
        if ($job->course_major) {
            $education = is_array($nsrp->education) ? $nsrp->education : (json_decode($nsrp->education ?? '{}', true) ?: []);
            $courses = [];
            foreach (['Tertiary / College', 'Graduate Studies/Post-graduate/Masters'] as $lvl) {
                if (!empty($education[$lvl]['course']))       $courses[] = $education[$lvl]['course'];
                if (!empty($education[$lvl]['course_other'])) $courses[] = $education[$lvl]['course_other'];
            }
            $matched = $this->courseMatches($job->course_major, $courses);
            $addCriterion('Course/Major: ' . $job->course_major, 8, $matched,
                $matched ? null : (empty($courses) ? 'No course on record' : 'Your course: ' . implode(', ', $courses)));
        }

        // ── Work Experience (months) ──
        if ($job->experience_months) {
            $totalMonths = $this->computeTotalWorkMonths($nsrp->workExperiences);
            $required = (int) $job->experience_months;
            $weight = 15;
            if ($required > 0) {
                $ratio = min($totalMonths / $required, 1);
                $total += $weight;
                $score += $weight * $ratio;
                $criteria[] = [
                    'label'   => 'Work Experience: ' . $required . ' month/s required',
                    'matched' => $ratio >= 1 ? true : ($ratio > 0 ? 'partial' : false),
                    'note'    => 'You have ' . $totalMonths . ' month/s of recorded experience',
                ];
            }
        }

        // ── Nature of Work vs jobseeker's work experience status history ──
        if ($job->type) {
            $typeMap = [
                'permanent'   => 'Permanent',
                'contractual' => 'Contractual',
                'part_time'   => 'Part-time',
            ];
            if (isset($typeMap[$job->type])) {
                $target = $typeMap[$job->type];
                $has = $nsrp->workExperiences->contains(function ($exp) use ($target) {
                    return strtolower($exp->employment_status ?? '') === strtolower($target);
                });
                $addCriterion('Nature of Work: ' . $target, 8, $has,
                    $has ? null : 'No recorded work experience with this employment status');
            }
        }

        // ── License ──
        if ($job->license) {
            $names = $nsrp->certifications->where('category', 'license')->pluck('name')->toArray();
            $matched = $this->textListMatches($job->license, $names);
            $addCriterion('License: ' . $job->license, 5, $matched,
                $matched ? null : 'No matching license on record');
        }

        // ── Eligibility ──
        if ($job->eligibility) {
            $names = $nsrp->certifications->where('category', 'eligibility')->pluck('name')->toArray();
            $matched = $this->textListMatches($job->eligibility, $names);
            $addCriterion('Eligibility: ' . $job->eligibility, 5, $matched,
                $matched ? null : 'No matching eligibility on record');
        }

        // ── Certification (Training OR Eligibility/License) ──
        if ($job->certification) {
            $trainings = is_array($nsrp->trainings) ? $nsrp->trainings : (json_decode($nsrp->trainings ?? '[]', true) ?: []);
            $trainingNames = array_filter(array_map(fn($t) => $t['course'] ?? '', $trainings));
            $certNames = $nsrp->certifications->pluck('name')->toArray();
            $pool = array_merge($trainingNames, $certNames);
            $matched = $this->textListMatches($job->certification, $pool);
            $addCriterion('Certification: ' . $job->certification, 9, $matched,
                $matched ? null : 'No matching training or certification on record');
        }

        // ── Language / Dialect ──
        if ($job->language) {
            $langProf = is_array($nsrp->language_proficiency) ? $nsrp->language_proficiency : (json_decode($nsrp->language_proficiency ?? '{}', true) ?: []);
            $spokenLangs = [];
            foreach ($langProf as $lang => $skills) {
                if ($lang === 'other') continue;
                if (is_array($skills) && array_filter($skills)) {
                    $spokenLangs[] = $lang;
                }
            }
            $otherLangs = is_array($nsrp->other_language) ? $nsrp->other_language : (json_decode($nsrp->other_language ?? '[]', true) ?: []);
            $spokenLangs = array_merge($spokenLangs, $otherLangs);
            $matched = $this->textListMatches($job->language, $spokenLangs);
            $addCriterion('Language: ' . $job->language, 5, $matched,
                $matched ? null : 'Not listed in your language proficiency');
        }

        // ── Skills — keyword scan sa "Other Qualifications" ──
        $skillKeywords = ['Auto Mechanic','Beautician','Carpentry Work','Computer Literate','Domestic Chores','Driver','Electrician','Embroidery','Gardening','Masonry','Painter/Artist','Painting Jobs','Photography','Plumbing','Sewing Dresses','Stenography','Tailoring'];
        $mentionedSkills = array_filter($skillKeywords, function ($skill) use ($job) {
            return $job->other_qualifications && stripos($job->other_qualifications, $skill) !== false;
        });
        if (!empty($mentionedSkills)) {
            $jobseekerSkills = is_array($nsrp->other_skills) ? $nsrp->other_skills : (json_decode($nsrp->other_skills ?? '[]', true) ?: []);
            $matched = count(array_intersect(
                array_map('strtolower', $mentionedSkills),
                array_map('strtolower', $jobseekerSkills)
            ));
            $weight = 5;
            $total += $weight;
            $ratio = $matched / count($mentionedSkills);
            $score += $ratio * $weight;
            $criteria[] = [
                'label'   => 'Skills: ' . implode(', ', $mentionedSkills),
                'matched' => $ratio >= 1 ? true : ($ratio > 0 ? 'partial' : false),
                'note'    => 'Required from job\'s other qualifications',
            ];
        }

        $percentage = $total === 0 ? 100 : round(($score / $total) * 100, 2);

        return [
            'percentage' => $percentage,
            'criteria'   => $criteria,
        ];
    }

    // ── Helper: i-determine ang jobseeker's completed & attempted education level ──
    private function getEducationLevelStatus($education): array
    {
        if (!is_array($education)) {
            $education = json_decode($education ?? '{}', true) ?: [];
        }

        $levelRank = [
            'Elementary'                             => 1,
            'Junior High School'                     => 2,
            'Senior High School'                     => 3,
            'Tertiary / College'                     => 4,
            'Graduate Studies/Post-graduate/Masters' => 5,
        ];

        $completeKeywords = [
            'Elementary'                             => ['Graduated'],
            'Junior High School'                     => ['Completer / Grade 10'],
            'Senior High School'                     => ['SHS Graduated'],
            'Tertiary / College'                     => ['Fresh Graduated'],
            'Graduate Studies/Post-graduate/Masters' => ['Graduated'],
        ];

        $completed = 0;
        $attempted = 0;

        foreach ($levelRank as $level => $rank) {
            $data = $education[$level] ?? [];
            if (empty($data['school_name'])) continue;

            $attempted = max($attempted, $rank);

            $yearGraduated = $data['year_graduated'] ?? '';
            if (in_array($yearGraduated, $completeKeywords[$level] ?? [])) {
                $completed = max($completed, $rank);
            }
        }

        return ['completed' => $completed, 'attempted' => $attempted];
    }

    // ── Helper: total work experience sa jobseeker, sa months ──
    private function computeTotalWorkMonths($workExperiences): int
    {
        $totalMonths = 0;
        foreach ($workExperiences as $exp) {
            if (empty($exp->date_from)) continue;
            try {
                $from = \Carbon\Carbon::createFromFormat('m/Y', $exp->date_from)->startOfMonth();
                $to   = ($exp->is_current || $exp->date_to === 'present')
                    ? \Carbon\Carbon::now()
                    : \Carbon\Carbon::createFromFormat('m/Y', $exp->date_to)->startOfMonth();
                $totalMonths += max(0, $from->diffInMonths($to));
            } catch (\Exception $e) {
                // dili ma-parse ang date format, i-skip ra
            }
        }
        return $totalMonths;
    }

    // ── Helper: fuzzy text match ──
    private function textListMatches(?string $needle, array $haystackList): bool
    {
        if (empty($needle) || empty($haystackList)) return false;
        $needle = strtolower(trim($needle));
        foreach ($haystackList as $hay) {
            $hay = strtolower(trim($hay ?? ''));
            if ($hay === '') continue;
            if (str_contains($needle, $hay) || str_contains($hay, $needle)) return true;
        }
        return false;
    }

    /**
     * Course/Major match, with the degree wrapper taken off both sides first.
     *
     * The two sides of this comparison are not written by the same kind of
     * person. The jobseeker picks from a fixed dropdown on the NSRP form and
     * always ends up with the same 47 strings — "BS Nursing". The employer
     * types the requirement free-hand, and writes it the way their industry
     * writes it: "Bachelor of Science in Nursing", "Nursing / Caregiving",
     * "BSc Nursing".
     *
     * Compared whole, none of those three contains "BS Nursing" as a run of
     * characters, so a nurse scored zero on a nursing vacancy. The word
     * "nursing" is in both every time — it is only the degree wrapper in front
     * of it that differs, and that wrapper carries no meaning for matching.
     *
     * Stripping it is deliberately the smallest fix that works. Splitting into
     * words and matching any one of them would pair "BS Marine Engineering"
     * with "BS Marine Transportation" on the word "marine" — two different
     * professions. Checked against all 47 dropdown courses, this rule pairs no
     * course with a different course except AB Communication and AB Mass
     * Communication, which an employer asking for one would accept from the
     * other anyway.
     *
     * Not handled: bare acronyms. "BSN" shares no word with "BS Nursing" and
     * would need a synonym list, which is a table somebody has to maintain.
     */
    private function courseMatches(?string $required, array $courses): bool
    {
        if (empty($required) || empty($courses)) return false;

        $needle = $this->stripDegreePrefix($required);
        if ($needle === '') return false;

        foreach ($courses as $course) {
            $hay = $this->stripDegreePrefix((string) $course);
            if ($hay === '') continue;
            if (str_contains($needle, $hay) || str_contains($hay, $needle)) return true;
        }

        return false;
    }

    /** Lowercase, punctuation out, and the leading degree words removed. */
    private function stripDegreePrefix(string $text): string
    {
        $text = strtolower(trim($text));
        $text = preg_replace('/[^a-z0-9\s]+/', ' ', $text);
        $text = trim(preg_replace('/\s+/', ' ', $text));

        // Longest first, so "bachelor of science in" is taken before "bachelor of".
        $prefixes = [
            'bachelor of science in', 'bachelor of arts in', 'bachelor of science',
            'bachelor of arts', 'bachelors in', 'bachelors of', 'bachelor in', 'bachelor of',
            'master of science in', 'master of arts in', 'master of science',
            'master of arts', 'masters in', 'masters of', 'master in', 'master of',
            'associate in', 'associate of',
            'bsc', 'bsed', 'bse', 'bs', 'ab', 'ba',
        ];

        foreach ($prefixes as $prefix) {
            if (str_starts_with($text, $prefix . ' ')) {
                return trim(substr($text, strlen($prefix) + 1));
            }
        }

        return $text;
    }
}