<?php

namespace App\Console\Commands;

use App\Models\Application;
use App\Models\EmployerNsrpRegistration;
use App\Models\EmployerRequirement;
use App\Models\Job;
use App\Models\JobFairEmploymentRequest;
use App\Models\JobFairEvent;
use App\Models\JobFairParticipant;
use App\Models\JobFairRegistration;
use App\Models\JobseekerNsrpRegistration;
use App\Models\JobseekerRegistration;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * One job fair, one employer, one jobseeker who has joined it.
 *
 * PESO CDO, 2026-11-06: the system was emptied for the disc, and the paper
 * needs a screenshot of a jobseeker holding a slip number. That takes a fair
 * to join, an employer to be at it, and a vacancy to apply for — so this puts
 * the smallest set of rows in place that makes the slip real rather than
 * drawn.
 *
 * Everything it writes is marked: the accounts end in @demo.peso.test and the
 * event is titled Demo. `--undo` takes exactly those rows out again and
 * nothing else.
 *
 * Re-running it is safe. It finds what it already made instead of making a
 * second copy.
 */
class DemoJobFair extends Command
{
    protected $signature = 'peso:demo-jobfair {--undo : Remove what this command created}';

    protected $description = 'Put one job fair, one employer and one joined jobseeker in place for a screenshot';

    private const PASSWORD = 'Peso@2026';

    // The LRA desk, not the Job Fair one. Both could own the event, but the
    // slip number is only ever shown on two screens — Participants and the
    // Attendance report — and those are LRA and SRA only. A Job Fair account
    // here would make the fair and then be unable to see the thing the fair
    // was made to show.
    private const STAFF_EMAIL    = 'lra.demo@demo.peso.test';
    private const EMPLOYER_EMAIL = 'employer.demo@demo.peso.test';
    private const SEEKER_EMAIL   = 'jobseeker.demo@demo.peso.test';

    private const EVENT_TITLE = 'Demo Job Fair 2026';

    public function handle(): int
    {
        return $this->option('undo') ? $this->undo() : $this->make();
    }

    private function make(): int
    {
        DB::transaction(function () {
            // ── The desk that owns the event ──
            // job_fair_events.created_by is NOT NULL, so a fair cannot exist
            // without a staff row behind it.
            $staffUser = $this->account(self::STAFF_EMAIL, 'PESO LRA Staff', 'staff');
            $staff = Staff::firstOrCreate(
                ['user_id' => $staffUser->users_id],
                ['staff_role' => 'lra', 'first_name' => 'Demo', 'last_name' => 'LRA Staff']
            );

            // ── The employer, with its papers already read ──
            $employerUser = $this->account(self::EMPLOYER_EMAIL, 'Northbay Trading Corporation', 'company');
            $employer = EmployerNsrpRegistration::firstOrCreate(
                ['user_id' => $employerUser->users_id],
                [
                    'company_name'          => 'Northbay Trading Corporation',
                    'contact_person'        => 'Marivic Lamparas',
                    'position_title'        => 'HR Officer',
                    'mobile_number'         => '09171234567',
                    'employer_type'         => 'Direct Hire',
                    'tin'                   => '319584027',
                    'line_of_business'      => 'Retail Trade',
                    'industry_group'        => 'Wholesale and Retail Trade',
                    'est_province'          => 'Misamis Oriental',
                    'est_city_municipality' => 'Cagayan de Oro City',
                    'est_barangay'          => 'Carmen',
                    'is_overseas'           => false,
                ]
            );

            // Approved papers: without them the posting never reaches the
            // public, and the fair would have nothing to show.
            EmployerRequirement::updateOrCreate(
                ['user_id' => $employer->employer_nsrp_registrations_id],
                [
                    'status'          => 'approved',
                    'approved_fields' => array_keys(EmployerRequirement::LOCAL_DOCUMENTS),
                    'rejected_fields' => [],
                ]
            );

            // ── The fair ──
            // Far enough ahead to be the next one on the landing page, close
            // enough that its own cutoff has not passed.
            $event = JobFairEvent::firstOrCreate(
                ['title' => self::EVENT_TITLE],
                [
                    'created_by'        => $staff->staff_id,
                    'event_date'        => now()->addDays(35)->toDateString(),
                    'event_time'        => '09:00:00',
                    'venue'             => 'Limketkai Center Atrium',
                    'venue_address'     => 'Lapasan, Cagayan de Oro City',
                    'cater'             => ['local'],
                    'target_industries' => ['Wholesale and Retail Trade'],
                    'pwd_only'          => false,
                    'local_capacity'    => 10,
                    'employer_capacity' => 10,
                    'status'            => 'upcoming',
                ]
            );

            // Confirmed, not accepted: the SMS gate and the reports both
            // count confirmed, and a local employer's yes is a yes.
            JobFairParticipant::updateOrCreate(
                ['job_fair_id' => $event->job_fair_events_id, 'employer_id' => $employer->employer_nsrp_registrations_id],
                ['confirmation_status' => 'confirmed', 'invited_at' => now(), 'responded_at' => now()]
            );

            // ── The vacancy, already past its cutoff ──
            $job = Job::firstOrCreate(
                [
                    'company_id' => $employer->employer_nsrp_registrations_id,
                    'title'      => 'Sales Associate',
                    'schedule_type' => 'job_fair',
                ],
                [
                    'description'           => 'Assists walk-in customers, restocks shelves, and operates the point-of-sale terminal.',
                    'location'              => 'Cagayan de Oro City',
                    'type'                  => 'permanent',
                    'salary'                => 'P13,000 - P15,000 / month',
                    'slots'                 => 6,
                    'deadline'              => $event->event_date->toDateString(),
                    'education_required'    => 'Any',
                    'language'              => 'English',
                    'requested_job_fair_id' => $event->job_fair_events_id,
                    'posting_status'        => 'approved',
                    'status'                => 'open',
                ]
            );

            JobFairEmploymentRequest::firstOrCreate([
                'job_fair_id' => $event->job_fair_events_id,
                'employer_id' => $employer->employer_nsrp_registrations_id,
                'job_id'      => $job->job_qualifications_id,
            ]);

            // ── The jobseeker, and the slip ──
            $seekerUser = $this->account(self::SEEKER_EMAIL, 'Neca Subasos', 'jobseeker');
            $seeker = JobseekerRegistration::firstOrCreate(
                ['user_id' => $seekerUser->users_id],
                [
                    'first_name'        => 'Neca',
                    'surname'           => 'Subasos',
                    'sex'               => 'Female',
                    'age'               => 24,
                    'date_of_birth'     => '2002-04-18',
                    'contact_number'    => '09171234568',
                    'reg_email'         => self::SEEKER_EMAIL,
                    'municipality_city' => 'Cagayan de Oro City',
                    'province'          => 'Misamis Oriental',
                    'barangay'          => 'Pagatpat',
                    'sms_opt_in'        => true,
                ]
            );

            JobseekerNsrpRegistration::firstOrCreate(
                ['jobseeker_registration_id' => $seeker->jobseeker_registrations_id],
                [
                    'type'                  => 'local',
                    'employment_type'       => 'unemployed',
                    'work_type'             => 'full_time',
                    'preferred_occupations' => ['Sales Associate', 'Cashier'],
                    'language_proficiency'  => ['English' => ['read' => '1', 'speak' => '1', 'write' => '1', 'understand' => '1'],
                                                'Filipino' => ['read' => '1', 'speak' => '1', 'write' => '1', 'understand' => '1']],
                ]
            );

            // The slip number is built the way joinJobFair builds it: the
            // fair's id, then the count of people already in, padded to four.
            $already = JobFairRegistration::where('job_fair_id', $event->job_fair_events_id)->count();
            JobFairRegistration::firstOrCreate(
                ['job_fair_id' => $event->job_fair_events_id, 'user_id' => $seeker->jobseeker_registrations_id],
                [
                    'slip_number' => 'JF' . $event->job_fair_events_id . '-' . str_pad($already + 1, 4, '0', STR_PAD_LEFT),
                    'is_early'    => true,
                ]
            );

            // Applied as well, so the employer's list is not empty behind it.
            Application::firstOrCreate(
                ['job_id' => $job->job_qualifications_id, 'jobseeker_id' => $seeker->jobseeker_registrations_id],
                ['status' => 'pending', 'match_percentage' => 100]
            );
        });

        $this->report();

        return self::SUCCESS;
    }

    /** A user row with the demo password, or the one already there. */
    private function account(string $email, string $name, string $role): User
    {
        $user = User::where('email', $email)->first();

        if ($user) {
            return $user;
        }

        return User::create([
            'name'     => $name,
            'email'    => $email,
            'password' => Hash::make(self::PASSWORD),
            'role'     => $role,
            'status'   => 'approved',
            'privacy_consented_at' => now(),
        ]);
    }

    private function report(): void
    {
        $event = JobFairEvent::where('title', self::EVENT_TITLE)->first();
        $slip  = $event
            ? JobFairRegistration::where('job_fair_id', $event->job_fair_events_id)->value('slip_number')
            : null;

        $this->line('');
        $this->line('  Event    ' . self::EVENT_TITLE . '  ·  ' . ($event?->event_date->toDateString() ?? '?')
            . '  (id ' . ($event?->job_fair_events_id ?? '?') . ')');
        $this->line('  Employer Northbay Trading Corporation  ·  ' . self::EMPLOYER_EMAIL);
        $this->line('  Seeker   Neca Subasos  ·  ' . self::SEEKER_EMAIL);
        $this->line('  Staff    ' . self::STAFF_EMAIL . '  (LRA desk, where the slip shows)');
        $this->line('  SLIP     ' . ($slip ?: 'not issued'));
        $this->line('');
        $this->info('All three sign in with the usual demo password. Remove with --undo.');
    }

    private function undo(): int
    {
        DB::transaction(function () {
            $event = JobFairEvent::where('title', self::EVENT_TITLE)->first();

            if ($event) {
                // The postings first: deleting the event cascades the link
                // rows away, and an orphaned posting could not be found again.
                $jobIds = JobFairEmploymentRequest::where('job_fair_id', $event->job_fair_events_id)->pluck('job_id');
                Job::whereIn('job_qualifications_id', $jobIds)->delete();
                $event->delete();
            }

            foreach ([self::SEEKER_EMAIL, self::EMPLOYER_EMAIL, self::STAFF_EMAIL] as $email) {
                $user = User::where('email', $email)->first();
                if (!$user) {
                    continue;
                }

                $seeker = JobseekerRegistration::where('user_id', $user->users_id)->first();
                if ($seeker) {
                    JobFairRegistration::where('user_id', $seeker->jobseeker_registrations_id)->delete();
                    Application::where('jobseeker_id', $seeker->jobseeker_registrations_id)->delete();
                    JobseekerNsrpRegistration::where('jobseeker_registration_id', $seeker->jobseeker_registrations_id)->delete();
                    $seeker->delete();
                }

                $employer = EmployerNsrpRegistration::where('user_id', $user->users_id)->first();
                if ($employer) {
                    EmployerRequirement::where('user_id', $employer->employer_nsrp_registrations_id)->delete();
                    Job::where('company_id', $employer->employer_nsrp_registrations_id)->delete();
                    JobFairParticipant::where('employer_id', $employer->employer_nsrp_registrations_id)->delete();
                    $employer->delete();
                }

                Staff::where('user_id', $user->users_id)->delete();
                $user->delete();
            }
        });

        $this->info('Removed. Only what this command created was touched.');

        return self::SUCCESS;
    }
}
