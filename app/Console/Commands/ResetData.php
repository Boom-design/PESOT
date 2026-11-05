<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

/**
 * Empties the system back to its three administrators.
 *
 * PESO CDO, 2026-11-05: the copy that goes on the disc, and the live site
 * after it, should hold nothing but the accounts the office signs in with.
 * Everything demonstrated, tested and seeded goes.
 *
 * The three administrators survive because AdminSeeder creates them and
 * nothing else can: with the users table empty there is no way back in.
 * Their passwords are untouched — this clears data, it does not reset
 * anybody's sign-in.
 *
 * NOT MIGRATIONS. `migrate:fresh` would drop the schema and take the
 * migration history with it; this empties tables that are already there, so
 * the database stays exactly the shape the code expects.
 */
class ResetData extends Command
{
    protected $signature = 'peso:reset-data
                            {--force : Skip the confirmation}
                            {--keep-staff : Keep the staff accounts as well}';

    protected $description = 'Empty every record except the administrator accounts';

    /**
     * Everything the office put in. Order does not matter — foreign keys are
     * off while this runs — but it is written desk by desk so a reader can
     * check nothing was forgotten.
     */
    private const WIPE = [
        // Jobseekers
        'jobseeker_work_experiences',
        'jobseeker_certifications',
        'jobseeker_nsrp_registrations',
        'jobseeker_registrations',

        // Employers
        'employer_requirements',
        'employer_account_transfers',
        'employer_nsrp_registrations',

        // Postings and what happened to them
        'job_matching',
        'job_activity_logs',
        'job_archives',
        'job_qualifications',

        // In-house
        'inhouse_participants',
        'inhouse_schedules',

        // Job fairs
        'job_fair_employment_requests',
        'job_fair_participants',
        'job_fair_registrations',
        'job_fair_imported_reports',
        'job_fair_events',

        // The office's own
        'job_vacancy_imported_reports',
        'office_calendar_events',
        'announcements',
        'admin_activity_logs',

        // Sign-in leftovers. Not the users table — that is handled below.
        'sessions',
        'password_reset_codes',
        'password_reset_tokens',
        'personal_access_tokens',
        'cache',
        'cache_locks',
    ];

    public function handle(): int
    {
        $keepStaff = (bool) $this->option('keep-staff');

        $admins = User::where('role', 'admin')->pluck('email')->all();

        if (empty($admins)) {
            $this->error('No administrator account found. Run peso:ensure-admin or db:seed --class=AdminSeeder first, '
                . 'or this would leave the system with no way in.');
            return self::FAILURE;
        }

        $this->line('');
        $this->line('  This empties every record in <options=bold>' . DB::connection()->getDatabaseName() . '</>.');
        $this->line('');
        $this->line('  Kept: ' . implode(', ', $admins));
        if ($keepStaff) {
            $this->line('        and every staff account');
        }
        $this->line('  Gone: employers, jobseekers, postings, applications, job fairs,');
        $this->line('        schedules, reports, the office calendar, and uploaded files.');
        $this->line('');

        if (!$this->option('force') && !$this->confirm('This cannot be undone. Continue?', false)) {
            $this->line('Nothing was changed.');
            return self::SUCCESS;
        }

        $cleared = [];

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            foreach (self::WIPE as $table) {
                if (!Schema::hasTable($table)) {
                    continue;
                }
                $n = DB::table($table)->count();
                DB::table($table)->truncate();
                if ($n > 0) {
                    $cleared[$table] = $n;
                }
            }

            // The staff rows go with their users unless they are being kept.
            if (!$keepStaff && Schema::hasTable('staff')) {
                $n = DB::table('staff')->count();
                DB::table('staff')->truncate();
                if ($n > 0) {
                    $cleared['staff'] = $n;
                }
            }

            // Last, and by email: a truncate here would take the
            // administrators with everything else.
            $query = User::whereNotIn('email', $admins);
            if ($keepStaff) {
                $query->where('role', '!=', 'staff');
            }
            $gone = $query->count();
            $query->delete();
            if ($gone > 0) {
                $cleared['users'] = $gone;
            }
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        $files = $this->clearUploads();

        $this->line('');
        foreach ($cleared as $table => $n) {
            $this->line(sprintf('  %-32s %6d row(s)', $table, $n));
        }
        $this->line(sprintf('  %-32s %6d file(s)', 'uploaded files', $files));
        $this->line('');
        $this->info('Empty. ' . count($admins) . ' administrator account(s) kept, passwords untouched.');

        if (!$keepStaff) {
            $this->warn('No staff accounts remain. An administrator has to create them before any desk can work.');
        }

        return self::SUCCESS;
    }

    /**
     * The documents, logos and posters people uploaded.
     *
     * A row pointing at a file that is gone is a broken page; a file with no
     * row is a copy of somebody's papers nobody can reach or delete. Both
     * leave together.
     */
    private function clearUploads(): int
    {
        $count = 0;

        // The folder names are the ones the upload calls actually write to;
        // the local disk is rooted at storage/app/private, the public one at
        // storage/app/public. A folder that was never created is skipped.
        foreach (['local'  => ['employer_requirements', 'nsrp_scans', 'certificates'],
                  'public' => ['job_posters', 'job_images', 'jobfair_posters',
                               'jobseeker_photos', 'profile_photos']] as $disk => $folders) {
            foreach ($folders as $folder) {
                if (!Storage::disk($disk)->exists($folder)) {
                    continue;
                }
                $count += count(Storage::disk($disk)->allFiles($folder));
                Storage::disk($disk)->deleteDirectory($folder);
            }
        }

        return $count;
    }
}
