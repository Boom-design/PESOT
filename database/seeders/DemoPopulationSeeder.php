<?php

namespace Database\Seeders;

use App\Http\Controllers\ApplicationController;
use App\Models\Job;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

/**
 * Fill the system with realistic, internally consistent demo data.
 *
 * Panel suggestion, 2026-09-13: the system should be populated, so reports,
 * charts, pagination and matching can be judged at a believable size instead
 * of on two or three hand-typed rows.
 *
 * This is not static data. It writes ordinary rows into the same tables the
 * registration form, Post a Job and Apply write to, once. After that the
 * system reads them exactly like anything typed in by hand — every report,
 * match and count is still computed live, and live actions still change them.
 *
 * What keeps it believable:
 *   - Match percentages come from ApplicationController's real scoring, not
 *     invented numbers.
 *   - Dates follow the real order: registered, posted, applied, interviewed,
 *     hired, started. Deadlines never exceed Job::MAX_DEADLINE_MONTHS.
 *   - Hires never exceed a posting's slots.
 *   - Every employer has a posting inside the last month, so the daily
 *     inactivity sweep does not start warning them the morning after.
 *
 * What keeps it safe:
 *   - Every account uses an @demo.peso.test address. The .test domain is
 *     reserved and never resolves, so no email can reach a real person.
 *   - Every seeded jobseeker and employer has sms_opt_in = 0, so a Notify
 *     press during a demo cannot text 200 random numbers at our expense.
 *   - Nothing here calls a notifier, a mailer or PhilSMS.
 *   - Every id it creates is written to a manifest, and
 *     DemoPopulationPurgeSeeder removes exactly those rows and nothing else.
 *
 * Run:    php artisan db:seed --class=DemoPopulationSeeder
 * Undo:   php artisan db:seed --class=DemoPopulationPurgeSeeder
 */
class DemoPopulationSeeder extends Seeder
{
    public const DOMAIN   = 'demo.peso.test';
    public const MANIFEST = 'demo_population_manifest.json';
    public const DOCUMENT = 'employer_requirements/demo-population-requirement.pdf';

    private const EMPLOYERS_LOCAL    = 30;
    private const EMPLOYERS_OVERSEAS = 6;
    private const JOBSEEKERS         = 220;

    private const STAFF_SRA      = 1;
    private const STAFF_LRA      = 2;
    private const STAFF_JOB_FAIR = 3;
    private const STAFF_JOB_VACANCY = 4;

    private const WHOLESALE = 'Wholesale, Retail Trade, Repair of Motor Vehicles, Motorcycles, & Personal and Household Goods';
    private const OVERSEAS  = 'Overseas Manpower Services';

    // title => [course, certification, education required, skills, salary min, salary max, licence]
    private const LOCAL_TRACKS = [
        'Manufacturing' => [
            'Production Operator'     => ['BS Food Technology', 'Food Processing NC II', 'Tertiary / College', ['Computer Literate'], 13500, 16000, null],
            'Quality Control Analyst' => ['BS Food Technology', 'Food Processing NC II', 'Tertiary / College', ['Computer Literate'], 17000, 21000, null],
            'Machine Operator'        => ['BS Mechanical Engineering', 'Mechatronics Servicing NC II', 'Tertiary / College', ['Auto Mechanic'], 14000, 17000, null],
            'Warehouse Checker'       => [null, null, 'Senior High', ['Computer Literate'], 12500, 14500, null],
        ],
        'Construction' => [
            'Mason'         => [null, 'Masonry NC II', 'Senior High', ['Masonry'], 12000, 15000, null],
            'Carpenter'     => [null, 'Carpentry NC II', 'Senior High', ['Carpentry Work'], 12000, 15000, null],
            'Electrician'   => [null, 'Electrical Installation and Maintenance NC II', 'Senior High', ['Electrician'], 14000, 18000, null],
            'Site Engineer' => ['BS Civil Engineering', null, 'Tertiary / College', ['Computer Literate'], 25000, 35000, null],
        ],
        'Hotel and Restaurants' => [
            'Front Desk Officer'          => ['BS Tourism Management', null, 'Tertiary / College', ['Computer Literate'], 14000, 17000, null],
            'Cook'                        => [null, 'Cookery NC II', 'Senior High', ['Domestic Chores'], 13000, 16000, null],
            'Food and Beverage Attendant' => [null, 'Food and Beverage Services NC II', 'Senior High', ['Domestic Chores'], 12500, 14500, null],
            'Housekeeping Attendant'      => [null, 'Housekeeping NC II', 'Senior High', ['Domestic Chores'], 12500, 14000, null],
        ],
        'Real Estate, Renting and Business Activities' => [
            'Customer Service Representative' => ['BS Information Technology', 'Computer Systems Servicing NC II', 'Tertiary / College', ['Computer Literate'], 16000, 20000, null],
            'Technical Support Associate'     => ['BS Information Technology', 'Computer Systems Servicing NC II', 'Tertiary / College', ['Computer Literate'], 18000, 22000, null],
            'Data Encoder'                    => [null, 'Computer Systems Servicing NC II', 'Senior High', ['Computer Literate'], 13000, 15500, null],
        ],
        self::WHOLESALE => [
            'Sales Associate' => ['BS Business Administration', null, 'Tertiary / College', ['Computer Literate'], 12500, 15000, null],
            'Cashier'         => [null, 'Bookkeeping NC III', 'Senior High', ['Computer Literate'], 12500, 14500, null],
            'Inventory Clerk' => ['BS Business Administration', 'Bookkeeping NC III', 'Tertiary / College', ['Computer Literate'], 13500, 16000, null],
        ],
        'Health and Social Work' => [
            'Staff Nurse'        => ['BS Nursing', null, 'Tertiary / College', ['Computer Literate'], 22000, 30000, 'Registered Nurse (PRC)'],
            'Caregiver'          => [null, 'Caregiving NC II', 'Senior High', ['Domestic Chores'], 12000, 15000, null],
            'Pharmacy Assistant' => ['BS Pharmacy', null, 'Tertiary / College', ['Computer Literate'], 14000, 17000, null],
        ],
        'Education' => [
            'Elementary Teacher' => ['BS Education', null, 'Tertiary / College', ['Computer Literate'], 20000, 26000, 'Licensed Professional Teacher (PRC)'],
            'Teacher Aide'       => ['BS Education', null, 'Tertiary / College', ['Computer Literate'], 13000, 16000, null],
        ],
        'Transport, Storage and Communications' => [
            'Delivery Rider'        => [null, null, 'Senior High', ['Driver'], 12000, 15000, null],
            'Logistics Coordinator' => ['BS Business Administration', null, 'Tertiary / College', ['Computer Literate', 'Driver'], 17000, 21000, null],
        ],
        'Financial Intermediation' => [
            'Bookkeeper'   => ['BS Accountancy', 'Bookkeeping NC III', 'Tertiary / College', ['Computer Literate'], 16000, 20000, null],
            'Loan Officer' => ['BS Business Administration', null, 'Tertiary / College', ['Computer Literate', 'Driver'], 16000, 21000, null],
        ],
        'Other Community, Social and Personal Activities' => [
            'Security Guard' => [null, 'Security Services NC II', 'Senior High', ['Driver'], 13000, 16000, null],
            'Janitor'        => [null, null, 'High School', ['Domestic Chores'], 11500, 13000, null],
        ],
    ];

    private const OVERSEAS_TRACKS = [
        'Hotel Housekeeping Attendant' => [null, 'Housekeeping NC II', 'Senior High', ['Domestic Chores'], 0, 0, null],
        'Kitchen Staff'                => [null, 'Cookery NC II', 'Senior High', ['Domestic Chores'], 0, 0, null],
        'Welder'                       => [null, 'Shielded Metal Arc Welding NC II', 'Senior High', ['Auto Mechanic'], 0, 0, null],
        'Heavy Equipment Operator'     => [null, 'Heavy Equipment Operation NC II', 'Senior High', ['Driver'], 0, 0, null],
        'Factory Worker'               => [null, null, 'High School', ['Computer Literate'], 0, 0, null],
    ];

    private const OVERSEAS_PLACES = [
        'Dubai, United Arab Emirates' => 'AED 1,800 / month',
        'Riyadh, Saudi Arabia'        => 'SAR 1,700 / month',
        'Doha, Qatar'                 => 'QAR 1,800 / month',
        'Taoyuan, Taiwan'             => 'TWD 27,470 / month',
        'Kuwait City, Kuwait'         => 'KWD 150 / month',
    ];

    private const NAME_PREFIX = [
        'Kagay-anon', 'Oro Horizon', 'Northpoint', 'Rio Verde', 'Macabalan', 'Lumbia Ridge', 'Puerto Bay', 'Gold Coast',
        'Mindanao Summit', 'Opol Crest', 'Bugo Harbor', 'Carmen Heights', 'Cugman Valley', 'Iponan River', 'Pagatpat',
        'Balulang Hills', 'Nazareth', 'Tablon', 'Gusa Point', 'Agusan Bay', 'Bayabas', 'Bulua Park', 'Patag Highlands',
        'Kauswagan', 'Macasandig', 'Lapasan', 'Puntod Coastal', 'Bonbon', 'Del Monte View', 'Cagayan River', 'Misamis',
        'Seaside', 'Evergreen', 'Silver Oak', 'Blue Marlin', 'Sunrise Pacific', 'Golden Fields', 'Highland Pine',
        'Riverbend', 'Coral Bay',
    ];

    private const NAME_SUFFIX = [
        'Manufacturing'                                   => ['Foods Corp.', 'Beverages Inc.', 'Packaging Corp.', 'Industrial Products Inc.'],
        'Construction'                                    => ['Builders Inc.', 'Construction Corp.', 'Engineering and Development Corp.', 'Contractors Co.'],
        'Hotel and Restaurants'                           => ['Hotel and Suites', 'Resort and Restaurant', 'Grill and Cafe Inc.', 'Inn and Convention Center'],
        'Real Estate, Renting and Business Activities'    => ['Business Solutions Inc.', 'Outsourcing Services Inc.', 'Digital Services Corp.', 'Contact Center Inc.'],
        self::WHOLESALE                                   => ['Trading Corp.', 'Supermart Inc.', 'Merchandising Co.', 'Hardware and Supply'],
        'Health and Social Work'                          => ['Medical Clinic Inc.', 'Diagnostic Center', 'Pharmacy Corp.', 'Home Care Services Inc.'],
        'Education'                                       => ['Learning Academy', 'School of Arts and Trades', 'Montessori School Inc.', 'Review Center'],
        'Transport, Storage and Communications'           => ['Logistics Corp.', 'Freight Services Inc.', 'Courier Express Inc.', 'Transport Services Inc.'],
        'Financial Intermediation'                        => ['Lending Corp.', 'Credit Cooperative', 'Microfinance Inc.', 'Pawnshop and Remittance'],
        'Other Community, Social and Personal Activities' => ['Security Agency Inc.', 'Janitorial Services Corp.', 'Manpower Services Inc.', 'Facility Management Inc.'],
        self::OVERSEAS                                    => ['International Manpower Services Inc.', 'Overseas Placement Agency Inc.', 'Global Recruitment Corp.', 'Worldwide Manpower Inc.'],
    ];

    private const FIRST_MALE = [
        'John Paul', 'Mark Anthony', 'Christian', 'Jerome', 'Kenneth', 'Ryan', 'Joshua', 'Carlo', 'Jayson', 'Rodel',
        'Arnel', 'Michael', 'Vincent', 'Jomar', 'Renz', 'Aldrin', 'Joel', 'Ramil', 'Dexter', 'Bryan', 'Neil',
        'Francis', 'Kevin', 'Rommel', 'Jericho',
    ];

    private const FIRST_FEMALE = [
        'Mary Grace', 'Kristine', 'Angelica', 'Jessa Mae', 'Princess', 'Rhea', 'Joanna', 'Maricel', 'Charmaine',
        'Kimberly', 'Liezel', 'April Joy', 'Shiela', 'Janine', 'Camille', 'Rose Ann', 'Diane', 'Ma. Cristina',
        'Lovely', 'Hazel', 'Nikka', 'Trisha', 'Jenny', 'Frances', 'Aileen',
    ];

    private const SURNAMES = [
        'Abella', 'Acedo', 'Bacalso', 'Baclayon', 'Cabahug', 'Cabatingan', 'Dagatan', 'Daclan', 'Echavez', 'Estrada',
        'Fernandez', 'Gonzaga', 'Gumapac', 'Jumao-as', 'Lagura', 'Lim', 'Maglinte', 'Mercado', 'Neri', 'Ompoc',
        'Pacana', 'Paradela', 'Quijano', 'Ramos', 'Rosales', 'Sabellano', 'Sagarino', 'Tagaylo', 'Torralba', 'Ubaldo',
        'Villaruel', 'Yap', 'Zamora', 'Abregana', 'Andales', 'Balatero', 'Densing', 'Pagatpat', 'Sumalinog', 'Tabada',
    ];

    // Checked against storage/app/ph_address for Cagayan de Oro City.
    private const BARANGAYS = [
        'Agusan', 'Balulang', 'Bayabas', 'Bonbon', 'Bugo', 'Bulua', 'Carmen', 'Cugman', 'Gusa', 'Iponan',
        'Kauswagan', 'Lapasan', 'Macasandig', 'Nazareth', 'Patag', 'Puntod', 'Tablon',
    ];

    private const HIGH_SCHOOLS = [
        'Cagayan de Oro City National High School', 'Gusa Regional Science High School', 'Bugo National High School',
        'Lapasan National High School', 'Macasandig National High School', 'Bulua National High School',
    ];

    private const COLLEGES = [
        'Liceo de Cagayan University', 'Xavier University - Ateneo de Cagayan', 'Capitol University',
        'University of Science and Technology of Southern Philippines', 'Lourdes College',
        'Cagayan de Oro College - PHINMA', 'Southern de Oro Philippines College',
    ];

    private const PAST_FAIRS = [
        ['CDO Summer Job Fair 2026', '2026-03-20', 'Limketkai Center Atrium', 'Lapasan, Cagayan de Oro City', ['local'], 8],
        ['Labor Day Job Fair 2026', '2026-05-01', 'Cagayan de Oro City Hall Grounds', 'Poblacion, Cagayan de Oro City', ['local', 'overseas'], 10],
        ['Independence Day Job Fair 2026', '2026-06-12', 'SM CDO Downtown Premier Event Center', 'Claro M. Recto Ave., Lapasan, Cagayan de Oro City', ['local', 'overseas'], 9],
    ];

    private Carbon $today;
    private string $password;
    private ApplicationController $matcher;

    private array $manifest = [
        'users' => [], 'employers' => [], 'requirements' => [], 'registrations' => [], 'nsrp' => [],
        'work_experiences' => [], 'certifications' => [], 'jobs' => [], 'events' => [], 'participants' => [],
        'employment_requests' => [], 'fair_registrations' => [], 'applications' => [],
        'inhouse_schedules' => [], 'inhouse_participants' => [], 'office_events' => [], 'jf_imported' => [],
        'jv_imported' => [], 'activity_logs' => [], 'announcements' => [], 'staff' => [],
    ];

    private array $employers  = [];
    private array $jobseekers = [];
    private array $jobs       = [];
    private array $jobModels  = [];
    private array $hired      = [];
    private array $usedNames  = [];
    private array $usedTins   = [];
    private array $usedEmails = [];
    private array $applied    = [];
    private array $preApproval = [];
    private array $dormant    = [];
    private array $schedules  = [];
    private array $staffIds   = [];
    private array $requirementNotices = [];
    private ?array $upcoming  = null;
    private ?int $expiredEmployerId = null;

    public function run(): void
    {
        if (Storage::disk('local')->exists(self::MANIFEST)
            || DB::table('users')->where('email', 'like', '%@' . self::DOMAIN)->exists()) {
            throw new \RuntimeException(
                'Demo population already exists. Run: php artisan db:seed --class=DemoPopulationPurgeSeeder'
            );
        }

        // Belt and braces. Nothing below sends anything, but a future edit might.
        config(['services.philsms.enabled' => false, 'mail.default' => 'array']);

        mt_srand(20260913);

        $this->today    = Carbon::today();
        $this->password = Hash::make('Peso@2026');
        $this->matcher  = new ApplicationController();

        // The desks as they stand now, so notices reach whoever holds the seat.
        $this->staffIds = DB::table('staff')->join('users', 'users.users_id', '=', 'staff.user_id')
            ->where('users.status', '!=', 'deactivated')
            ->get(['staff.staff_id', 'staff.staff_role'])
            ->groupBy('staff_role')
            ->map(fn($rows) => $rows->pluck('staff_id')->all())
            ->all();

        Storage::disk('local')->put(self::DOCUMENT, $this->placeholderPdf());

        DB::transaction(function () {
            $this->seedEmployers();
            $this->markDormantEmployers();
            $this->seedPreApprovalEmployers();
            $this->seedJobseekers();
            $this->seedWalkInJobseekers();
            $this->seedDeactivatedStaff();
            $this->seedPastJobFairs();
            $this->seedRegularPostings();
            $this->seedPendingPostings();
            $this->ensureRecentPosting();
            $this->seedRegularApplications();
            $this->ensureEveryEmployerHired();
            $this->applyDormancy();
            $this->applyRequirementStates();
            $this->seedUpcomingJobFair();
            $this->seedInhouseSchedules();
            $this->seedOfficeCalendar();
            $this->seedImportedReports();
            $this->seedActivityLogs();
            $this->recordPesoEmployment();
            $this->seedNotifications();
        });

        Storage::disk('local')->put(self::MANIFEST, json_encode($this->manifest, JSON_PRETTY_PRINT));

        $this->command?->info(sprintf(
            'Demo population: %d employers, %d jobseekers, %d job postings, %d applications (%d hired), %d job fairs, %d fair registrations.',
            count($this->manifest['employers']),
            count($this->manifest['registrations']),
            count($this->manifest['jobs']),
            count($this->manifest['applications']),
            array_sum($this->hired),
            count($this->manifest['events']),
            count($this->manifest['fair_registrations'])
        ));
        $this->command?->info(sprintf(
            'Also: %d employers awaiting approval, %d inactive employers, %d walk-in jobseekers, %d in-house schedules, %d calendar entries, %d imported reports, %d posting edits, %d notifications.',
            count($this->preApproval),
            count($this->dormant),
            count(array_filter($this->jobseekers, fn($js) => $js['walkin'])),
            count($this->manifest['inhouse_schedules']),
            count($this->manifest['office_events']),
            count($this->manifest['jf_imported']) + count($this->manifest['jv_imported']),
            count($this->manifest['activity_logs']),
            count($this->manifest['announcements'])
        ));
    }

    // ──────────────────────────────────────────────────────────────
    // EMPLOYERS
    // ──────────────────────────────────────────────────────────────

    private function seedEmployers(): void
    {
        $industries = array_keys(self::LOCAL_TRACKS);

        for ($i = 0; $i < self::EMPLOYERS_LOCAL; $i++) {
            $this->makeEmployer($industries[$i % count($industries)], false);
        }
        for ($i = 0; $i < self::EMPLOYERS_OVERSEAS; $i++) {
            $this->makeEmployer(self::OVERSEAS, true);
        }
    }

    private function makeEmployer(string $industry, bool $overseas, string $state = 'approved'): void
    {
        // An employer still waiting on the desk registered only days ago; an
        // older unapproved account would already have been chased.
        $created  = $state === 'approved'
            ? $this->between(Carbon::create(2025, 11, 3), Carbon::create(2026, 6, 30))
            : $this->between($this->today->copy()->subDays(18), $this->today->copy()->subDay());
        $company  = $this->uniqueCompanyName($industry);
        $male     = $this->chance(55);
        $first    = $this->pick($male ? self::FIRST_MALE : self::FIRST_FEMALE);
        $last     = $this->pick(self::SURNAMES);
        $barangay = $this->pick(self::BARANGAYS);
        $email    = $this->uniqueEmail(preg_replace('/[^a-z0-9]+/', '', strtolower($company)));

        $userId = DB::table('users')->insertGetId([
            'name'                 => "$first $last",
            'email'                => $email,
            'email_verified_at'    => $created,
            'password'             => $this->password,
            'must_change_password' => 0,
            'role'                 => 'company',
            'status'               => 'approved',
            'created_at'           => $created,
            'updated_at'           => $created,
        ]);
        $this->manifest['users'][] = $userId;

        $employerId = DB::table('employer_nsrp_registrations')->insertGetId([
            'user_id'               => $userId,
            'company_name'          => $company,
            'contact_person'        => "$first $last",
            'position_title'        => $this->pick(['Human Resources Manager', 'Human Resources Officer', 'Recruitment Head', 'Operations Manager', 'Branch Manager']),
            'mobile_number'         => $this->mobile(),
            'sms_opt_in'            => 0,
            'employer_type'         => $overseas ? 'Overseas Recruitment Agency' : ($this->chance(85) ? 'Direct Hire' : 'Local Recruitment Agency'),
            'trade_name'            => $this->chance(60) ? trim(preg_replace('/\b(Inc|Corp|Co)\.?$/', '', $company)) : null,
            'tin'                   => $this->uniqueTin(),
            'tin_type'              => $this->chance(85) ? 'main' : 'branch',
            'total_workforce'       => $this->pick(['micro', 'small', 'small', 'medium', 'medium', 'large']),
            'line_of_business'      => $overseas ? 'Overseas recruitment and manpower deployment' : $industry,
            'industry_group'        => $industry,
            'est_barangay'          => $barangay,
            'est_city_municipality' => 'City of Cagayan De Oro',
            'est_province'          => 'Misamis Oriental',
            'contact_title'         => $male ? 'Mr.' : 'Ms.',
            'telephone_no'          => sprintf('(088) 8%02d-%04d', mt_rand(50, 59), mt_rand(1000, 9999)),
            'certification_agreed'  => 1,
            'certification_date'    => $created->toDateString(),
            'is_overseas'           => $overseas ? 1 : 0,
            'created_at'            => $created,
            'updated_at'            => $created,
        ]);
        $this->manifest['employers'][] = $employerId;

        $reviewedAt = $created->copy()->addDays(mt_rand(1, 5));
        if ($state !== 'approved' && $reviewedAt->gte(now())) {
            $reviewedAt = now()->subHour();
        }
        $documents = ['business_permit', 'sec_dti', 'company_profile', 'no_pending_case_certificate', 'vacancy_posting'];

        $requirement = [
            'user_id'                                => $employerId,
            'business_permit'                        => self::DOCUMENT,
            'business_permit_year'                   => 2026,
            'business_permit_expires_at'             => '2026-12-31',
            'sec_dti'                                => self::DOCUMENT,
            'sec_dti_expires_at'                     => '2028-12-31',
            'company_profile'                        => self::DOCUMENT,
            'company_profile_expires_at'             => '2027-12-31',
            'no_pending_case_certificate'            => self::DOCUMENT,
            'no_pending_case_certificate_expires_at' => '2027-06-30',
            'vacancy_posting'                        => self::DOCUMENT,
            'vacancy_posting_expires_at'             => '2027-06-30',
            'created_at'                             => $created,
        ];

        $requirement = match ($state) {
            'approved' => array_merge($requirement, [
                'reviewed_by'     => $overseas ? self::STAFF_SRA : self::STAFF_JOB_VACANCY,
                'status'          => 'approved',
                'approved_fields' => json_encode($documents),
                'updated_at'      => $reviewedAt,
            ]),
            'rejected' => array_merge($requirement, [
                'reviewed_by'                => $overseas ? self::STAFF_SRA : self::STAFF_JOB_VACANCY,
                'status'                     => 'rejected',
                'business_permit_year'       => 2025,
                'business_permit_expires_at' => '2025-12-31',
                'remarks'                    => 'The business permit uploaded is for 2025. Please upload your 2026 business permit.',
                'rejected_fields'            => json_encode(['business_permit']),
                'approved_fields'            => json_encode(array_slice($documents, 1)),
                'updated_at'                 => $reviewedAt,
            ]),
            default => array_merge($requirement, ['status' => 'pending', 'updated_at' => $created]),
        };

        $requirementId = null;
        if ($state !== 'none') {
            $requirementId = DB::table('employer_requirements')->insertGetId($requirement);
            $this->manifest['requirements'][] = $requirementId;
        }

        $row = [
            'id'          => $employerId,
            'user'        => $userId,
            'contact'     => "$first $last",
            'requirement' => $requirementId,
            'industry'    => $industry,
            'overseas'    => $overseas,
            'created'     => $state === 'approved' ? $reviewedAt : $created,
            'registered'  => $created,
            'reviewed'    => $reviewedAt,
            'name'        => $company,
            'barangay'    => $barangay,
            'dormant'     => false,
            'state'       => $state,
        ];

        if ($state === 'approved') {
            $this->employers[] = $row;
        } else {
            $this->preApproval[] = $row;
        }
    }

    // ──────────────────────────────────────────────────────────────
    // JOBSEEKERS
    // ──────────────────────────────────────────────────────────────

    private function seedJobseekers(): void
    {
        for ($i = 0; $i < self::JOBSEEKERS; $i++) {
            $this->makeJobseeker();
        }
    }

    private function makeJobseeker(bool $walkIn = false): void
    {
        $roll = mt_rand(1, 100);
        $type = $roll <= 77 ? 'local' : ($roll <= 91 ? 'overseas' : 'both');

        if ($type === 'overseas') {
            $titles    = array_keys(self::OVERSEAS_TRACKS);
            $primary   = $this->pick($titles);
            $profile   = self::OVERSEAS_TRACKS[$primary];
            $preferred = $this->withOthers($primary, $titles, 2);
        } else {
            $industry  = $this->pick(array_keys(self::LOCAL_TRACKS));
            $titles    = array_keys(self::LOCAL_TRACKS[$industry]);
            $primary   = $this->pick($titles);
            $profile   = self::LOCAL_TRACKS[$industry][$primary];
            $preferred = $this->withOthers($primary, $titles, 2);

            if ($type === 'both') {
                $preferred   = array_slice($preferred, 0, 2);
                $preferred[] = $this->pick(array_keys(self::OVERSEAS_TRACKS));
            }
        }

        [$course, $certification, $education, $skills, , , $licence] = $profile;

        $male     = $this->chance(50);
        $first    = $this->pick($male ? self::FIRST_MALE : self::FIRST_FEMALE);
        $middle   = $this->pick(self::SURNAMES);
        $surname  = $this->pick(self::SURNAMES);
        $age      = mt_rand($education === 'Tertiary / College' ? 21 : 19, 44);
        $dob      = $this->today->copy()->subYears($age)->subDays(mt_rand(1, 360));
        $created  = $walkIn
            ? $this->between($this->today->copy()->subDays(60), $this->today->copy()->subDays(2))
            : $this->between(Carbon::create(2025, 12, 1), $this->today->copy()->subDays(5));
        $barangay = $this->pick(self::BARANGAYS);
        $email    = $this->uniqueEmail(strtolower(preg_replace('/[^a-z]+/i', '', $first . $surname)));
        $street   = $this->chance(50)
            ? sprintf('Purok %d, Zone %d', mt_rand(1, 9), mt_rand(1, 8))
            : sprintf('Blk %d Lot %d', mt_rand(1, 30), mt_rand(1, 40));

        // A walk-in is registered at the counter by staff; no account is made.
        $userId = null;
        if (!$walkIn) {
            $userId = DB::table('users')->insertGetId([
                'name'                 => "$first $surname",
                'middle_name'          => $middle,
                'email'                => $email,
                'email_verified_at'    => $created,
                'password'             => $this->password,
                'must_change_password' => 0,
                'role'                 => 'jobseeker',
                'status'               => 'approved',
                'created_at'           => $created,
                'updated_at'           => $created,
            ]);
            $this->manifest['users'][] = $userId;
        }

        $registrationId = DB::table('jobseeker_registrations')->insertGetId([
            'user_id'                => $userId,
            'is_walk_in'             => $walkIn ? 1 : 0,
            'surname'                => $surname,
            'first_name'             => $first,
            'middle_name'            => $middle,
            'date_of_birth'          => $dob->toDateString(),
            'age'                    => $age,
            'sex'                    => $male ? 'Male' : 'Female',
            'religion'               => $this->weighted(['Roman Catholic' => 80, 'Born Again Christian' => 8, 'Iglesia ni Cristo' => 7, 'Islam' => 5]),
            'civil_status'           => $this->weighted(['Single' => 65, 'Married' => 30, 'Widowed' => 5]),
            'house_street'           => $street,
            'barangay'               => $barangay,
            'municipality_city'      => 'City of Cagayan De Oro',
            'province'               => 'Misamis Oriental',
            'perm_house_street'      => $street,
            'perm_barangay'          => $barangay,
            'perm_municipality_city' => 'City of Cagayan De Oro',
            'perm_province'          => 'Misamis Oriental',
            'same_as_permanent'      => 1,
            'tin'                    => $this->chance(60) ? $this->uniqueTin() : null,
            'disabilities'           => json_encode($this->chance(4) ? [$this->pick(['Hearing', 'Physical', 'Visual'])] : []),
            'height'                 => (string) mt_rand($male ? 160 : 150, $male ? 180 : 168),
            'weight'                 => (string) mt_rand($male ? 55 : 45, $male ? 85 : 70),
            'contact_number'         => $this->mobile(),
            'sms_opt_in'             => 0,
            'reg_email'              => $email,
            'created_at'             => $created,
            'updated_at'             => $created,
        ]);
        $this->manifest['registrations'][] = $registrationId;

        $experiences = $this->chance(45) ? mt_rand(1, 2) : 0;
        $isEmployed  = $experiences > 0 && $this->chance(20);

        $nsrpId = DB::table('jobseeker_nsrp_registrations')->insertGetId([
            'jobseeker_registration_id' => $registrationId,
            'employment_type'           => $isEmployed ? 'employed' : 'unemployed',
            'type'                      => $type,
            'employed_sub_type'         => $isEmployed ? 'wage_employed' : null,
            'months_looking'            => $isEmployed ? null : (string) mt_rand(1, 12),
            'unemployed_reason'         => $isEmployed ? null : ($experiences ? $this->pick(['finished_contract', 'resigned']) : 'new_entrant'),
            'is_4ps'                    => $is4ps = $this->chance(12) ? 1 : 0,
            'household_id'              => $is4ps ? sprintf('%012d', mt_rand(100000000, 999999999)) : null,
            'work_type'                 => $this->chance(90) ? 'full_time' : 'part_time',
            'preferred_occupations'     => json_encode(array_values($preferred)),
            'local_locations'           => json_encode(['Cagayan de Oro City', $this->pick(['Iligan City', 'El Salvador City', 'Opol', 'Tagoloan', 'Valencia City'])]),
            'overseas_locations'        => json_encode($type === 'local' ? [] : [$this->pick(['United Arab Emirates', 'Saudi Arabia', 'Qatar', 'Taiwan', 'Kuwait'])]),
            'language_proficiency'      => json_encode([
                'English'  => ['read' => '1', 'write' => '1', 'speak' => '1', 'understand' => '1'],
                'Filipino' => ['read' => '1', 'write' => '1', 'speak' => '1', 'understand' => '1'],
            ]),
            'currently_in_school'       => 0,
            'education'                 => json_encode($this->education($education, $course, $barangay)),
            'trainings'                 => json_encode($certification && $this->chance(75) ? [[
                'course'      => $certification,
                'hours'       => (string) mt_rand(160, 480),
                'duration'    => $this->monthRange($created->copy()->subMonths(mt_rand(6, 30)), mt_rand(1, 4)),
                'institution' => 'TESDA Regional Training Center - Cagayan de Oro',
            ]] : []),
            'other_skills'              => json_encode(array_values(array_unique(array_merge(
                $skills,
                $this->chance(35) ? [$this->pick(['Computer Literate', 'Driver', 'Photography', 'Domestic Chores'])] : []
            )))),
            'certification_agreed'      => 1,
            'certification_date'        => $created->toDateString(),
            'status'                    => 'submitted',
            'created_at'                => $created,
            'updated_at'                => $created,
        ]);
        $this->manifest['nsrp'][] = $nsrpId;

        $cursor = $created->copy()->subMonths(mt_rand(6, 12));
        for ($e = 0; $e < $experiences; $e++) {
            $months  = mt_rand(6, 30);
            $from    = $cursor->copy()->subMonths($months);
            $current = $isEmployed && $e === 0;

            $this->manifest['work_experiences'][] = DB::table('jobseeker_work_experiences')->insertGetId([
                'jobseeker_nsrp_registration_id' => $nsrpId,
                'company_name'                   => $this->pick(self::NAME_PREFIX) . ' ' . $this->pick(['Enterprises', 'Trading', 'Services', 'Corporation', 'Company']),
                'position'                       => $this->pick($preferred),
                'industry'                       => 'Cagayan de Oro City',
                'date_from'                      => $from->format('m/Y'),
                'date_to'                        => $current ? null : $cursor->format('m/Y'),
                'is_current'                     => $current ? 1 : 0,
                'employment_status'              => $this->pick(['Permanent', 'Contractual', 'Contractual', 'Probationary', 'Part-time']),
                'created_at'                     => $created,
                'updated_at'                     => $created,
            ]);
            $cursor = $from->copy()->subMonths(mt_rand(1, 6));
        }

        $graduated = $education !== 'Tertiary / College' || $this->completedCollege;
        if ($licence && $graduated && $this->chance(85)) {
            $this->manifest['certifications'][] = DB::table('jobseeker_certifications')->insertGetId([
                'jobseeker_nsrp_registration_id' => $nsrpId,
                'category'                       => 'license',
                'name'                           => $licence,
                'date_taken'                     => $created->copy()->subMonths(mt_rand(3, 24))->toDateString(),
                'valid_until'                    => $created->copy()->addYears(3)->toDateString(),
                'created_at'                     => $created,
                'updated_at'                     => $created,
            ]);
        }
        if ($this->chance(8)) {
            $this->manifest['certifications'][] = DB::table('jobseeker_certifications')->insertGetId([
                'jobseeker_nsrp_registration_id' => $nsrpId,
                'category'                       => 'eligibility',
                'name'                           => 'Career Service Sub-Professional',
                'date_taken'                     => $created->copy()->subYears(mt_rand(1, 5))->toDateString(),
                'created_at'                     => $created,
                'updated_at'                     => $created,
            ]);
        }

        $this->jobseekers[] = [
            'id'        => $registrationId,
            'type'      => $type,
            'sex'       => $male ? 'Male' : 'Female',
            'preferred' => array_map('strtolower', $preferred),
            'created'   => $created,
            'user'      => $userId,
            'name'      => "$first $surname",
            'walkin'    => $walkIn,
            'nsrp'      => $nsrpId,
        ];
    }

    private bool $completedCollege = true;

    private function education(string $level, ?string $course, string $barangay): array
    {
        $empty = ['school_name' => null, 'course' => null, 'year_graduated' => null, 'level_reached' => null, 'year_last_attended' => null];
        $hs    = $this->pick(self::HIGH_SCHOOLS);

        $e = [
            'Elementary'                             => ['school_name' => "$barangay Elementary School", 'course' => 'N/A', 'year_graduated' => 'Graduated', 'level_reached' => null, 'year_last_attended' => null],
            'Junior High School'                     => ['school_name' => $hs, 'course' => 'N/A', 'year_graduated' => 'Completer / Grade 10', 'level_reached' => null, 'year_last_attended' => null],
            'Senior High School'                     => $empty,
            'Tertiary / College'                     => $empty + ['course_other' => null],
            'Graduate Studies/Post-graduate/Masters' => $empty,
        ];

        $this->completedCollege = true;

        if ($level === 'High School') {
            if ($this->chance(50)) {
                $e['Senior High School'] = ['school_name' => $hs, 'course' => 'TVL - Technical-Vocational-Livelihood', 'year_graduated' => 'SHS Graduated', 'level_reached' => null, 'year_last_attended' => null];
            }
            return $e;
        }

        $strand = $course
            ? $this->pick(['STEM - Science, Technology, Engineering and Mathematics', 'ABM - Accountancy, Business and Management', 'HUMSS - Humanities and Social Sciences', 'GAS - General Academic Strand'])
            : 'TVL - Technical-Vocational-Livelihood';
        $e['Senior High School'] = ['school_name' => $hs, 'course' => $strand, 'year_graduated' => 'SHS Graduated', 'level_reached' => null, 'year_last_attended' => null];

        if ($level === 'Tertiary / College') {
            $graduated = $this->chance(82);
            $this->completedCollege = $graduated;
            $e['Tertiary / College'] = [
                'school_name'        => $this->pick(self::COLLEGES),
                'course'             => $course,
                'course_other'       => null,
                'year_graduated'     => $graduated ? 'Fresh Graduated' : '3rd Year',
                'level_reached'      => $graduated ? null : '3rd Year',
                'year_last_attended' => $graduated ? null : (string) mt_rand(2020, 2025),
            ];
        } elseif ($this->chance(25)) {
            $e['Tertiary / College'] = [
                'school_name'        => $this->pick(self::COLLEGES),
                'course'             => 'BS Business Administration',
                'course_other'       => null,
                'year_graduated'     => '2nd Year',
                'level_reached'      => '2nd Year',
                'year_last_attended' => (string) mt_rand(2018, 2024),
            ];
        }

        return $e;
    }

    // ──────────────────────────────────────────────────────────────
    // PAST JOB FAIRS — event, confirmed employers, attached vacancies,
    // jobseeker sign-ups with attendance, and the hires that followed
    // ──────────────────────────────────────────────────────────────

    private function seedPastJobFairs(): void
    {
        foreach (self::PAST_FAIRS as [$title, $date, $venue, $address, $cater, $employerCount]) {
            $eventDate = Carbon::parse($date);
            $withOverseas = in_array('overseas', $cater, true);

            $eligible = array_values(array_filter(
                $this->employers,
                fn($emp) => $emp['created']->lt($eventDate->copy()->subDays(40))
            ));
            shuffle($eligible);

            $local    = array_values(array_filter($eligible, fn($emp) => !$emp['overseas']));
            $overseas = array_values(array_filter($eligible, fn($emp) => $emp['overseas']));
            $chosen   = array_slice($local, 0, $withOverseas ? $employerCount - 2 : $employerCount);
            if ($withOverseas) {
                $chosen = array_merge($chosen, array_slice($overseas, 0, 2));
            }
            if (!$chosen) {
                continue;
            }

            $eventId = DB::table('job_fair_events')->insertGetId([
                'created_by'            => self::STAFF_JOB_FAIR,
                'title'                 => $title,
                'event_date'            => $eventDate->toDateString(),
                'event_time'            => '08:00:00',
                'venue'                 => $venue,
                'venue_address'         => $address,
                'cater'                 => json_encode($cater),
                'target_industries'     => null,
                'pwd_only'              => 0,
                'jobseekers_invited_at' => $eventDate->copy()->subDays(14)->setTime(9, 0),
                'employer_capacity'     => $employerCount,
                'local_capacity'        => $withOverseas ? $employerCount - 2 : $employerCount,
                'overseas_capacity'     => $withOverseas ? 2 : null,
                'status'                => 'completed',
                'created_at'            => $eventDate->copy()->subDays(45),
                'updated_at'            => $eventDate->copy()->addDay(),
            ]);
            $this->manifest['events'][] = $eventId;

            $fair = ['id' => $eventId, 'date' => $eventDate, 'overseas' => $withOverseas];
            $fairJobs = [];
            $participantOf = [];

            foreach ($chosen as $emp) {
                $invited   = $eventDate->copy()->subDays(40)->setTime(9, 0);
                $responded = $invited->copy()->addDays(mt_rand(1, 5))->setTime(mt_rand(8, 16), mt_rand(0, 59));

                $participantId = DB::table('job_fair_participants')->insertGetId([
                    'job_fair_id'         => $eventId,
                    'employer_id'         => $emp['id'],
                    'confirmation_status' => 'confirmed',
                    'invited_at'          => $invited,
                    'invited_by'          => $emp['overseas'] ? self::STAFF_SRA : self::STAFF_JOB_FAIR,
                    'sra_decided_by'      => $emp['overseas'] ? self::STAFF_SRA : null,
                    'sra_decided_at'      => $emp['overseas'] ? $responded->copy()->addDays(2) : null,
                    'sra_decision_note'   => $emp['overseas'] ? 'Brought to the fair.' : null,
                    'responded_at'        => $responded,
                    'created_at'          => $invited,
                    'updated_at'          => $responded,
                ]);
                $this->manifest['participants'][] = $participantId;
                $participantOf[$emp['id']] = $participantId;

                for ($k = 0, $n = mt_rand(1, 2); $k < $n; $k++) {
                    $job = $this->makePosting($emp, $fair);
                    $fairJobs[] = $job;

                    $this->manifest['employment_requests'][] = DB::table('job_fair_employment_requests')->insertGetId([
                        'job_fair_id' => $eventId,
                        'employer_id' => $emp['id'],
                        'job_id'      => $job['id'],
                        'created_at'  => $eventDate->copy()->subDays(5),
                        'updated_at'  => $eventDate->copy()->subDays(5),
                    ]);
                }
            }

            // Sign-ups and attendance.
            $wanted = $withOverseas ? ['local', 'overseas', 'both'] : ['local', 'both'];
            $pool = array_values(array_filter(
                $this->jobseekers,
                fn($js) => in_array($js['type'], $wanted, true) && $js['created']->lt($eventDate->copy()->subDays(3))
            ));
            shuffle($pool);
            $pool = array_slice($pool, 0, mt_rand(45, 80));

            $slip = 0;
            $totals = [];

            foreach ($pool as $js) {
                $slip++;
                $signedUp = $eventDate->copy()->subDays(mt_rand(1, 14))->setTime(mt_rand(8, 20), mt_rand(0, 59));
                $attended = $this->chance(82);

                $this->manifest['fair_registrations'][] = DB::table('job_fair_registrations')->insertGetId([
                    'job_fair_id'            => $eventId,
                    'user_id'                => $js['id'],
                    'slip_number'            => 'JF' . $eventId . '-' . str_pad((string) $slip, 4, '0', STR_PAD_LEFT),
                    'is_early'               => $signedUp->diffInDays($eventDate, false) >= 3 ? 1 : 0,
                    'is_attended'            => $attended ? 1 : 0,
                    'attended_at'            => $attended ? $eventDate->copy()->setTime(mt_rand(8, 11), mt_rand(0, 59)) : null,
                    'attendance_notified_at' => $eventDate->copy()->subDay()->setTime(18, 0),
                    'created_at'             => $signedUp,
                    'updated_at'             => $attended ? $eventDate : $signedUp,
                ]);

                if (!$attended) {
                    continue;
                }

                $visible = array_values(array_filter($fairJobs, fn($job) => $this->canSee($js, $job)));
                if (!$visible) {
                    continue;
                }

                $preferredJobs = array_values(array_filter($visible, fn($job) => in_array(strtolower($job['title']), $js['preferred'], true)));
                $targets = [];
                for ($a = 0, $n = $this->chance(70) ? 1 : 2; $a < $n; $a++) {
                    if ($preferredJobs && $this->chance(80)) {
                        $targets[] = $this->pick($preferredJobs);
                    } elseif ($this->chance(50)) {
                        $targets[] = $this->pick($visible);
                    }
                }

                foreach ($this->uniqueJobs($targets) as $job) {
                    $applied = $eventDate->copy()->setTime(mt_rand(9, 15), mt_rand(0, 59));
                    $row = $this->apply($js, $job, $applied);

                    $key = $job['employer'];
                    $totals[$key] ??= ['interviewed' => 0, 'male' => 0, 'female' => 0, 'hired' => 0];
                    $totals[$key]['interviewed']++;
                    $totals[$key][$js['sex'] === 'Male' ? 'male' : 'female']++;
                    if ($row['status'] === 'hired') {
                        $totals[$key]['hired']++;
                    }
                }
            }

            foreach ($participantOf as $employerId => $participantId) {
                $slots = array_sum(array_map(
                    fn($job) => $job['employer'] === $employerId ? $job['slots'] : 0,
                    $fairJobs
                ));
                $t = $totals[$employerId] ?? ['interviewed' => 0, 'male' => 0, 'female' => 0, 'hired' => 0];

                DB::table('job_fair_participants')->where('job_fair_participants_id', $participantId)->update([
                    'total_vacancies'   => $slots,
                    'total_interviewed' => $t['interviewed'],
                    'male_count'        => $t['male'],
                    'female_count'      => $t['female'],
                    'total_hired'       => $t['hired'],
                ]);
            }
        }
    }

    // ──────────────────────────────────────────────────────────────
    // REGULAR POSTINGS — company interview and in-house
    // ──────────────────────────────────────────────────────────────

    private function seedRegularPostings(): void
    {
        foreach ($this->employers as $emp) {
            for ($k = 0, $n = mt_rand(2, 5); $k < $n; $k++) {
                $this->makePosting($emp, null);
            }
        }
    }

    /**
     * The inactivity sweep reads each employer's newest posting. Without one
     * inside the last month, every seeded employer would be warned the next
     * morning and the demo would open to a wall of inactivity notices.
     */
    private function ensureRecentPosting(): void
    {
        $cutoff = $this->today->copy()->subDays(25);

        foreach ($this->employers as $emp) {
            if (!empty($emp['dormant'])) {
                continue;
            }
            $newest = null;
            foreach ($this->jobs as $job) {
                if ($job['employer'] === $emp['id'] && (!$newest || $job['created']->gt($newest))) {
                    $newest = $job['created'];
                }
            }
            if (!$newest || $newest->lt($cutoff)) {
                $this->makePosting($emp, null, $this->between($this->today->copy()->subDays(20), $this->today->copy()->subDays(3)));
            }
        }
    }

    private function makePosting(array $emp, ?array $fair, ?Carbon $createdOverride = null, ?string $forceSchedule = null, bool $forcePending = false): array
    {
        $tracks = $emp['overseas'] ? self::OVERSEAS_TRACKS : self::LOCAL_TRACKS[$emp['industry']];
        $title  = $this->pick(array_keys($tracks));
        [$course, $certification, $education, $skills, $salaryMin, $salaryMax, $licence] = $tracks[$title];

        $upcoming = $fair['upcoming'] ?? false;

        if ($fair) {
            $schedule = 'job_fair';
            // A fair still weeks away: posted in the days after confirming.
            $created  = $upcoming
                ? $this->between($this->today->copy()->subDays(10), $this->today->copy()->subDay())
                : $fair['date']->copy()->subDays(mt_rand(20, 35))->setTime(mt_rand(8, 16), mt_rand(0, 59));
        } else {
            $schedule = $forceSchedule ?? ($this->chance(58) ? 'company_interview' : 'inhouse');
            // An employer that later went inactive stopped posting months ago.
            $latest   = $this->today->copy()->subDays(!empty($emp['dormant']) ? 100 : 2);
            $created  = $createdOverride
                ?? $this->between($emp['created']->copy()->addDays(3), $latest);
        }

        // Never more than Job::MAX_DEADLINE_MONTHS after it was posted.
        $deadline = $fair
            ? $fair['date']->copy()
            : $created->copy()->addDays(mt_rand(30, 59))->startOfDay();

        $meetDate = $fair ? $fair['date']->copy() : $this->weekday($created->copy()->addDays(mt_rand(7, 14))->startOfDay());
        $meetEnd  = $schedule === 'inhouse' ? $this->weekday($meetDate->copy()->addDays(mt_rand(0, 2))) : null;

        // A future in-house booking at the PESO Office would hold real days on
        // the live calendar. Upcoming ones are held at the employer's own place.
        $venueType = null;
        $venueAddress = null;
        if ($schedule === 'inhouse') {
            if ($meetDate->gt($this->today)) {
                $venueType = 'other';
                $venueAddress = $emp['name'] . ' office, ' . $emp['barangay'] . ', Cagayan de Oro City';
            } else {
                $venueType = 'peso_office';
            }
        }

        // Only what a desk must approve can be waiting: an in-house booking,
        // and an overseas agency's company interview. A job fair vacancy
        // waits for its fair's cutoff.
        $needsApproval = $schedule === 'inhouse' || ($schedule === 'company_interview' && $emp['overseas']);
        $pending = $forcePending || $upcoming
            || (!$fair && !$createdOverride && $needsApproval && empty($emp['dormant'])
                && $created->gt($this->today->copy()->subDays(7)) && $this->chance(30));
        // Saved open, as the form saves it; the posting status keeps a waiting
        // one hidden.
        $closed  = ($fair && !$upcoming) || (!$pending && $deadline->lt($this->today));

        if ($emp['overseas']) {
            $location = $this->pick(array_keys(self::OVERSEAS_PLACES));
            $salary   = self::OVERSEAS_PLACES[$location];
        } else {
            $location = 'Cagayan de Oro City';
            $salary   = number_format((int) (round(mt_rand($salaryMin, $salaryMax) / 500) * 500));
        }

        $slots = mt_rand(1, $fair ? 15 : 10);
        $mention = implode(', ', $skills);

        $id = DB::table('job_qualifications')->insertGetId([
            'company_id'            => $emp['id'],
            'title'                 => $title,
            'description'           => "$title for {$emp['name']}. Handles the day-to-day duties of the position, follows company policies and safety standards, keeps accurate records of work done, and reports to the immediate supervisor.",
            'location'              => $location,
            'type'                  => $this->chance(55) ? 'contractual' : 'permanent',
            'industry_group'        => $emp['industry'],
            'slots'                 => $slots,
            'sex_preference'        => 'Any',
            'civil_status'          => 'Any',
            'religion'              => 'Any',
            'other_qualifications'  => "$mention. Must be willing to follow the company's work schedule and safety rules.",
            'accepts_disability'    => ($pwd = $this->chance(30)) ? 'yes' : 'no',
            'disability_types'      => $pwd ? json_encode([$this->pick(['Hearing', 'Speech', 'Physical'])]) : null,
            'course_major'          => $course && $this->chance(60) ? $course : null,
            'license'               => $licence && $this->chance(70) ? $licence : null,
            'certification'         => $certification && $this->chance(50) ? $certification : null,
            'language'              => 'English, Filipino',
            'preferred_residence'   => 'Cagayan de Oro City',
            'accepts_programs'      => json_encode($this->chance(40) ? ['PESO', 'SPES'] : ['PESO']),
            'experience_months'     => $this->chance(35) ? $this->pick([6, 12]) : null,
            'education_required'    => $education,
            'deadline'              => $deadline->toDateString(),
            'status'                => $closed ? 'closed' : 'open',
            'schedule_type'         => $schedule,
            'requested_job_fair_id' => $fair['id'] ?? null,
            'preferred_date'        => $fair ? null : $meetDate->toDateString(),
            'preferred_date_end'    => $meetEnd?->toDateString(),
            'confirmed_date'        => $schedule === 'inhouse' && !$pending ? $meetDate->toDateString() : null,
            'venue_type'            => $venueType,
            'venue_address'         => $venueAddress,
            'posting_status'        => $pending ? 'pending' : 'approved',
            'salary'                => $salary,
            'created_at'            => $created,
            'updated_at'            => $created,
        ]);
        $this->manifest['jobs'][] = $id;

        $job = [
            'id'       => $id,
            'employer' => $emp['id'],
            'title'    => $title,
            'schedule' => $schedule,
            'overseas' => $emp['overseas'],
            'created'  => $created,
            'deadline' => $deadline,
            'date'     => $meetDate,
            'slots'    => $slots,
            'pending'  => $pending,
            'fair'     => (bool) $fair,
            'dormant'  => !empty($emp['dormant']),
        ];
        $this->jobs[] = $job;
        $this->hired[$id] = 0;

        return $job;
    }

    private function seedRegularApplications(): void
    {
        $open = array_values(array_filter($this->jobs, fn($job) => !$job['fair'] && !$job['pending']));

        foreach ($this->jobseekers as $js) {
            $visible = array_values(array_filter(
                $open,
                fn($job) => $this->canSee($js, $job) && $job['created']->lt($this->today)
            ));
            if (!$visible) {
                continue;
            }

            $preferredJobs = array_values(array_filter($visible, fn($job) => in_array(strtolower($job['title']), $js['preferred'], true)));
            $targets = [];
            for ($a = 0, $n = mt_rand(1, 4); $a < $n; $a++) {
                $targets[] = ($preferredJobs && $this->chance(72)) ? $this->pick($preferredJobs) : $this->pick($visible);
            }

            foreach ($this->uniqueJobs($targets) as $job) {
                $from = ($job['created']->gt($js['created']) ? $job['created'] : $js['created'])->copy()->addHours(2);
                $until = collect([$job['date']->copy()->subDay(), $job['deadline'], $this->today])->min();

                if ($until->lte($from)) {
                    continue;
                }

                // between() picks a random office hour, which on the posting's own
                // day can land before the posting existed.
                $applied = $this->between($from, $until);
                $this->apply($js, $job, $applied->lt($from) ? $from->copy() : $applied);
            }
        }
    }

    // ──────────────────────────────────────────────────────────────
    // ONE APPLICATION — real match score, then a believable outcome
    // ──────────────────────────────────────────────────────────────

    private function apply(array $js, array $job, Carbon $applied, ?string $forceStatus = null): array
    {
        $model = $this->jobModels[$job['id']] ??= Job::with('company')->find($job['id']);
        $match = (float) ($this->matcher->computeMatchBreakdownByRegistrationId($js['id'], $model)['percentage'] ?? 0);

        $decisionDay = $job['date'];
        $status   = 'pending';
        $hiredAt  = null;
        $start    = null;
        $updated  = $applied;

        if ($decisionDay->gt($this->today)) {
            $status = $this->chance(60) ? 'pending' : 'reviewed';
        } else {
            $roll = mt_rand(1, 100);
            if ($match >= 75) {
                $status = $roll <= 40 ? 'hired' : ($roll <= 65 ? 'waiting' : ($roll <= 80 ? 'rejected' : 'qualified'));
            } elseif ($match >= 50) {
                $status = $roll <= 12 ? 'hired' : ($roll <= 45 ? 'waiting' : ($roll <= 85 ? 'rejected' : 'reviewed'));
            } else {
                $status = $roll <= 70 ? 'rejected' : 'reviewed';
            }

            if ($forceStatus) {
                $status = $forceStatus;
            }

            if ($status === 'hired' && $this->hired[$job['id']] >= $job['slots']) {
                $status = 'waiting';
            }

            // Job fair hires are recorded inside the 30-day decision window.
            $latestDecision = $decisionDay->copy()->addDays($job['fair'] ? mt_rand(0, 20) : mt_rand(0, 5));
            $decided = ($latestDecision->gt($this->today) ? $this->today->copy() : $latestDecision)->setTime(mt_rand(9, 16), mt_rand(0, 59));
            $updated = $decided->lt($applied) ? $applied->copy() : $decided;

            if ($status === 'hired') {
                $this->hired[$job['id']]++;
                $hiredAt = $updated;
                $start   = $this->weekday($hiredAt->copy()->addDays(mt_rand(3, 14))->startOfDay());
            }
        }

        $this->applied[$job['id'] . ':' . $js['id']] = true;

        $id = DB::table('job_matching')->insertGetId([
            'job_id'                            => $job['id'],
            'jobseeker_id'                      => $js['id'],
            'status'                            => $status,
            'hired_at'                          => $hiredAt,
            'start_date'                        => $start?->toDateString(),
            'match_percentage'                  => round($match, 2),
            'inhouse_participation'             => $job['schedule'] === 'inhouse' ? 'accepted' : null,
            'inhouse_participation_notified_at' => $job['schedule'] === 'inhouse' ? $applied->copy()->addDay() : null,
            'company_interview_participation'   => $job['schedule'] === 'company_interview' ? 'accepted' : null,
            'created_at'                        => $applied,
            'updated_at'                        => $updated,
        ]);
        $this->manifest['applications'][] = $id;

        return ['id' => $id, 'status' => $status];
    }

    // ──────────────────────────────────────────────────────────────
    // ACCOUNTS IN EVERY STATE A DESK MEETS — waiting, refused, inactive,
    // walk-in, switched off
    // ──────────────────────────────────────────────────────────────

    /**
     * Pick the employers that end up switched off for inactivity.
     *
     * Chosen before any posting exists, so every posting they make is old and
     * the warnings that followed can be dated from the last one.
     */
    private function markDormantEmployers(): void
    {
        foreach ([0 => 3, 1 => 1] as $overseas => $count) {
            $candidates = array_keys(array_filter(
                $this->employers,
                fn($emp) => $emp['overseas'] === (bool) $overseas
                    && $emp['created']->lt(Carbon::create(2026, 3, 1))
            ));
            foreach (array_slice($candidates, 0, $count) as $index) {
                $this->employers[$index]['dormant'] = true;
            }
        }
    }

    /** Employers the desk has not approved yet: waiting, never uploaded, or sent back. */
    private function seedPreApprovalEmployers(): void
    {
        foreach ([
            ['Education', false, 'pending'],
            ['Manufacturing', false, 'pending'],
            ['Construction', false, 'none'],
            ['Hotel and Restaurants', false, 'rejected'],
            [self::OVERSEAS, true, 'pending'],
            [self::OVERSEAS, true, 'rejected'],
        ] as [$industry, $overseas, $state]) {
            $this->makeEmployer($industry, $overseas, $state);
        }
    }

    /** People registered at the counter by staff, with no account of their own. */
    private function seedWalkInJobseekers(): void
    {
        for ($i = 0; $i < 12; $i++) {
            $this->makeJobseeker(true);
        }
    }

    /** A former desk officer whose account the admin switched off. */
    private function seedDeactivatedStaff(): void
    {
        $created = Carbon::create(2026, 2, 2, 9, 0);
        $offAt   = Carbon::create(2026, 7, 31, 16, 30);

        $userId = DB::table('users')->insertGetId([
            'name'                 => 'Maricel Dagatan',
            'email'                => $this->uniqueEmail('maricel.dagatan'),
            'email_verified_at'    => $created,
            'password'             => $this->password,
            'must_change_password' => 0,
            'role'                 => 'staff',
            'status'               => 'deactivated',
            'created_at'           => $created,
            'updated_at'           => $offAt,
        ]);
        $this->manifest['users'][] = $userId;

        $this->manifest['staff'][] = DB::table('staff')->insertGetId([
            'user_id'    => $userId,
            'staff_role' => 'lra',
            'first_name' => 'Maricel',
            'last_name'  => 'Dagatan',
            'phone'      => $this->mobile(),
            'created_at' => $created,
            'updated_at' => $offAt,
        ]);
    }

    /**
     * Postings a desk still has to decide on: every in-house booking and an
     * overseas agency's company interview.
     *
     * No waiting job fair posting is made here. The nightly cutoff sweeps every
     * waiting job fair posting onto any fair inside its posting window, and a
     * fair can be days away — it would put these in front of real jobseekers.
     */
    private function seedPendingPostings(): void
    {
        $active = array_values(array_filter($this->employers, fn($emp) => empty($emp['dormant'])));
        shuffle($active);

        $local    = array_values(array_filter($active, fn($emp) => !$emp['overseas']));
        $overseas = array_values(array_filter($active, fn($emp) => $emp['overseas']));

        foreach ([[$overseas, 'company_interview', 3], [$local, 'inhouse', 3], [$overseas, 'inhouse', 2]] as [$pool, $schedule, $count]) {
            foreach (array_slice($pool, 0, $count) as $emp) {
                $created = $this->between($this->today->copy()->subDays(6), $this->today->copy()->subDay());
                $this->makePosting($emp, null, $created, $schedule, true);
            }
        }
    }

    /** Every approved employer has placed at least one person, so no hires tab opens empty. */
    private function ensureEveryEmployerHired(): void
    {
        $hiresBy = [];
        foreach ($this->jobs as $job) {
            $hiresBy[$job['employer']] = ($hiresBy[$job['employer']] ?? 0) + $this->hired[$job['id']];
        }

        foreach ($this->employers as $emp) {
            if (($hiresBy[$emp['id']] ?? 0) > 0) {
                continue;
            }

            $jobs = array_values(array_filter($this->jobs, fn($job) =>
                $job['employer'] === $emp['id'] && !$job['pending'] && !$job['fair']
                && $job['date']->lt($this->today->copy()->subDays(3))
                && $this->hired[$job['id']] < $job['slots']
            ));
            usort($jobs, fn($a, $b) => $b['date'] <=> $a['date']);

            foreach ($jobs as $job) {
                $pool = array_values(array_filter($this->jobseekers, fn($js) =>
                    $this->canSee($js, $job)
                    && $js['created']->lt($job['date']->copy()->subDays(2))
                    && !isset($this->applied[$job['id'] . ':' . $js['id']])
                ));
                if (!$pool) {
                    continue;
                }

                $preferred = array_values(array_filter($pool, fn($js) => in_array(strtolower($job['title']), $js['preferred'], true)));
                $js = $this->pick($preferred ?: $pool);

                $from  = ($job['created']->gt($js['created']) ? $job['created'] : $js['created'])->copy()->addHours(2);
                $until = $job['deadline']->lt($job['date']) ? $job['deadline']->copy() : $job['date']->copy()->subDay();
                if ($until->lte($from)) {
                    continue;
                }

                $applied = $this->between($from, $until);
                $this->apply($js, $job, $applied->lt($from) ? $from->copy() : $applied, 'hired');
                break;
            }
        }
    }

    /**
     * Walk the chosen employers down the inactivity ladder: two warnings, the
     * grace period, an answer or silence, and staff switching them off.
     */
    private function applyDormancy(): void
    {
        $answers = [
            ['still_hiring', 'We are still hiring for our production and warehouse lines. Our HR officer was on leave, so no new vacancy was posted. We will post again this month.'],
            ['paused', 'Hiring is on hold until our new branch in Opol opens next quarter. We will post vacancies once the branch is ready.'],
            [null, null],
        ];
        $turn = 0;

        foreach ($this->employers as $emp) {
            if (empty($emp['dormant'])) {
                continue;
            }

            $lastPosted = $emp['created']->copy();
            foreach ($this->jobs as $job) {
                if ($job['employer'] === $emp['id'] && $job['created']->gt($lastPosted)) {
                    $lastPosted = $job['created']->copy();
                }
            }

            [$status, $response] = $emp['overseas']
                ? ['closed', 'Our DMW licence renewal is still being processed, so we cannot deploy workers for now.']
                : $answers[$turn++ % count($answers)];

            $first     = $lastPosted->copy()->addMonth()->addDay()->setTime(7, 30);
            $second    = $lastPosted->copy()->addMonths(2)->addDay()->setTime(7, 30);
            $prompted  = $second->copy()->addDays(7)->setTime(7, 45);
            $responded = $status ? $second->copy()->addDays(mt_rand(1, 4))->setTime(mt_rand(9, 16), mt_rand(0, 59)) : null;
            $dormantAt = ($responded ? $responded->copy()->addDays(mt_rand(3, 6)) : $prompted->copy()->addDays(mt_rand(1, 3)))
                ->setTime(mt_rand(9, 15), mt_rand(0, 59));
            if ($dormantAt->gte($this->today)) {
                $dormantAt = $this->today->copy()->subDay()->setTime(10, 0);
            }

            DB::table('employer_nsrp_registrations')->where('employer_nsrp_registrations_id', $emp['id'])->update([
                'inactivity_notified_at'         => $first,
                'inactivity_second_notified_at'  => $second,
                'inactivity_disable_prompted_at' => $responded ? null : $prompted,
                'inactivity_responded_at'        => $responded,
                'inactivity_status'              => $status,
                'inactivity_response'            => $response,
                'dormant_at'                     => $dormantAt,
                'updated_at'                     => $dormantAt,
            ]);
            DB::table('users')->where('users_id', $emp['user'])->update(['status' => 'dormant', 'updated_at' => $dormantAt]);
            DB::table('job_qualifications')->where('company_id', $emp['id'])->where('status', 'open')
                ->update(['status' => 'closed', 'dormant_closed_at' => $dormantAt]);

            $this->dormant[] = $emp + [
                'prompted'  => $prompted,
                'responded' => $responded,
                'status'    => $status,
                'response'  => $response,
                'dormantAt' => $dormantAt,
            ];
        }
    }

    /** Papers about to run out, and one employer whose permit already did. */
    private function applyRequirementStates(): void
    {
        $active   = array_values(array_filter($this->employers, fn($emp) => empty($emp['dormant'])));
        $local    = array_values(array_filter($active, fn($emp) => !$emp['overseas']));
        $overseas = array_values(array_filter($active, fn($emp) => $emp['overseas']));

        foreach ([[$local[0] ?? null, 'vacancy_posting', 4], [$local[1] ?? null, 'no_pending_case_certificate', 6], [$overseas[0] ?? null, 'company_profile', 2]] as [$emp, $field, $days]) {
            if (!$emp) {
                continue;
            }
            $expires = $this->today->copy()->addDays($days);
            $warned  = $expires->copy()->subDays(7)->setTime(7, 0);

            DB::table('employer_requirements')->where('employer_requirements_id', $emp['requirement'])->update([
                "{$field}_expires_at"         => $expires->toDateString(),
                "{$field}_expiry_notified_at" => $warned,
            ]);
            $this->requirementNotices[] = ['employer' => $emp, 'kind' => 'expiring', 'field' => $field, 'expires' => $expires, 'at' => $warned];
        }

        // The permit ran out and the grace months passed with no new copy.
        // Dated from the employer's own last posting, so nothing was posted
        // after the papers lapsed.
        $emp = $local[2] ?? null;
        if (!$emp) {
            return;
        }

        $lastPosted = $emp['created']->copy();
        foreach ($this->jobs as $job) {
            if ($job['employer'] === $emp['id'] && $job['created']->gt($lastPosted)) {
                $lastPosted = $job['created']->copy();
            }
        }

        $expiredAt = $lastPosted->copy()->addDays(2)->setTime(7, 15);
        if ($expiredAt->gt(now())) {
            $expiredAt = $this->today->copy()->setTime(7, 15);
        }
        $expires = $expiredAt->copy()->subMonths((int) config('peso.employer.business_permit_grace_months', 3))->subDay()->startOfDay();

        DB::table('employer_requirements')->where('employer_requirements_id', $emp['requirement'])->update([
            'status'                             => 'expired',
            'business_permit_year'               => $expires->year,
            'business_permit_expires_at'         => $expires->toDateString(),
            'business_permit_expiry_notified_at' => $expires->copy()->subDays(7)->setTime(7, 0),
            'business_permit_grace_notified_at'  => $expires->copy()->addDay()->setTime(7, 0),
            'rejected_fields'                    => json_encode(['business_permit']),
            'updated_at'                         => $expiredAt,
        ]);

        $this->expiredEmployerId = $emp['id'];
        $this->requirementNotices[] = ['employer' => $emp, 'kind' => 'expired', 'field' => 'business_permit', 'expires' => $expires, 'at' => $expiredAt];
    }

    // ──────────────────────────────────────────────────────────────
    // THE NEXT JOB FAIR — invitations in every answer, vacancies waiting
    // for their cutoff, and jobseekers already signed up
    // ──────────────────────────────────────────────────────────────

    private function seedUpcomingJobFair(): void
    {
        $date    = $this->weekday($this->today->copy()->addDays(39));
        $created = $this->today->copy()->subDays(20)->setTime(10, 0);
        $invited = $created->copy()->addDay()->setTime(9, 0);
        $title   = 'PESO CDO Career Fair ' . $date->year;
        $venue   = 'Centrio Mall Activity Center';

        // Waiting job fair postings are swept onto any earlier fair that asks
        // for their industry once its window opens. Vacancies for this fair
        // are only posted in industries no earlier fair wants, so none of them
        // can be taken by another event first.
        $blocked   = [];
        $blocksAll = false;
        $earlier   = DB::table('job_fair_events')->where('status', '!=', 'completed')
            ->whereDate('event_date', '>=', $this->today)->whereDate('event_date', '<', $date)->get();
        foreach ($earlier as $event) {
            if (!in_array('local', (array) json_decode($event->cater ?? '[]', true), true)) {
                continue;
            }
            $targets = json_decode($event->target_industries ?? 'null', true);
            if (empty($targets)) {
                $blocksAll = true;
                break;
            }
            $blocked = array_merge($blocked, $targets);
        }
        $safe = fn($emp) => !$blocksAll && !in_array($emp['industry'], $blocked, true);

        $eventId = DB::table('job_fair_events')->insertGetId([
            'created_by'        => self::STAFF_JOB_FAIR,
            'title'             => $title,
            'event_date'        => $date->toDateString(),
            'event_time'        => '08:00:00',
            'venue'             => $venue,
            'venue_address'     => 'Claro M. Recto Ave., Lapasan, Cagayan de Oro City',
            'cater'             => json_encode(['local', 'overseas']),
            'target_industries' => null,
            'pwd_only'          => 0,
            'employer_capacity' => 10,
            'local_capacity'    => 8,
            'overseas_capacity' => 2,
            'status'            => 'upcoming',
            'created_at'        => $created,
            'updated_at'        => $created,
        ]);
        $this->manifest['events'][] = $eventId;

        $active = array_values(array_filter($this->employers, fn($emp) =>
            empty($emp['dormant']) && $emp['id'] !== $this->expiredEmployerId));
        shuffle($active);
        $local    = array_values(array_filter($active, fn($emp) => !$emp['overseas']));
        $local    = array_merge(array_values(array_filter($local, $safe)), array_values(array_filter($local, fn($emp) => !$safe($emp))));
        $overseas = array_values(array_filter($active, fn($emp) => $emp['overseas']));

        $plan = [];
        foreach (array_slice($local, 0, 5) as $emp) {
            $plan[] = [$emp, 'confirmed'];
        }
        foreach (array_slice($local, 5, 2) as $emp) {
            $plan[] = [$emp, 'pending'];
        }
        foreach ([7 => 'declined', 8 => 'expired'] as $index => $state) {
            if (isset($local[$index])) {
                $plan[] = [$local[$index], $state];
            }
        }
        foreach (['confirmed', 'accepted', 'not_selected'] as $index => $state) {
            if (isset($overseas[$index])) {
                $plan[] = [$overseas[$index], $state];
            }
        }

        $participants = [];
        $confirmedAt  = [];

        foreach ($plan as [$emp, $state]) {
            $isOverseas = $emp['overseas'];
            $inviteAt   = match ($state) {
                'pending'  => $this->today->copy()->subDays(3)->setTime(9, 0),
                'accepted' => $this->today->copy()->subDays(4)->setTime(9, 0),
                default    => $invited->copy(),
            };
            $responded = match ($state) {
                'confirmed', 'declined', 'not_selected' => $this->between($invited->copy()->addDay(), $invited->copy()->addDays(5)),
                'accepted'                              => $this->between($inviteAt->copy()->addDay(), $this->today->copy()->subDay()),
                default                                 => null,
            };
            $sraAt = $isOverseas && in_array($state, ['confirmed', 'not_selected'], true)
                ? $responded->copy()->addDay()->setTime(14, 0)
                : null;
            $updated = match ($state) {
                'expired' => $invited->copy()->addDays(8)->setTime(6, 5),
                'pending' => $inviteAt->copy(),
                default   => $sraAt ?? $responded,
            };

            $id = DB::table('job_fair_participants')->insertGetId([
                'job_fair_id'         => $eventId,
                'employer_id'         => $emp['id'],
                'confirmation_status' => $state,
                'invited_at'          => $inviteAt,
                'invited_by'          => $isOverseas ? self::STAFF_SRA : self::STAFF_JOB_FAIR,
                'sra_decided_by'      => $sraAt ? self::STAFF_SRA : null,
                'sra_decided_at'      => $sraAt,
                'sra_decision_note'   => $sraAt ? ($state === 'confirmed'
                    ? 'Approved by the PESO head. The agency has an active job order.'
                    : 'Only two overseas slots are open; agencies with active job orders were brought first.') : null,
                'responded_at'        => $responded,
                'created_at'          => $inviteAt,
                'updated_at'          => $updated,
            ]);
            $this->manifest['participants'][] = $id;

            if ($state === 'confirmed') {
                $confirmedAt[] = $sraAt ?? $responded;
            }
            $participants[] = ['employer' => $emp, 'state' => $state, 'invited' => $inviteAt, 'responded' => $responded, 'updated' => $updated];

            if ($state === 'confirmed' && !$isOverseas && $safe($emp)) {
                for ($k = 0, $n = mt_rand(1, 2); $k < $n; $k++) {
                    $this->makePosting($emp, ['id' => $eventId, 'date' => $date, 'overseas' => true, 'upcoming' => true]);
                }
            }
        }

        usort($confirmedAt, fn($a, $b) => $a <=> $b);
        $threshold   = \App\Support\JobFairAudience::threshold();
        $announced   = isset($confirmedAt[$threshold - 1]) ? $confirmedAt[$threshold - 1]->copy()->addHour() : null;
        $registrants = [];

        if ($announced) {
            DB::table('job_fair_events')->where('job_fair_events_id', $eventId)->update(['jobseekers_invited_at' => $announced]);

            $pool = array_values(array_filter($this->jobseekers, fn($js) => !$js['walkin'] && $js['created']->lt($announced)));
            shuffle($pool);

            foreach (array_slice($pool, 0, 36) as $js) {
                $at = $this->between($announced, $this->today->copy()->subDay());
                $registrants[] = [$js, $at->lt($announced) ? $announced->copy()->addHours(2) : $at];
            }
            usort($registrants, fn($a, $b) => $a[1] <=> $b[1]);

            foreach ($registrants as $n => [$js, $at]) {
                $this->manifest['fair_registrations'][] = DB::table('job_fair_registrations')->insertGetId([
                    'job_fair_id' => $eventId,
                    'user_id'     => $js['id'],
                    'slip_number' => 'JF' . $eventId . '-' . str_pad((string) ($n + 1), 4, '0', STR_PAD_LEFT),
                    'is_early'    => $at->diffInDays($date, false) >= 3 ? 1 : 0,
                    'created_at'  => $at,
                    'updated_at'  => $at,
                ]);
            }
        }

        $this->upcoming = compact('eventId', 'date', 'title', 'venue', 'participants', 'announced', 'registrants');
    }

    // ──────────────────────────────────────────────────────────────
    // IN-HOUSE ROOM BOOKINGS, OFFICE CALENDAR, IMPORTED REPORTS, EDITS
    // ──────────────────────────────────────────────────────────────

    private function seedInhouseSchedules(): void
    {
        $active     = array_values(array_filter($this->employers, fn($emp) => empty($emp['dormant'])));
        $monthStart = $this->today->copy()->startOfMonth();

        $plan = [];
        for ($i = 0; $i < 4; $i++) {
            $plan[] = ['accepted', $this->weekdayBetween($monthStart, $this->today->copy()->subDays(2))];
        }
        for ($i = 0; $i < 18; $i++) {
            $plan[] = ['accepted', $this->weekdayBetween(Carbon::create(2026, 3, 2), $monthStart->copy()->subDay())];
        }
        for ($i = 0; $i < 3; $i++) {
            $plan[] = ['accepted', $this->weekdayBetween($this->today->copy()->addDays(8), $this->today->copy()->addDays(30))];
        }
        for ($i = 0; $i < 4; $i++) {
            $plan[] = ['pending', $this->weekdayBetween($this->today->copy()->addDays(10), $this->today->copy()->addDays(25))];
        }
        for ($i = 0; $i < 3; $i++) {
            $plan[] = ['rejected', $this->weekdayBetween(Carbon::create(2026, 5, 4), $this->today->copy()->subDays(10))];
        }

        $reasons = [
            'The PESO Office interview room is fully booked on your requested dates. Please request another schedule.',
            'Please post the vacancies for this interview first so we can match jobseekers to them.',
            'Your requested date falls on a local holiday. Please choose another date.',
        ];
        $notes = [
            'We will bring two interviewers from HR.',
            'Applicants should bring a printed resume and a valid ID.',
            'Initial interview and written exam on the same day.',
            null,
        ];
        $rejectTurn = 0;

        foreach ($plan as $i => [$status, $day]) {
            $overseas = $i % 5 === 0;
            $pool = array_values(array_filter($active, fn($emp) =>
                $emp['overseas'] === $overseas && $emp['created']->lt($day->copy()->subDays(21))));
            if (!$pool) {
                continue;
            }
            $emp = $this->pick($pool);

            $created = $status === 'pending'
                ? $this->between($this->today->copy()->subDays(5), $this->today->copy()->subDay())
                : $this->between($day->copy()->subDays(20), $day->copy()->subDays(9));
            if ($created->gte($this->today)) {
                $created = $this->between($this->today->copy()->subDays(6), $this->today->copy()->subDay());
            }

            $reviewed = null;
            if ($status !== 'pending') {
                $reviewed = $created->copy()->addDays(mt_rand(1, 3))->setTime(mt_rand(9, 16), mt_rand(0, 59));
                if ($reviewed->gte($this->today)) {
                    $reviewed = $this->today->copy()->subDay()->setTime(15, 0);
                }
                if ($reviewed->lt($created)) {
                    $reviewed = $created->copy()->addHour();
                }
            }

            // A booking still ahead is held at the employer's own office, so
            // no room at the PESO Office is taken from anyone testing.
            $custom  = $status === 'pending' || $day->gt($this->today);
            $titles  = array_keys($overseas ? self::OVERSEAS_TRACKS : self::LOCAL_TRACKS[$emp['industry']]);
            shuffle($titles);
            $time    = $this->pick(['08:30:00', '09:00:00', '13:00:00']);
            $address = $emp['name'] . ' office, ' . $emp['barangay'] . ', Cagayan de Oro City';
            $reason  = $status === 'rejected' ? $reasons[$rejectTurn++ % count($reasons)] : null;

            $id = DB::table('inhouse_schedules')->insertGetId([
                'employer_id'        => $emp['id'],
                'reviewed_by'        => $reviewed ? ($overseas ? self::STAFF_SRA : self::STAFF_LRA) : null,
                'preferred_date'     => $day->copy()->subDays($status === 'accepted' ? mt_rand(0, 1) : 0)->toDateString(),
                'preferred_date_end' => $day->copy()->addDays(mt_rand(0, 2))->toDateString(),
                'preferred_time'     => $time,
                'num_applicants'     => mt_rand(8, 25),
                'venue_type'         => $custom ? 'custom' : 'peso_office',
                'venue_address'      => $custom ? $address : null,
                'job_positions'      => json_encode(array_slice($titles, 0, mt_rand(1, 3))),
                'notes'              => $this->pick($notes),
                'status'             => $status,
                'rejection_reason'   => $reason,
                'confirmed_date'     => $status === 'accepted' ? $day->toDateString() : null,
                'confirmed_time'     => $status === 'accepted' ? $time : null,
                'created_at'         => $created,
                'updated_at'         => $reviewed ?? $created,
            ]);
            $this->manifest['inhouse_schedules'][] = $id;

            if ($status === 'accepted') {
                $lastJoin = ($day->lt($this->today) ? $day->copy()->subDay() : $this->today->copy()->subDay())->setTime(17, 0);
                $seekers  = array_values(array_filter($this->jobseekers, fn($js) =>
                    !$js['walkin'] && $this->canSee($js, ['overseas' => $overseas]) && $js['created']->lt($reviewed)));
                shuffle($seekers);

                foreach (array_slice($seekers, 0, mt_rand(4, 12)) as $js) {
                    $joined = $this->between($reviewed, $lastJoin);
                    if ($joined->lt($reviewed)) {
                        $joined = $reviewed->copy()->addHour();
                    }
                    $this->manifest['inhouse_participants'][] = DB::table('inhouse_participants')->insertGetId([
                        'inhouse_schedule_id' => $id,
                        'jobseeker_id'        => $js['id'],
                        'joined_at'           => $joined,
                        'created_at'          => $joined,
                        'updated_at'          => $joined,
                    ]);
                }
            }

            $this->schedules[] = [
                'id' => $id, 'employer' => $emp, 'status' => $status, 'day' => $day, 'time' => $time,
                'venue' => $custom ? $address : 'PESO Office', 'created' => $created, 'reviewed' => $reviewed,
                'reason' => $reason,
            ];
        }
    }

    private function seedOfficeCalendar(): void
    {
        $adminId = DB::table('users')->where('role', 'admin')->value('users_id');

        // Counted from today so the calendar always shows a recent month and
        // something ahead. The one future day is a whole-day training: the
        // office takes no in-house booking on it.
        $entries = [
            [-70, 'Monthly Staff Meeting', 'meeting', 0, '08:00', '10:00', 'PESO Conference Room', 'Review of last month\'s placements.'],
            [-58, 'SPES Orientation Seminar', 'training', 1, '08:00', '17:00', 'City Hall Session Hall', 'Orientation for SPES beneficiaries and partner employers.'],
            [-37, 'Monthly Staff Meeting', 'meeting', 0, '08:00', '10:00', 'PESO Conference Room', 'Job fair follow-up and employer visits.'],
            [-23, 'Barangay Employment Caravan - Bugo', 'activity', 0, '08:00', '15:00', 'Bugo Barangay Hall', 'Walk-in NSRP registration and job matching for residents.'],
            [-9, 'Monthly Staff Meeting', 'meeting', 0, '08:00', '10:00', 'PESO Conference Room', 'Planning for the next career fair.'],
            [17, 'PESO Staff Development Training', 'training', 0, '08:00', '17:00', 'City Hall Training Room', 'Whole-day training for all desks.'],
        ];

        foreach ($entries as [$offset, $title, $type, $extraDays, $start, $end, $location, $notes]) {
            $day = $this->weekday($this->today->copy()->addDays($offset));
            $at  = $day->copy()->subDays(mt_rand(7, 14))->setTime(mt_rand(9, 16), mt_rand(0, 59));

            $this->manifest['office_events'][] = DB::table('office_calendar_events')->insertGetId([
                'title'      => $title,
                'type'       => $type,
                'start_date' => $day->toDateString(),
                'end_date'   => $extraDays ? $this->weekday($day->copy()->addDays($extraDays))->toDateString() : null,
                'start_time' => $start,
                'end_time'   => $end,
                'location'   => $location,
                'notes'      => $notes,
                'created_by' => $adminId,
                'created_at' => $at,
                'updated_at' => $at,
            ]);
        }
    }

    /**
     * The desks' own spreadsheets, as if uploaded. Built from the seeded rows,
     * so what an imported sheet lists matches what the system holds.
     */
    private function seedImportedReports(): void
    {
        $names = array_column($this->employers, 'name', 'id');

        foreach ([2, 1] as $back) {
            $month = $this->today->copy()->startOfMonth()->subMonths($back);
            $jobs  = array_values(array_filter($this->jobs, fn($job) =>
                !$job['overseas'] && !$job['fair'] && !$job['pending'] && $job['created']->isSameMonth($month)));
            if (!$jobs) {
                continue;
            }
            usort($jobs, fn($a, $b) => strcmp($a['title'], $b['title']));

            $rows = [];
            foreach ($jobs as $i => $job) {
                $row = DB::table('job_qualifications')->where('job_qualifications_id', $job['id'])
                    ->first(['slots', 'education_required', 'experience_months']);
                $rows[] = [
                    (string) ($i + 1), strtoupper($job['title']), (string) $row->slots, 'None', 'M/F', 'None',
                    $row->education_required ?? 'None',
                    $row->experience_months ? $row->experience_months . ' months' : 'None',
                    strtoupper($names[$job['employer']] ?? 'None'),
                ];
            }

            $at = $month->copy()->addMonth()->addDays(mt_rand(2, 6))->setTime(mt_rand(9, 16), mt_rand(0, 59));
            if ($at->gte($this->today)) {
                $at = $this->today->copy()->subDay()->setTime(10, 0);
            }

            $this->manifest['jv_imported'][] = DB::table('job_vacancy_imported_reports')->insertGetId([
                'uploaded_by'       => self::STAFF_JOB_VACANCY,
                'title'             => 'Job Vacancies Solicited - ' . $month->format('F Y'),
                'period'            => $month->format('Y-m'),
                'original_filename' => 'JV Solicited ' . $month->format('F Y') . '.xlsx',
                'headers'           => json_encode(['No.', 'Job Title', 'No. of Vacancies', 'Age', 'Sex', 'Civil Status', 'Educational Attainment', 'Work Experience', 'Employer Name']),
                'rows'              => json_encode($rows),
                'row_count'         => count($rows),
                'created_at'        => $at,
                'updated_at'        => $at,
            ]);
        }

        $events = DB::table('job_fair_events')->whereIn('job_fair_events_id', $this->manifest['events'])
            ->where('status', 'completed')->get();

        foreach ($events as $event) {
            $registrations = DB::table('job_fair_registrations as r')
                ->join('jobseeker_registrations as j', 'j.jobseeker_registrations_id', '=', 'r.user_id')
                ->where('r.job_fair_id', $event->job_fair_events_id)
                ->orderBy('r.slip_number')
                ->get(['j.surname', 'j.first_name', 'j.sex', 'j.age', 'j.barangay', 'r.is_attended']);

            $rows = [];
            foreach ($registrations as $i => $reg) {
                $rows[] = [(string) ($i + 1), strtoupper($reg->surname), strtoupper($reg->first_name), $reg->sex === 'Male' ? 'M' : 'F', (string) $reg->age, $reg->barangay, $reg->is_attended ? 'Yes' : 'No'];
            }

            $at = Carbon::parse($event->event_date)->addDays(2)->setTime(10, 0);

            $this->manifest['jf_imported'][] = DB::table('job_fair_imported_reports')->insertGetId([
                'job_fair_id'       => $event->job_fair_events_id,
                'uploaded_by'       => self::STAFF_JOB_FAIR,
                'title'             => 'Registrant Masterlist',
                'original_filename' => 'Masterlist - ' . $event->title . '.xlsx',
                'headers'           => json_encode(['No.', 'Surname', 'First Name', 'Sex', 'Age', 'Barangay', 'Attended']),
                'rows'              => json_encode($rows),
                'row_count'         => count($rows),
                'created_at'        => $at,
                'updated_at'        => $at,
            ]);
        }
    }

    /** Employers going back into a live posting to add slots or raise the pay. */
    private function seedActivityLogs(): void
    {
        $employers = array_column($this->employers, null, 'id');
        $pool = array_values(array_filter($this->jobs, fn($job) =>
            !$job['fair'] && !$job['pending'] && !$job['dormant'] && $job['created']->lt($this->today->copy()->subDays(7))));
        shuffle($pool);

        foreach (array_slice($pool, 0, 16) as $job) {
            $emp   = $employers[$job['employer']];
            $row   = DB::table('job_qualifications')->where('job_qualifications_id', $job['id'])->first(['slots', 'salary']);
            $until = $job['deadline']->lt($this->today) ? $job['deadline']->copy() : $this->today->copy()->subDay();
            $at    = $this->between($job['created']->copy()->addDay(), $until);
            if ($at->lte($job['created'])) {
                $at = $job['created']->copy()->addHour();
            }

            $changes = [];
            $update  = [];
            if ($job['overseas'] || $this->chance(60)) {
                $to = $row->slots + mt_rand(1, 3);
                $changes['slots'] = ['label' => 'Slots', 'from' => (string) $row->slots, 'to' => (string) $to];
                $update['slots']  = $to;
            }
            $pay = (int) str_replace(',', '', (string) $row->salary);
            if (!$job['overseas'] && $pay > 0 && (!$changes || $this->chance(50))) {
                $to = number_format($pay + $this->pick([500, 1000, 1500]));
                $changes['salary'] = ['label' => 'Salary', 'from' => $row->salary, 'to' => $to];
                $update['salary']  = $to;
            }
            if (!$changes) {
                continue;
            }

            DB::table('job_qualifications')->where('job_qualifications_id', $job['id'])->update($update + ['updated_at' => $at]);

            $labels = array_map(fn($change) => lcfirst($change['label']), array_values($changes));

            $this->manifest['activity_logs'][] = DB::table('job_activity_logs')->insertGetId([
                'job_id'        => $job['id'],
                'actor_user_id' => $emp['user'],
                'actor_name'    => $emp['contact'],
                'action'        => 'qualifications_updated',
                'summary'       => 'Updated ' . implode(' and ', $labels) . '.',
                'changes'       => json_encode($changes),
                'created_at'    => $at,
                'updated_at'    => $at,
            ]);
        }
    }

    // ──────────────────────────────────────────────────────────────
    // NOTIFICATIONS — the bell each event would have rung, on the day it
    // happened. Old ones mostly read, the last few days mostly not.
    // ──────────────────────────────────────────────────────────────

    private function seedNotifications(): void
    {
        $staff     = fn(string $role) => $this->staffIds[$role] ?? [];
        $seekers   = array_column($this->jobseekers, null, 'id');
        $employers = array_column($this->employers, null, 'id');
        $date      = fn(Carbon $d) => $d->format('M d, Y');
        $toStaff   = function (array $ids, ...$args) {
            foreach ($ids as $id) {
                $this->notify(['staff_id' => $id], ...$args);
            }
        };

        // ── Registration and papers ──
        foreach ($this->employers as $emp) {
            $this->notify(['employer_id' => $emp['id']], 'requirements_approved', 'Requirements Approved ✅',
                'Your submitted requirements have been approved by PESO staff. You can now request in-house interviews and post job vacancies.',
                'employer_requirement', $emp['requirement'], $emp['created']);
        }

        foreach ($this->preApproval as $emp) {
            $owner = $emp['overseas'] ? $staff('sra') : $staff('job_vacancy');
            $toStaff($owner, 'employer_registered', 'New Employer Registration 🏢',
                $emp['name'] . ' has registered as a ' . ($emp['overseas'] ? 'overseas' : 'local') . ' employer. Please review their profile.',
                'employer_registration', $emp['user'], $emp['registered']);

            if ($emp['state'] === 'none') {
                continue;
            }
            $toStaff($owner, 'requirements_submitted', 'New Requirements Submitted 📋',
                $emp['name'] . ' has submitted their requirements for review.',
                'employer_requirement', $emp['requirement'], $emp['registered']->copy()->addHour());

            if ($emp['state'] === 'rejected') {
                $this->notify(['employer_id' => $emp['id']], 'requirements_rejected', 'Requirements Rejected ❌',
                    'Please resubmit the following document(s): ' . \App\Models\EmployerRequirement::DOCUMENT_LABELS['business_permit']
                    . '. Reason: The business permit uploaded is for 2025. Please upload your 2026 business permit.',
                    'employer_requirement', $emp['requirement'], $emp['reviewed']);
            }
        }

        foreach ($this->requirementNotices as $notice) {
            $label = \App\Models\EmployerRequirement::DOCUMENT_LABELS[$notice['field']] ?? $notice['field'];
            if ($notice['kind'] === 'expiring') {
                $this->notify(['employer_id' => $notice['employer']['id']], 'employer_requirement_expiring', 'Document Expiring Soon ⏳',
                    'Your ' . $label . ' expires on ' . $date($notice['expires']) . '. Upload an updated copy before then to keep posting vacancies.',
                    'employer_requirement', $notice['employer']['requirement'], $notice['at']);
            } else {
                $this->notify(['employer_id' => $notice['employer']['id']], 'employer_requirement_expired', 'Document Expired ⛔',
                    'Your ' . $label . ' expired on ' . $date($notice['expires']) . ' and the grace period has ended. Please resubmit an updated copy.',
                    'employer_requirement', $notice['employer']['requirement'], $notice['at']);
            }
        }

        foreach ($this->jobseekers as $js) {
            if ($js['walkin'] || $js['created']->lt($this->today->copy()->subDays(45))) {
                continue;
            }
            $ids = match ($js['type']) {
                'local'    => $staff('lra'),
                'overseas' => $staff('sra'),
                default    => array_merge($staff('lra'), $staff('sra')),
            };
            $toStaff($ids, 'nsrp_submitted', 'New Jobseeker Registration 📋',
                $js['name'] . ' has submitted their NSRP registration form.',
                'jobseeker_registration', $js['id'], $js['created']->copy()->addMinutes(20));
        }

        // ── Postings ──
        foreach ($this->jobs as $job) {
            $emp = $employers[$job['employer']];

            if ($job['created']->gte($this->today->copy()->subDays(30)) && !($job['fair'] && !$job['pending'])) {
                if ($job['schedule'] === 'inhouse') {
                    $venue = $job['date']->gt($this->today) ? $emp['name'] . ' office, ' . $emp['barangay'] . ', Cagayan de Oro City' : 'PESO Office';
                    $toStaff($job['overseas'] ? $staff('sra') : $staff('lra'), 'job_posted_notice', 'In-house Schedule Needs Approval 📅',
                        $emp['name'] . ' requested an in-house interview for "' . $job['title'] . '", ' . $date($job['date']) . ' at ' . $venue
                        . '. Those dates are held while you decide. The vacancy stays hidden from jobseekers until you accept.',
                        'inhouse_schedule', $job['id'], $job['created']);
                } elseif ($job['schedule'] === 'job_fair') {
                    $toStaff(array_merge($staff('job_vacancy'), $staff('job_fair')), 'job_posted_notice', 'New Job Fair Posting 🎪',
                        $emp['name'] . ' posted "' . $job['title'] . '" for job fair use. It will go live on '
                        . $date($job['deadline']->copy()->subDays(\App\Support\JobFairPostingWindow::daysBefore())) . ', '
                        . \App\Support\JobFairPostingWindow::daysBefore() . ' days before the fair.',
                        'job', $job['id'], $job['created']);
                } else {
                    $toStaff($job['overseas'] ? $staff('sra') : $staff('job_vacancy'), 'job_posted_notice',
                        $job['overseas'] ? 'Company Interview Needs Approval 💼' : 'New Job Posting 💼',
                        $emp['name'] . ' posted "' . $job['title'] . '"'
                        . ($job['overseas'] ? ' for a company interview. It stays hidden from jobseekers until you approve it.' : '. It is live now.'),
                        'job', $job['id'], $job['created']);
                }
            }

            $needsApproval = $job['schedule'] === 'inhouse' || ($job['schedule'] === 'company_interview' && $job['overseas']);
            if ($needsApproval && !$job['pending']) {
                $this->notify(['employer_id' => $emp['id']], 'job_approved', 'Job Posting Approved ✅',
                    'Your job posting "' . $job['title'] . '" has been approved and is now live.',
                    'job', $job['id'], $job['created']->copy()->addHours(mt_rand(3, 20)));
            }

            if (!$job['fair'] && !$job['pending'] && !$job['dormant'] && $job['deadline']->lt($this->today)) {
                $this->notify(['employer_id' => $emp['id']], 'job_posting_expired', 'Job Posting Expired ⏳',
                    'Your posting for "' . $job['title'] . '" reached its deadline of ' . $date($job['deadline'])
                    . ' and is now closed. Post it again if you are still hiring.',
                    'job', $job['id'], $job['deadline']->copy()->addDay()->setTime(0, 15));
            }
        }

        // ── Applications ──
        $statusNotice = [
            'hired'    => ['Application Update — Hired! 🎉', 'Congratulations! You have been hired for the position "%s" at %s.'],
            'waiting'  => ['Application Update — On Process ⏳', 'Your application for "%s" at %s is now on process. The employer is taking you forward.'],
            'rejected' => ['Application Update ❌', 'Your application for "%s" at %s was not selected this time.'],
            'reviewed' => ['Application Update — Under Review 👀', 'Your application for "%s" at %s is now being reviewed.'],
        ];

        $applications = DB::table('job_matching as m')
            ->join('job_qualifications as j', 'j.job_qualifications_id', '=', 'm.job_id')
            ->join('employer_nsrp_registrations as e', 'e.employer_nsrp_registrations_id', '=', 'j.company_id')
            ->whereIn('m.job_matching_id', $this->manifest['applications'])
            ->get(['m.job_id', 'm.jobseeker_id', 'm.status', 'm.start_date', 'm.created_at', 'm.updated_at', 'j.title', 'j.company_id', 'e.company_name']);

        foreach ($applications as $app) {
            $js = $seekers[$app->jobseeker_id] ?? null;
            if (!$js) {
                continue;
            }
            $applied = Carbon::parse($app->created_at);

            $this->notify(['employer_id' => $app->company_id], 'new_applicant', 'New Job Applicant 📨',
                $js['name'] . ' applied for "' . $app->title . '".', 'job', $app->job_id, $applied);

            if ($js['walkin']) {
                continue;
            }

            if (in_array(strtolower($app->title), $js['preferred'], true) && $this->chance(60)) {
                $this->notify(['jobseeker_id' => $js['id']], 'job_match', 'Matching Job Vacancy Found! 💼',
                    'A job vacancy matching your preferred position "' . $app->title . '" from ' . $app->company_name . ' is now available. Would you like to apply?',
                    'job', $app->job_id, $applied->copy()->subHours(mt_rand(2, 30)));
            }

            if (isset($statusNotice[$app->status])) {
                [$title, $text] = $statusNotice[$app->status];
                $message = sprintf($text, $app->title, $app->company_name)
                    . ($app->status === 'hired' && $app->start_date ? ' You start work on ' . Carbon::parse($app->start_date)->format('F d, Y') . '.' : '');
                $this->notify(['jobseeker_id' => $js['id']], 'application_status', $title, $message, 'job', $app->job_id, Carbon::parse($app->updated_at));
            }
        }

        // ── The upcoming fair ──
        if ($fair = $this->upcoming) {
            foreach ($fair['participants'] as $p) {
                $emp = $p['employer'];
                $this->notify(['employer_id' => $emp['id']], 'job_fair_invitation', 'Job Fair Invitation 🎉',
                    'You are invited to join ' . $fair['title'] . ' on ' . $date($fair['date']) . ' at ' . $fair['venue']
                    . '. Please confirm by ' . $date($p['invited']->copy()->addDays(7)) . ' — after that the office invites other employers.',
                    'job_fair', $fair['eventId'], $p['invited']);

                if ($p['state'] === 'expired') {
                    $this->notify(['employer_id' => $emp['id']], 'job_fair_invitation_expired', 'Job Fair Invitation Lapsed',
                        'Your invitation to ' . $fair['title'] . ' on ' . $date($fair['date'])
                        . ' was not answered within 7 days, so the office is inviting other employers. You may still confirm if you want to join.',
                        'job_fair', $fair['eventId'], $p['updated']);
                }

                if ($emp['overseas'] && $p['responded'] && $p['state'] !== 'declined') {
                    $toStaff($staff('sra'), 'job_fair_selection_pending', 'Overseas Agency Accepted 📋',
                        $emp['name'] . ' accepted the invitation to ' . $fair['title'] . ' on ' . $date($fair['date']) . '. Choose whether to bring them to this fair.',
                        'job_fair_selection', $fair['eventId'], $p['responded']);
                }

                if ($p['state'] === 'confirmed') {
                    $toStaff($staff('job_fair'), 'job_fair_invitation', 'Employer Confirmed 🎉',
                        $emp['name'] . ' has confirmed participation in ' . $fair['title'] . ' on ' . $date($fair['date']) . '.',
                        'job_fair', $fair['eventId'], $p['updated']);
                }
            }

            if ($fair['announced']) {
                foreach ($fair['registrants'] as [$js, $at]) {
                    $this->notify(['jobseeker_id' => $js['id']], 'job_fair_announced', 'Job Fair Coming 🎪',
                        'PESO is holding ' . $fair['title'] . ' on ' . $date($fair['date']) . ' at 8:00 AM, ' . $fair['venue']
                        . '. Employers are being lined up now — open PESO Events to see the fair and sign up.',
                        'job_fair', $fair['eventId'], $fair['announced']);
                }
            }
        }

        // ── In-house room bookings ──
        foreach ($this->schedules as $s) {
            $emp = $s['employer'];
            $toStaff($emp['overseas'] ? $staff('sra') : $staff('lra'), 'inhouse_request', 'New In-house Interview Request 📅',
                $emp['name'] . ' has requested an in-house interview, available ' . $date($s['day']) . ' at '
                . Carbon::parse($s['time'])->format('h:i A') . ' (' . $s['venue'] . '). Please pick the interview date and accept or reject.',
                'inhouse_schedule', $s['id'], $s['created']);

            if ($s['status'] === 'accepted') {
                $this->notify(['employer_id' => $emp['id']], 'inhouse_accepted', 'In-house Schedule Accepted ✅',
                    'Your in-house interview request has been accepted for ' . $date($s['day']) . ' at '
                    . Carbon::parse($s['time'])->format('h:i A') . ' (' . $s['venue'] . ').',
                    'inhouse_schedule', $s['id'], $s['reviewed']);
            } elseif ($s['status'] === 'rejected') {
                $this->notify(['employer_id' => $emp['id']], 'inhouse_rejected', 'In-house Schedule Rejected ❌',
                    'Your in-house interview request was rejected. Reason: ' . $s['reason'],
                    'inhouse_schedule', $s['id'], $s['reviewed']);
            }
        }

        // ── Inactive employers, as the owning desk heard of them ──
        $answerLabel = ['still_hiring' => 'Still hiring', 'paused' => 'Paused for now', 'closed' => 'Closed down'];
        foreach ($this->dormant as $emp) {
            $owner = $emp['overseas'] ? $staff('sra') : $staff('job_vacancy');
            if ($emp['responded']) {
                $toStaff($owner, 'employer_inactivity_reply', 'Employer answered the status check 📨',
                    $emp['name'] . ' answered the status check: ' . $answerLabel[$emp['status']] . '. "' . $emp['response'] . '"',
                    'employer_inactivity', $emp['id'], $emp['responded']);
            } else {
                $toStaff($owner, 'employer_inactivity_for_disabling', 'Employer ready to be switched off 🔒',
                    $emp['name'] . ' was written to twice and has not answered. The 7-day grace has ended.'
                    . ' Open Employers → Registered Employer, use Update on their row to set the account to inactive.',
                    'employer_inactivity', $emp['id'], $emp['prompted']);
            }
        }
    }

    private function notify(array $recipient, string $type, string $title, string $message, ?string $referenceType, $referenceId, ?Carbon $at): void
    {
        if (!$at || $at->gt(now())) {
            return;
        }

        $old = $at->lt($this->today->copy()->subDays(5));

        $this->manifest['announcements'][] = DB::table('announcements')->insertGetId($recipient + [
            'type'           => $type,
            'title'          => $title,
            'message'        => $message,
            'is_read'        => $old ? ($this->chance(90) ? 1 : 0) : ($this->chance(25) ? 1 : 0),
            'admin_seen_at'  => isset($recipient['staff_id']) && $old ? $at->copy()->addDay() : null,
            'reference_type' => $referenceType,
            'reference_id'   => $referenceId,
            'sms_status'     => 'not_applicable',
            'created_at'     => $at,
            'updated_at'     => $at,
        ]);
    }

    private function weekdayBetween(Carbon $from, Carbon $to): Carbon
    {
        return $this->weekday($this->between($from, $to)->startOfDay());
    }

    /**
     * Every hire goes onto the jobseeker's NSRP work experience, the way the
     * system writes it when an employer marks someone hired.
     *
     * A hire followed by a later application was a job that ended — the person
     * said they were looking for work again before applying. The last hire with
     * nothing after it is the job they still hold, and their form says Employed.
     */
    private function recordPesoEmployment(): void
    {
        $nsrpOf  = array_column($this->jobseekers, 'nsrp', 'id');
        $typeMap = ['permanent' => 'Permanent', 'contractual' => 'Contractual', 'part_time' => 'Part-time'];
        $fields  = ['employment_type', 'employed_sub_type', 'self_employed_specify', 'months_looking', 'unemployed_reason', 'unemployed_other', 'terminated_abroad_country'];

        $applications = DB::table('job_matching as m')
            ->join('job_qualifications as j', 'j.job_qualifications_id', '=', 'm.job_id')
            ->join('employer_nsrp_registrations as e', 'e.employer_nsrp_registrations_id', '=', 'j.company_id')
            ->whereIn('m.job_matching_id', $this->manifest['applications'])
            ->orderBy('m.created_at')
            ->get(['m.job_matching_id', 'm.jobseeker_id', 'm.status', 'm.hired_at', 'm.start_date', 'm.created_at',
                   'j.title', 'j.type', 'j.location', 'e.company_name']);

        foreach ($applications->groupBy('jobseeker_id') as $jobseekerId => $rows) {
            $nsrpId = $nsrpOf[$jobseekerId] ?? null;
            $hires  = $rows->where('status', 'hired')->sortBy('hired_at')->values();
            if (!$nsrpId || $hires->isEmpty()) {
                continue;
            }

            $before  = (array) DB::table('jobseeker_nsrp_registrations')->where('jobseeker_nsrp_registrations_id', $nsrpId)->first($fields);
            $holding = false;

            foreach ($hires as $index => $hire) {
                $hiredAt = Carbon::parse($hire->hired_at);
                $start   = Carbon::parse($hire->start_date ?? $hire->hired_at);
                // The job ended by the time they applied again, or took the next job.
                $next     = $rows->first(fn($row) => Carbon::parse($row->created_at)->gt($hiredAt));
                $nextHire = $hires->get($index + 1);
                $endings  = array_filter([
                    $next ? Carbon::parse($next->created_at) : null,
                    $nextHire ? Carbon::parse($nextHire->hired_at) : null,
                ]);
                $ended    = $endings ? min($endings) : null;
                if ($ended && $ended->lt($start)) {
                    $ended = $start->copy();
                }

                $this->manifest['work_experiences'][] = DB::table('jobseeker_work_experiences')->insertGetId([
                    'jobseeker_nsrp_registration_id' => $nsrpId,
                    'job_matching_id'   => $hire->job_matching_id,
                    'company_name'      => $hire->company_name,
                    'position'          => $hire->title,
                    'industry'          => $hire->location,
                    'date_from'         => $start->format('m/Y'),
                    'date_to'           => $ended ? $ended->format('m/Y') : 'present',
                    'is_current'        => $ended ? 0 : 1,
                    'employment_status' => $typeMap[$hire->type] ?? null,
                    'status_before'     => json_encode($before),
                    'created_at'        => $hiredAt,
                    'updated_at'        => $ended ?? $hiredAt,
                ]);

                $holding = !$ended;
            }

            DB::table('jobseeker_nsrp_registrations')->where('jobseeker_nsrp_registrations_id', $nsrpId)->update($holding
                ? ['employment_type' => 'employed', 'employed_sub_type' => 'wage_employed', 'months_looking' => null, 'unemployed_reason' => null, 'unemployed_other' => null]
                : ['employment_type' => 'unemployed', 'employed_sub_type' => null, 'months_looking' => '0', 'unemployed_reason' => $this->pick(['finished_contract', 'resigned'])]);
        }
    }

    // ──────────────────────────────────────────────────────────────
    // HELPERS
    // ──────────────────────────────────────────────────────────────

    private function canSee(array $js, array $job): bool
    {
        return $job['overseas']
            ? in_array($js['type'], ['overseas', 'both'], true)
            : in_array($js['type'], ['local', 'both'], true);
    }

    private function uniqueJobs(array $jobs): array
    {
        $seen = [];
        foreach ($jobs as $job) {
            $seen[$job['id']] = $job;
        }
        return array_values($seen);
    }

    private function withOthers(string $primary, array $titles, int $extra): array
    {
        $others = array_values(array_diff($titles, [$primary]));
        shuffle($others);
        return array_merge([$primary], array_slice($others, 0, $extra));
    }

    private function uniqueCompanyName(string $industry): string
    {
        do {
            $name = $this->pick(self::NAME_PREFIX) . ' ' . $this->pick(self::NAME_SUFFIX[$industry]);
        } while (isset($this->usedNames[$name])
            || DB::table('employer_nsrp_registrations')->where('company_name', $name)->exists());

        $this->usedNames[$name] = true;
        return $name;
    }

    private function uniqueEmail(string $local): string
    {
        $local = substr($local ?: 'user', 0, 28);
        $email = $local . '@' . self::DOMAIN;
        $n = 1;
        while (isset($this->usedEmails[$email])) {
            $email = $local . (++$n) . '@' . self::DOMAIN;
        }
        $this->usedEmails[$email] = true;
        return $email;
    }

    private function uniqueTin(): string
    {
        do {
            $tin = sprintf('%03d-%03d-%03d-000', mt_rand(100, 999), mt_rand(100, 999), mt_rand(100, 999));
        } while (isset($this->usedTins[$tin]));

        $this->usedTins[$tin] = true;
        return $tin;
    }

    private function mobile(): string
    {
        return '09' . $this->pick(['17', '18', '26', '27', '55', '56', '66', '95', '97', '98']) . sprintf('%07d', mt_rand(0, 9999999));
    }

    private function monthRange(Carbon $from, int $months): string
    {
        return $from->format('m/Y') . ' to ' . $from->copy()->addMonths($months)->format('m/Y');
    }

    private function weekday(Carbon $date): Carbon
    {
        while ($date->isWeekend()) {
            $date->addDay();
        }
        return $date;
    }

    private function between(Carbon $from, Carbon $to): Carbon
    {
        $a = $from->timestamp;
        $b = max($a, $to->timestamp);
        return Carbon::createFromTimestamp(mt_rand($a, $b))->setTime(mt_rand(8, 17), mt_rand(0, 59), mt_rand(0, 59));
    }

    private function chance(int $percent): bool
    {
        return mt_rand(1, 100) <= $percent;
    }

    private function pick(array $items)
    {
        $items = array_values($items);
        return $items[mt_rand(0, count($items) - 1)];
    }

    private function weighted(array $weights): string
    {
        $roll = mt_rand(1, array_sum($weights));
        foreach ($weights as $value => $weight) {
            if (($roll -= $weight) <= 0) {
                return $value;
            }
        }
        return array_key_first($weights);
    }

    private function placeholderPdf(): string
    {
        $text = 'BT /F1 16 Tf 72 720 Td (PESO CDO demo requirement document - sample only) Tj ET';

        return "%PDF-1.4\n"
            . "1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj\n"
            . "2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj\n"
            . "3 0 obj<</Type/Page/Parent 2 0 R/MediaBox[0 0 612 792]/Contents 4 0 R/Resources<</Font<</F1 5 0 R>>>>>>endobj\n"
            . "4 0 obj<</Length " . strlen($text) . ">>stream\n$text\nendstream endobj\n"
            . "5 0 obj<</Type/Font/Subtype/Type1/BaseFont/Helvetica>>endobj\n"
            . "trailer<</Root 1 0 R>>\n%%EOF\n";
    }
}
