<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

/**
 * Remove exactly what DemoPopulationSeeder created, and nothing else.
 *
 * It reads the manifest the seeder wrote, so an employer or jobseeker
 * registered by hand is never touched. Rows that live use added on top of the
 * demo data — an application, a notification, an in-house booking made during
 * a walkthrough — are removed with the demo records they point to, so nothing
 * is left pointing at a row that no longer exists.
 *
 * Run: php artisan db:seed --class=DemoPopulationPurgeSeeder
 */
class DemoPopulationPurgeSeeder extends Seeder
{
    public function run(): void
    {
        $disk = Storage::disk('local');

        if (!$disk->exists(DemoPopulationSeeder::MANIFEST)) {
            $this->command?->warn('No demo population manifest found. Nothing to remove.');
            return;
        }

        $m = json_decode($disk->get(DemoPopulationSeeder::MANIFEST), true) ?: [];
        $ids = fn(string $key) => array_values(array_filter((array) ($m[$key] ?? [])));

        $users     = $ids('users');
        $employers = $ids('employers');
        $regs      = $ids('registrations');
        $nsrp      = $ids('nsrp');
        $jobs      = $ids('jobs');
        $events    = $ids('events');

        DB::transaction(function () use ($ids, $users, $employers, $regs, $nsrp, $jobs, $events) {
            $in = function (string $table, string $column, array $values) {
                if ($values && Schema::hasTable($table) && Schema::hasColumn($table, $column)) {
                    foreach (array_chunk($values, 500) as $chunk) {
                        DB::table($table)->whereIn($column, $chunk)->delete();
                    }
                }
            };

            // Notices the seeder wrote, then anything live use hung on the demo rows.
            $in('announcements', 'announcements_id', $ids('announcements'));
            if ($jobs) {
                DB::table('announcements')->where('reference_type', 'job')->whereIn('reference_id', $jobs)->delete();
            }
            if ($events) {
                DB::table('announcements')->whereIn('reference_type', ['job_fair', 'job_fair_selection'])
                    ->whereIn('reference_id', $events)->delete();
            }
            if ($ids('inhouse_schedules')) {
                DB::table('announcements')->where('reference_type', 'inhouse_schedule')
                    ->whereIn('reference_id', $ids('inhouse_schedules'))->delete();
            }
            $in('inhouse_participants', 'inhouse_schedule_id', $ids('inhouse_schedules'));
            $in('inhouse_schedules', 'inhouse_schedules_id', $ids('inhouse_schedules'));
            $in('office_calendar_events', 'office_calendar_events_id', $ids('office_events'));
            $in('job_fair_imported_reports', 'job_fair_imported_reports_id', $ids('jf_imported'));
            $in('job_fair_imported_reports', 'job_fair_id', $events);
            $in('job_vacancy_imported_reports', 'job_vacancy_imported_reports_id', $ids('jv_imported'));
            $in('announcements', 'jobseeker_id', $regs);
            $in('announcements', 'employer_id', $employers);
            $in('inhouse_participants', 'jobseeker_id', $regs);
            $in('inhouse_schedules', 'employer_id', $employers);
            $in('job_activity_logs', 'job_id', $jobs);
            $in('job_archives', 'company_id', $employers);
            $in('employer_account_transfers', 'employer_id', $employers);

            // The demo rows themselves, children first.
            $in('job_matching', 'job_matching_id', $ids('applications'));
            $in('job_matching', 'job_id', $jobs);
            $in('job_matching', 'jobseeker_id', $regs);
            $in('job_fair_registrations', 'job_fair_registrations_id', $ids('fair_registrations'));
            $in('job_fair_registrations', 'user_id', $regs);
            $in('job_fair_registrations', 'job_fair_id', $events);
            $in('job_fair_employment_requests', 'job_fair_employment_requests_id', $ids('employment_requests'));
            $in('job_fair_employment_requests', 'job_id', $jobs);
            $in('job_fair_participants', 'job_fair_participants_id', $ids('participants'));
            $in('job_fair_participants', 'employer_id', $employers);
            $in('job_fair_participants', 'job_fair_id', $events);
            $in('job_fair_events', 'job_fair_events_id', $events);
            $in('job_qualifications', 'job_qualifications_id', $jobs);
            $in('jobseeker_certifications', 'jobseeker_nsrp_registration_id', $nsrp);
            $in('jobseeker_work_experiences', 'jobseeker_nsrp_registration_id', $nsrp);
            $in('jobseeker_nsrp_registrations', 'jobseeker_nsrp_registrations_id', $nsrp);
            $in('jobseeker_registrations', 'jobseeker_registrations_id', $regs);
            $in('employer_requirements', 'employer_requirements_id', $ids('requirements'));
            $in('employer_nsrp_registrations', 'employer_nsrp_registrations_id', $employers);
            $in('staff', 'staff_id', $ids('staff'));
            $in('sessions', 'user_id', $users);
            $in('users', 'users_id', $users);
        });

        $disk->delete([DemoPopulationSeeder::DOCUMENT, DemoPopulationSeeder::MANIFEST]);

        $this->command?->info(sprintf(
            'Demo population removed: %d users, %d employers, %d jobseekers, %d job postings, %d job fairs.',
            count($users), count($employers), count($regs), count($jobs), count($events)
        ));
    }
}
