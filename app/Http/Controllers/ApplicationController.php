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

        $registration = JobseekerRegistration::where('user_id', $jobseeker->id)->first();
        if (!$registration) {
            return redirect()->route('jobseeker.nsrp')
                ->with('info', 'Please complete your NSRP Registration Form first before applying.');
        }

        $job = Job::where('id', $jobId)->where('status', 'open')->firstOrFail();

        // Check if already applied
        $existing = Application::where('jobseeker_id', $registration->id)
            ->where('job_id', $jobId)->first();

        if ($existing) {
            return redirect()->route('jobseeker.jobs.show', $jobId)
                ->with('error', 'You have already applied for this job.');
        }

        // Compute match percentage
        $matchPercentage = $this->computeMatch($jobseeker->id, $job);

        Application::create([
            'jobseeker_id'     => $registration->id,
            'job_id'           => $jobId,
            'status'           => 'pending',
            'match_percentage' => $matchPercentage,
        ]);

        return redirect()->route('jobseeker.jobs.show', $jobId)
            ->with('success', 'Application submitted successfully!');
    }

    // ── PUBLIC wrapper — for use in JobseekerWebController ──
    public function computeMatchPublic($jobseekerId, Job $job): float
    {
        return $this->computeMatch($jobseekerId, $job);
    }

    // ── COMPUTE MATCH PERCENTAGE ──
    private function computeMatch($jobseekerId, Job $job): float
    {
        $registration = JobseekerRegistration::with('nsrp')->where('user_id', $jobseekerId)->first();
        if (!$registration || !$registration->nsrp) return 0;

        $nsrpForm = $registration->nsrp;

        $score = 0;
        $total = 0;

        // ── Sex preference ──
        if ($job->sex_preference && $job->sex_preference !== 'Any') {
            $total += 20;
            if (strtolower($registration->sex ?? '') === strtolower($job->sex_preference)) {
                $score += 20;
            }
        }

        // ── Age ──
        if ($job->age_required) {
            $total += 20;
            $age = $registration->age ?? 0;
            if ($age >= $job->age_min && $age <= $job->age_max) {
                $score += 20;
            }
        }

        // ── Height ──
        if ($job->height_required && $job->height_minimum) {
            $total += 10;
            $jobseekerHeight = floatval($registration->height ?? 0);
            $requiredHeight  = floatval($job->height_minimum);
            if ($jobseekerHeight >= $requiredHeight) {
                $score += 10;
            }
        }

        // ── Education ──
        if ($job->education_required) {
            $total += 20;
            $eduLevels = [
                'Elementary'         => 1,
                'High School'        => 2,
                'Senior High'        => 3,
                'Tertiary / College' => 4,
                'Graduate Studies'   => 5,
            ];
            $education = is_array($nsrpForm->education)
                ? $nsrpForm->education
                : json_decode($nsrpForm->education ?? '{}', true);

            $highestLevel = 0;
            foreach ($education as $level => $data) {
                if (!empty($data['school_name']) && isset($eduLevels[$level])) {
                    $highestLevel = max($highestLevel, $eduLevels[$level]);
                }
            }
            $requiredLevel = $eduLevels[$job->education_required] ?? 0;
            if ($highestLevel >= $requiredLevel) {
                $score += 20;
            }
        }

        // ── Experience ──
        if ($job->experience_required) {
            $total += 20;

            // Use normalized table
            $workExperiences = \App\Models\JobseekerWorkExperience::where(
    'jobseeker_nsrp_registration_id', $nsrpForm->id
)->get();

            $totalYears = 0;
            foreach ($workExperiences as $exp) {
                if (!empty($exp->date_from)) {
                    try {
                        $from = \Carbon\Carbon::createFromFormat('m/Y', $exp->date_from);
                        $to   = $exp->is_current || $exp->date_to === 'present'
                            ? \Carbon\Carbon::now()
                            : \Carbon\Carbon::createFromFormat('m/Y', $exp->date_to);
                        $totalYears += $from->diffInYears($to);
                    } catch (\Exception $e) {}
                }
            }

            if ($totalYears >= ($job->experience_years ?? 0)) {
                $score += 20;
            }
        }

        // ── Skills ──
        if ($job->skills_required) {
            $requiredSkills = is_array($job->skills_required)
                ? $job->skills_required
                : json_decode($job->skills_required ?? '[]', true);

            if (!empty($requiredSkills)) {
                $total += 10;
                $jobseekerSkills = is_array($nsrpForm->other_skills)
                    ? $nsrpForm->other_skills
                    : json_decode($nsrpForm->other_skills ?? '[]', true);

                $matched = count(array_intersect(
                    array_map('strtolower', $requiredSkills),
                    array_map('strtolower', $jobseekerSkills)
                ));
                $score += ($matched / count($requiredSkills)) * 10;
            }
        }

        if ($total === 0) return 100;

        return round(($score / $total) * 100, 2);
    }
}