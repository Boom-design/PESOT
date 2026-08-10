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

        $job = Job::where('job_qualifications_id', $jobId)->where('status', 'open')->firstOrFail();

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
            'office_participation'   => $job->schedule_type === 'office_based' ? 'pending' : null,
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
            $daysUntil = $job->preferred_date ? now()->diffInDays(\Carbon\Carbon::parse($job->preferred_date), false) : null;
            if ($daysUntil !== null && $daysUntil >= 0 && $daysUntil <= 5) {
                $application->update(['inhouse_participation_notified_at' => now()]);
                return redirect()->route('jobseeker.jobs.show', $jobId)
                    ->with('success', 'Application submitted successfully!')
                    ->with('show_inhouse_prompt', $application->job_matching_id);
            }
            return redirect()->route('jobseeker.jobs.show', $jobId)
                ->with('success', 'Application submitted successfully!');
        }

        // ── Office Based: prompt dayon, walay 5-day rule — ipakita ang company name + address ──
        if ($job->schedule_type === 'office_based') {
            return redirect()->route('jobseeker.jobs.show', $jobId)
                ->with('success', 'Application submitted successfully!')
                ->with('show_office_prompt', $application->job_matching_id);
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
        $job = Job::where('job_qualifications_id', $request->job_id)->where('status', 'open')->firstOrFail();

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
            'office_participation'  => $job->schedule_type === 'office_based' ? 'accepted' : null,
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

        $application->update(['inhouse_participation' => $request->response]);

        $msg = $request->response === 'accepted'
            ? 'You have confirmed your participation in the in-house interview! Check your Schedules page for details.'
            : 'You have declined to participate in the in-house interview.';

        return back()->with('success', $msg);
    }

    // ── RESPOND SA OFFICE BASED PARTICIPATION PROMPT ──
    public function respondOfficeParticipation(Request $request, $id)
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

        if ($request->response === 'declined') {
            // ── I-delete ang application record — free na siya mo-apply pag-usab sa parehas nga job ──
            $jobId = $application->job_id;
            $application->delete();

            return redirect()->route('jobseeker.jobs.show', $jobId)
                ->with('success', 'You have declined this job posting. You may apply again anytime.');
        }

        $application->update(['office_participation' => $request->response]);

        return back()->with('success', 'You have confirmed your interest in this job posting!');
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
            $matched = $this->textListMatches($job->course_major, $courses);
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
}