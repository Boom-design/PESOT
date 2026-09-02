<?php

    use Illuminate\Support\Facades\Route;
    use App\Http\Controllers\AdminController;
    use App\Http\Controllers\CompanyWebController;
    use App\Http\Controllers\UnifiedAuthController;
    use App\Http\Controllers\StaffWebController;
    use App\Http\Controllers\EmployerRequirementController;
    use App\Http\Controllers\DocumentController;
    use App\Http\Controllers\JobseekerWebController;
    use App\Http\Controllers\ApplicationController;
    use App\Http\Controllers\LandingController;

    // ───────────────────────────────
    // ROOT → Public Landing Page
    // ───────────────────────────────
    Route::get('/', [LandingController::class, 'index'])->name('landing');
    Route::get('/jobs', [LandingController::class, 'allJobs'])->name('jobs.all');

    // ───────────────────────────────
    // UNIFIED LOGIN
    // ───────────────────────────────
    Route::get('/login',  [LandingController::class, 'index'])->name('login');
    Route::post('/login', [UnifiedAuthController::class, 'login'])->name('login.post');
    Route::post('/logout',[UnifiedAuthController::class, 'logout'])->name('logout');

    // ── Ang employer nga gihatagan ug temporary password sa telepono. Ang
    // ── EnsurePasswordChanged nga middleware mo-redirect ngari hangtod mo-ilis
    // ── siya, mao nga kini nga duha ka route lang ang naa sa gawas sa pugos. ──
    Route::middleware('auth')->group(function () {
        Route::get('/password/force-change',  [UnifiedAuthController::class, 'showForceChangePassword'])->name('password.force');
        Route::post('/password/force-change', [UnifiedAuthController::class, 'forceChangePassword'])->name('password.force.update');
    });

    // ───────────────────────────────
    // FORGOT PASSWORD
    // ───────────────────────────────
    Route::get('/forgot-password',  [UnifiedAuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [UnifiedAuthController::class, 'sendResetCode'])->name('password.email');
    // ── Employer recovery: pangita pinaagi sa pangalan sa kompanya kung wala
    // ── kabalo ang bag-ong HR unsa nga email ang gigamit sa daan nga HR. ──
    Route::post('/forgot-password/company',      [UnifiedAuthController::class, 'lookupCompany'])->name('password.company.lookup');
    Route::post('/forgot-password/company/send', [UnifiedAuthController::class, 'sendCodeToCompany'])->name('password.company.send');
    Route::get('/reset-password',   [UnifiedAuthController::class, 'showResetForm'])->name('password.verify');
    Route::post('/reset-password',  [UnifiedAuthController::class, 'resetPassword'])->name('password.update');



    // ───────────────────────────────
    // REGISTER — Jobseeker + Company
    // ───────────────────────────────
    Route::middleware('guest')->group(function () {
        Route::get('/register/jobseeker',  [UnifiedAuthController::class, 'showJobseekerRegister'])->name('register.jobseeker');
        Route::post('/register/jobseeker', [UnifiedAuthController::class, 'registerJobseeker'])->name('register.jobseeker.post');
        Route::get('/register/company',    [UnifiedAuthController::class, 'showCompanyRegister'])->name('register.company');
        Route::post('/register/company',   [UnifiedAuthController::class, 'registerCompany'])->name('register.company.post');
    });

    // ───────────────────────────────
    // PRIVATE DOCUMENTS — usa ka pultahan para sa tanang gi-upload nga
    // dokumento nga dili para sa publiko. Ang file naa na sa `local` nga disk
    // (storage/app/private), nga wala gi-serve sa web server, mao nga ang
    // bugtong dalan pasulod mao kining route — ug ang matag hangyo gi-check
    // kung kinsa ang nangayo. Tan-awa ang DocumentController.
    // ───────────────────────────────
    Route::middleware('auth')->group(function () {
        Route::get('/documents/requirement/{id}/{field}', [DocumentController::class, 'requirement'])
            ->whereNumber('id')->name('documents.requirement');
        Route::get('/documents/certificate/{id}/{index}', [DocumentController::class, 'certificate'])
            ->whereNumber('id')->whereNumber('index')->name('documents.certificate');
    });

    // ───────────────────────────────
    // ADMIN — Auth Routes
    // ───────────────────────────────
    Route::middleware('auth')->group(function () {
        Route::get('/admin/dashboard',                    [AdminController::class, 'dashboard'])->name('admin.dashboard');
        Route::post('/admin/logout',                      [AdminController::class, 'logout'])->name('admin.logout');
        Route::get('/admin/users',                        [AdminController::class, 'manageUsers'])->name('admin.users.manage');
        Route::post('/admin/users/store',                 [AdminController::class, 'storeUser'])->name('admin.users.store');
        Route::put('/admin/users/{id}',                   [AdminController::class, 'updateUser'])->name('admin.users.update');
        // ── Piho ug gi-rekord nga buhat, dili kinatibuk-ang pag-edit: ang 403
        // ── sa updateUser para sa company account magpabilin. Backup ni kung
        // ── wala ang LRA/SRA nga mo-handle sa nitawag nga bag-ong HR. ──
        Route::post('/admin/employers/{id}/recover',      [AdminController::class, 'recoverEmployerAccount'])->name('admin.employers.recover');
        Route::get('/admin/profile',                      [AdminController::class, 'showProfile'])->name('admin.profile');
        Route::post('/admin/profile',                     [AdminController::class, 'updateProfile'])->name('admin.profile.update');
        Route::post('/admin/profile/password',            [AdminController::class, 'changePassword'])->name('admin.profile.password');
        Route::post('/admin/notifications/clear-all',     [AdminController::class, 'clearAllNotifications'])->name('admin.notifications.clearAll');
        Route::post('/admin/notifications/mark-all-read', [AdminController::class, 'markAllNotificationsRead'])->name('admin.notifications.markAllRead');
        Route::post('/admin/notifications/{id}/read',     [AdminController::class, 'markNotificationRead'])->name('admin.notifications.markRead');
        Route::get('/admin/notifications',                [AdminController::class, 'notifications'])->name('admin.notifications.index');
        Route::get('/admin/registrations',                [AdminController::class, 'registrations'])->name('admin.registrations');
        Route::get('/admin/registrations/{id}',           [AdminController::class, 'viewRegistration'])->name('admin.registration.view');
        Route::get('/admin/reports',                      [AdminController::class, 'reports'])->name('admin.reports');
        Route::get('/admin/reports/staff',                [AdminController::class, 'staffReports'])->name('admin.reports.staff');
        Route::get('/admin/reports/staff/job-vacancy',    [AdminController::class, 'staffJobVacancyReports'])->name('admin.reports.staffJobVacancy');
        Route::get('/admin/job-activities',               [AdminController::class, 'jobActivities'])->name('admin.job.activities');

        // ── OFFICE CALENDAR — ang admin ra ang makamarka sa adlaw nga puliki
        // ── ang opisina. Mobasa ang upat ka staff calendar, ug dili na ma-book
        // ── ang maong adlaw. ──
        Route::get('/admin/calendar-data',                [AdminController::class, 'officeCalendarData'])->name('admin.calendar.data');
        Route::post('/admin/calendar',                    [AdminController::class, 'storeOfficeEvent'])->name('admin.calendar.store');
        Route::put('/admin/calendar/{id}',                [AdminController::class, 'updateOfficeEvent'])->name('admin.calendar.update');
        Route::delete('/admin/calendar/{id}',             [AdminController::class, 'deleteOfficeEvent'])->name('admin.calendar.delete');
    });

    // ───────────────────────────────
    // COMPANY — Auth Routes
    // ───────────────────────────────
    Route::middleware('auth')->group(function () {
        Route::post('/company/logout',                       [CompanyWebController::class, 'logout'])->name('company.logout');
        Route::get('/company/dashboard',                     [CompanyWebController::class, 'dashboard'])->name('company.dashboard');
        Route::get('/company/jobs',                          [CompanyWebController::class, 'jobs'])->name('company.jobs');
        Route::get('/company/jobs/create',                   [CompanyWebController::class, 'createJob'])->name('company.jobs.create');
        Route::post('/company/jobs',                         [CompanyWebController::class, 'storeJob'])->name('company.jobs.store');
        Route::get('/company/jobs/{id}/edit',                [CompanyWebController::class, 'editJob'])->name('company.jobs.edit');
        Route::put('/company/jobs/{id}',                     [CompanyWebController::class, 'updateJob'])->name('company.jobs.update');
        Route::delete('/company/jobs/{id}',                  [CompanyWebController::class, 'deleteJob'])->name('company.jobs.delete');
        Route::get('/company/jobs/{id}/applicants',          [CompanyWebController::class, 'applicants'])->name('company.applicants');
        Route::get('/company/jobs/{id}/details',             [CompanyWebController::class, 'jobDetails'])->name('company.jobs.details');
        Route::post('/company/applicants/{id}/status',       [CompanyWebController::class, 'updateApplicantStatus'])->name('company.applicants.status');
        Route::get('/company/profile',                       [CompanyWebController::class, 'showProfile'])->name('company.profile');
        Route::post('/company/profile',                      [CompanyWebController::class, 'updateProfile'])->name('company.profile.update');
        Route::post('/company/profile/password',             [CompanyWebController::class, 'changePassword'])->name('company.profile.password');
        Route::post('/company/jobs/request',                 [CompanyWebController::class, 'requestJob'])->name('company.jobs.request');
        Route::post('/company/jobs/confirm-initial',          [CompanyWebController::class, 'confirmInitialVacancy'])->name('company.jobs.confirmInitial');
        Route::post('/company/notifications/clear-all',      [CompanyWebController::class, 'clearAllNotifications'])->name('company.notifications.clearAll');
        Route::post('/company/applications/{id}/status',     [CompanyWebController::class, 'updateApplicationStatus'])->name('company.applications.status');
        Route::post('/company/notifications/{id}/read',      [CompanyWebController::class, 'markNotificationRead'])->name('company.notifications.markRead');
        Route::post('/company/notifications/mark-all-read',  [CompanyWebController::class, 'markAllNotificationsRead'])->name('company.notifications.markAllRead');
        Route::get('/company/notifications',                  [CompanyWebController::class, 'notifications'])->name('company.notifications.index');
        Route::get('/company/notifications/fetch',            [CompanyWebController::class, 'notificationsFetch'])->name('company.notifications.fetch');

        // ── ACCOUNT DISABLED — ang employer nga hunong na sa pagpasa ug
        // ── bakante ug wala mitubag sa email. Kini ra ang pahina nga iyang
        // ── maabot hangtod siya mosulti sa iyang rason. ──
        Route::get('/company/status-check',                  [CompanyWebController::class, 'showDormantNotice'])->name('employer.dormant');
        Route::post('/company/status-check',                 [CompanyWebController::class, 'submitDormantNotice'])->name('employer.dormant.submit');

        // ── COMPANY REQUIREMENTS ──
        Route::get('/company/requirements',                  [EmployerRequirementController::class, 'index'])->name('company.requirements');
        Route::post('/company/requirements',                 [EmployerRequirementController::class, 'store'])->name('company.requirements.store');

        // ── COMPANY JOB FAIR ──
        Route::get('/company/jobs/{id}/qualified',           [CompanyWebController::class, 'qualifiedApplicants'])->name('company.jobs.qualified');
        // ── Ang employer mo-report sa gi-hire nga wala miagi sa PESO. Mo-apektar
        // ── sa tibuok posting group — usa ra man ka bakante ang gi-ambitan sa
        // ── mga channel. Ang posting mo-sira na lang mag-isa pag-abot sa
        // ── slots, mao nga walay bulag nga "mark as filled" nga buton. ──
        Route::post('/company/jobs/{id}/external-hires',     [CompanyWebController::class, 'recordExternalHires'])->name('company.jobs.recordHires');
        Route::get('/company/jobfair',                       [CompanyWebController::class, 'jobFairInvitations'])->name('company.jobfair');
        Route::post('/company/jobfair/{id}/respond',         [CompanyWebController::class, 'respondJobFair'])->name('company.jobfair.respond');

        // ── COMPANY IN-HOUSE ──
        // ── Usa ka HR, mahimong daghang kompanya. Ang gitrabahoan naa sa
        // ── session; ang pagdugang moagi sa parehas nga porma sa rehistro. ──
        Route::post('/company/companies/{id}/switch',        [CompanyWebController::class, 'switchCompany'])->name('company.companies.switch');
        Route::get('/company/companies/add',                 [CompanyWebController::class, 'showAddCompany'])->name('company.companies.add');
        Route::post('/company/companies',                    [CompanyWebController::class, 'storeAddCompany'])->name('company.companies.store');

        Route::get('/company/calendar-data',                 [CompanyWebController::class, 'calendarData'])->name('company.calendarData');

        Route::get('/company/inhouse',                       [CompanyWebController::class, 'inhouseSchedules'])->name('company.inhouse');
        Route::get('/company/inhouse/check-date',             [CompanyWebController::class, 'checkInhouseDateAvailability'])->name('company.inhouse.checkDate');
        Route::get('/company/inhouse/booked-dates',            [CompanyWebController::class, 'getBookedDates'])->name('company.inhouse.bookedDates');
        Route::get('/company/inhouse/create',                [CompanyWebController::class, 'createInhouse'])->name('company.inhouse.create');
        Route::post('/company/inhouse',                      [CompanyWebController::class, 'storeInhouse'])->name('company.inhouse.store');

        // ── COMPANY JOBSEEKERS ──
        Route::get('/company/jobseekers',                    [CompanyWebController::class, 'jobseekers'])->name('company.jobseekers');

        // ── COMPANY REPORTS ──
        Route::get('/company/reports',                       [CompanyWebController::class, 'reports'])->name('company.reports');
        // ── CSV download ug printable nga kopya. Usa ka route, ang ?format=
        // ── maoy mag-lain — parehas ra man ang query ug ang mga column. ──
        Route::get('/company/reports/export',                [CompanyWebController::class, 'exportReports'])->name('company.reports.export');
        Route::get('/company/reports/{jobId}',                [CompanyWebController::class, 'reportsByJob'])->name('company.reports.show');
    });

    // ───────────────────────────────
    // STAFF — Auth Routes
    // ───────────────────────────────
    Route::middleware('auth')->group(function () {
        Route::get('/staff/dashboard',                        [StaffWebController::class, 'dashboard'])->name('staff.dashboard');
        Route::get('/staff/registrations',                    [StaffWebController::class, 'registrations'])->name('staff.registrations');
        Route::get('/staff/registrations/export',             [StaffWebController::class, 'exportRegistrations'])->name('staff.registrations.export');
        Route::get('/staff/registrations/{id}',               [StaffWebController::class, 'viewRegistration'])->name('staff.registrations.view');
        Route::get('/staff/profile',                          [StaffWebController::class, 'showProfile'])->name('staff.profile');
        Route::post('/staff/profile',                         [StaffWebController::class, 'updateProfile'])->name('staff.profile.update');
        Route::post('/staff/profile/password',                [StaffWebController::class, 'changePassword'])->name('staff.profile.password');
        Route::post('/staff/notifications/{id}/read',         [StaffWebController::class, 'markNotificationRead'])->name('staff.notifications.markRead');
        Route::post('/staff/notifications/mark-all-read',     [StaffWebController::class, 'markAllNotificationsRead'])->name('staff.notifications.markAllRead');
        Route::get('/staff/notifications',                    [StaffWebController::class, 'notifications'])->name('staff.notifications.index');
        Route::get('/staff/notifications/fetch',              [StaffWebController::class, 'notificationsFetch'])->name('staff.notifications.fetch');

        // ── STAFF EMPLOYERS ──
        Route::get('/staff/employers',                        [StaffWebController::class, 'employers'])->name('staff.employers');
        // ── Walk-in: ang Job Vacancy staff mo-encode sa usa ka lokal nga
        // ── employer nga miadto sa opisina — kompanya, papeles ug bakante sa
        // ── usa ka lingkod. Ang role check naa sa controller. ──
        Route::get('/staff/employers/walk-in',                [StaffWebController::class, 'walkinEmployer'])->name('staff.employers.walkin');
        Route::post('/staff/employers/walk-in',               [StaffWebController::class, 'storeWalkinEmployer'])->name('staff.employers.walkin.store');
        // ── Ang bugtong field sa employer nga mausab sa staff. Tan-awa ang
        // ── updateEmployerIndustry kung ngano. ──
        // Ang gisalang listahan, isip Excel. Parehas nga query sa nakita sa
        // screen — ang gi-download mao gyud ang gitan-aw.
        Route::get('/staff/employers/export',                 [StaffWebController::class, 'exportEmployers'])->name('staff.employers.export');
        Route::post('/staff/employers/{id}/industry',         [StaffWebController::class, 'updateEmployerIndustry'])->name('staff.employers.industry');
        // ── HR handover: LRA/Job Vacancy para sa local, SRA para sa overseas.
        // ── Ang role check naa sa controller kay ang basihan mao ang is_overseas
        // ── sa employer, dili ang route. ──
        Route::post('/staff/employers/{id}/transfer',         [StaffWebController::class, 'transferEmployerAccount'])->name('staff.employers.transfer');
        // ── I-abli pag-usab ang account nga gisira sa inactivity sweep,
        // ── human mabasa sa staff ang rason sa employer. ──
        Route::post('/staff/employers/{id}/enable',           [StaffWebController::class, 'enableDormantEmployer'])->whereNumber('id')->name('staff.employers.enable');

        // ── STAFF REQUIREMENTS ──
        Route::get('/staff/requirements',                     [StaffWebController::class, 'employerRequirements'])->name('staff.requirements');
        Route::get('/staff/requirements/{id}',                [StaffWebController::class, 'viewEmployerRequirement'])->name('staff.requirements.view');
        Route::post('/staff/requirements/{id}/approve',       [StaffWebController::class, 'approveRequirement'])->name('staff.requirements.approve');
        Route::post('/staff/requirements/{id}/reject',        [StaffWebController::class, 'rejectRequirement'])->name('staff.requirements.reject');
        // Usa ka papel matag pindot. Ang pag-approve sa tibuok folder magpabilin
        // sa taas nga route, ug mobalibad siya hangtod mahurot kining lima.
        Route::post('/staff/requirements/{id}/documents/{field}/accept', [StaffWebController::class, 'acceptRequirementDocument'])->whereNumber('id')->name('staff.requirements.documents.accept');
        Route::post('/staff/requirements/{id}/documents/{field}/reject', [StaffWebController::class, 'rejectRequirementDocument'])->whereNumber('id')->name('staff.requirements.documents.reject');
        Route::post('/staff/requirements/{id}/documents/{field}/undo',   [StaffWebController::class, 'undoRequirementDocument'])->whereNumber('id')->name('staff.requirements.documents.undo');

        // ── STAFF JOB FAIR ──
        Route::get('/staff/jobfair/events',                   [StaffWebController::class, 'jobFairEvents'])->name('staff.jobfair.events');
        Route::get('/staff/jobfair/events/create',            [StaffWebController::class, 'createJobFairEvent'])->name('staff.jobfair.events.create');
        Route::post('/staff/jobfair/events',                  [StaffWebController::class, 'storeJobFairEvent'])->name('staff.jobfair.events.store');
        Route::get('/staff/jobfair/events/{id}/edit',         [StaffWebController::class, 'editJobFairEvent'])->name('staff.jobfair.events.edit');
        Route::put('/staff/jobfair/events/{id}',              [StaffWebController::class, 'updateJobFairEvent'])->name('staff.jobfair.events.update');
        Route::delete('/staff/jobfair/events/{id}',           [StaffWebController::class, 'deleteJobFairEvent'])->name('staff.jobfair.events.delete');
        Route::post('/staff/jobfair/events/{id}/invite-more', [StaffWebController::class, 'inviteMoreEmployers'])->whereNumber('id')->name('staff.jobfair.events.inviteMore');
        Route::get('/staff/jobfair/postings',                 [StaffWebController::class, 'jobFairPostings'])->name('staff.jobfair.postings');
        // Usa ra ka buton. Ang fair mismo ang nagsala: ang naghulat nga bakante
        // nga sakop niini mosulod, ang uban maghulat sa fair nga modawat nila.
        Route::post('/staff/jobfair/postings/post-fitting',  [StaffWebController::class, 'approveFittingJobFairJobs'])->name('staff.jobfair.postings.postFitting');
        // Ang pagpakita sa bakante ngadto sa jobseeker. Manual: ang desk ang
        // nagbuot kung kanus-a mobuto ang listahan, sagad lima ka adlaw sa
        // dili pa ang fair.
        Route::post('/staff/jobfair/postings/open-all',      [StaffWebController::class, 'openJobFairPostings'])->name('staff.jobfair.postings.openAll');

        // Ang applicant sa usa ka bakante nga gidala sa fair, gibahin sa marka.
        // Ang text gikan dinhi ug dili gikan sa usa ka kinatibuk-ang blast page:
        // ang staff makakita kinsa ang padad-an sa dili pa siya mopadala.
        Route::get('/staff/jobfair/postings/{id}/applicants', [StaffWebController::class, 'jobFairApplicants'])->whereNumber('id')->name('staff.jobfair.postings.applicants');
        Route::post('/staff/jobfair/postings/{id}/notify',    [StaffWebController::class, 'notifyJobFairApplicants'])->whereNumber('id')->name('staff.jobfair.postings.notify');

        // ── STAFF IN-HOUSE ──
        Route::get('/staff/inhouse/jobfair',                  [StaffWebController::class, 'jobFairViewOnly'])->name('staff.inhouse.jobfair');
        // ── Ang overseas nga ahensya dili na awtomatiko nga ma-invite sa job
        // ── fair. Si SRA ang mopili, human siya mangayo ug permiso sa pangulo
        // ── sa PESO — mao nga ang lihok naay ngalan ug petsa nga nahibilin. ──
        Route::post('/staff/jobfair/overseas/{id}/invite',    [StaffWebController::class, 'inviteOverseasToJobFair'])->name('staff.jobfair.overseas.invite');
        // Ang ikaduhang pultahan: mitubag na ug oo ang ahensya, si SRA na ang
        // mopili kung dad-on ba siya sa fair.
        Route::post('/staff/jobfair/overseas/{id}/decide',     [StaffWebController::class, 'decideOverseasSelection'])->whereNumber('id')->name('staff.jobfair.overseas.decide');
        // ── Kinsa ang niapil: ang mga jobseeker nga niduyog sa in-house ug
        // ── ang naka-rehistro sa job fair. LRA/SRA ra, ug bahin sila sumala
        // ── sa classification sa jobseeker mismo. ──
        Route::get('/staff/participants',                     [StaffWebController::class, 'participants'])->name('staff.participants');
        Route::get('/staff/inhouse',                          [StaffWebController::class, 'inhouseSchedules'])->name('staff.inhouse');
        Route::get('/staff/inhouse/calendar-data',             [StaffWebController::class, 'inhouseCalendarData'])->name('staff.inhouse.calendarData');
        // ── Ang gikaon sa calendar picker sa walk-in nga porma. ──
        Route::get('/staff/inhouse/booked-dates',              [StaffWebController::class, 'inhouseBookedDates'])->name('staff.inhouse.bookedDates');
        Route::get('/staff/inhouse/check-date',                [StaffWebController::class, 'inhouseCheckDate'])->name('staff.inhouse.checkDate');
        Route::get('/staff/inhouse/{id}',                     [StaffWebController::class, 'viewInhouseSchedule'])->name('staff.inhouse.view');
        Route::post('/staff/inhouse/{id}/accept',             [StaffWebController::class, 'acceptInhouse'])->name('staff.inhouse.accept');
        Route::post('/staff/inhouse/{id}/reject',             [StaffWebController::class, 'rejectInhouse'])->name('staff.inhouse.reject');
        

        // ── STAFF JOB VACANCIES ──
        Route::post('/staff/jobs/{id}/approve',               [StaffWebController::class, 'approveJob'])->name('staff.jobs.approve');
        Route::post('/staff/jobs/{id}/reject',                [StaffWebController::class, 'rejectJob'])->name('staff.jobs.reject');
        Route::get('/staff/jobs/{id}/qualified',              [StaffWebController::class, 'qualifiedApplicants'])->name('staff.jobs.qualified');
        Route::get('/staff/jobs',                             [StaffWebController::class, 'jobVacancies'])->name('staff.jobs');
        // ── Ang poster nga gi-upload sa employer, isip file. Ang staff
        // ── mag-post niini sa social media ug mag-print para sa bulletin
        // ── board sa gawas. ──
        Route::get('/staff/jobs/{id}/poster',                 [StaffWebController::class, 'downloadJobPoster'])->whereNumber('id')->name('staff.jobs.poster');
        Route::get('/staff/jobs/create',                      [StaffWebController::class, 'createJobVacancy'])->name('staff.jobs.create');
        Route::post('/staff/jobs',                            [StaffWebController::class, 'storeJobVacancy'])->name('staff.jobs.store');
        Route::get('/staff/jobs/{id}/edit',                   [StaffWebController::class, 'editJobVacancy'])->name('staff.jobs.edit');
        Route::put('/staff/jobs/{id}',                        [StaffWebController::class, 'updateJobVacancy'])->name('staff.jobs.update');
        // Walay delete dinhi nga tinuyo. PESO, 2026-08-26: ang posting iya sa
        // employer, ug siya ra ang makatangtang niini. Ang staff makapa-undang
        // ug posting pinaagi sa Take Down (staff.jobs.reject), nga naay rason
        // nga makita sa employer — dili siya mawala nga walay tubag.

        // ── STAFF JOBSEEKERS ──
        Route::post('/staff/registrations/{id}/apply',        [ApplicationController::class, 'applyByStaff'])->name('staff.registrations.apply');

        // ── STAFF WALK-IN NSRP (LRA/SRA) ──
        Route::get('/staff/nsrp',                              [StaffWebController::class, 'walkinNsrp'])->name('staff.nsrp');
        Route::post('/staff/nsrp',                             [StaffWebController::class, 'storeWalkinNsrp'])->name('staff.nsrp.store');
        Route::post('/staff/nsrp/scan',                        [StaffWebController::class, 'nsrpScan'])->name('staff.nsrp.scan');

        Route::post('/staff/jobfair/registrations/{id}/attend',   [StaffWebController::class, 'markJobFairAttendance'])->name('staff.jobfair.attendance.mark');
        Route::post('/staff/jobfair/registrations/{id}/unattend', [StaffWebController::class, 'unmarkJobFairAttendance'])->name('staff.jobfair.attendance.unmark');

        // ── STAFF REPORTS ──
        Route::get('/staff/reports',                          [StaffWebController::class, 'reports'])->name('staff.reports');
        // ── CSV download ug printable nga kopya (LRA/SRA). Usa ka route, ang
        // ── ?format=print maoy mo-usab sa porma. ──
        Route::get('/staff/reports/export',                   [StaffWebController::class, 'exportReports'])->name('staff.reports.export');
        Route::get('/staff/reports/employers',                [StaffWebController::class, 'jobVacancyReports'])->name('staff.reports.employers');
        // ── JOB VACANCY REPORTS — ma-download ang report sa sistema, ug
        // ── ma-upload ang kaugalingong excel sa opisina. Ang gi-import wala
        // ── gyud isagol sa numero sa sistema; kaugalingon niyang tab. ──
        Route::get('/staff/reports/employers/export',         [StaffWebController::class, 'exportJobVacancyReport'])->name('staff.reports.employers.export');
        Route::post('/staff/reports/employers/import',        [StaffWebController::class, 'importJobVacancyReport'])->name('staff.reports.employers.import');
        Route::get('/staff/reports/employers/import/{id}',    [StaffWebController::class, 'downloadJobVacancyImportedReport'])->whereNumber('id')->name('staff.reports.employers.import.download');
        Route::delete('/staff/reports/employers/import/{id}', [StaffWebController::class, 'deleteJobVacancyImportedReport'])->whereNumber('id')->name('staff.reports.employers.import.delete');
        Route::get('/staff/reports/inhouse/export',           [StaffWebController::class, 'exportInhouseEmployerReport'])->name('staff.reports.inhouse.export');
        // ── JOB FAIR REPORTS — kada tab ma-download, ug ang staff
        // ── makasulod sa iyang kaugalingon nga report. Lahi ni sa
        // ── reports/export sa taas: event ang sakup niini, dili petsa. ──
        Route::get('/staff/reports/jobfair/export',           [StaffWebController::class, 'exportJobFairReport'])->name('staff.reports.jobfair.export');
        Route::post('/staff/reports/jobfair/import',          [StaffWebController::class, 'importJobFairReport'])->name('staff.reports.jobfair.import');
        Route::get('/staff/reports/jobfair/import/{id}',      [StaffWebController::class, 'downloadJobFairImportedReport'])->whereNumber('id')->name('staff.reports.jobfair.import.download');
        Route::delete('/staff/reports/jobfair/import/{id}',   [StaffWebController::class, 'deleteJobFairImportedReport'])->whereNumber('id')->name('staff.reports.jobfair.import.delete');
        Route::get('/staff/reports/job/{jobId}',              [StaffWebController::class, 'reportJobDetails'])->name('staff.reports.job');
    });

    // ───────────────────────────────
    // JOBSEEKER — Auth Routes
    // ───────────────────────────────
    Route::middleware('auth')->group(function () {
        Route::get('/jobseeker/dashboard',                    [JobseekerWebController::class, 'dashboard'])->name('jobseeker.dashboard');
        Route::get('/jobseeker/calendar-data',                [JobseekerWebController::class, 'calendarData'])->name('jobseeker.calendarData');
        Route::get('/jobseeker/nsrp',                         [JobseekerWebController::class, 'nsrp'])->name('jobseeker.nsrp');
        Route::get('/jobseeker/resume',                       [JobseekerWebController::class, 'downloadResume'])->name('jobseeker.resume');
        Route::post('/jobseeker/nsrp',                        [JobseekerWebController::class, 'nsrpStore'])->name('jobseeker.nsrp.store');
        Route::get('/jobseeker/jobs',                         [JobseekerWebController::class, 'jobs'])->name('jobseeker.jobs');
        Route::get('/jobseeker/applications',                 [JobseekerWebController::class, 'applications'])->name('jobseeker.applications');
        Route::get('/jobseeker/profile',                      [JobseekerWebController::class, 'showProfile'])->name('jobseeker.profile');
        Route::post('/jobseeker/profile',                     [JobseekerWebController::class, 'updateProfile'])->name('jobseeker.profile.update');
        Route::post('/jobseeker/profile/password',            [JobseekerWebController::class, 'changePassword'])->name('jobseeker.profile.password');
        Route::post('/jobseeker/notifications/{id}/read',     [JobseekerWebController::class, 'markNotificationRead'])->name('jobseeker.notifications.markRead');
        Route::post('/jobseeker/notifications/mark-all-read', [JobseekerWebController::class, 'markAllNotificationsRead'])->name('jobseeker.notifications.markAllRead');
        Route::get('/jobseeker/jobs/{id}',                    [JobseekerWebController::class, 'showJob'])->name('jobseeker.jobs.show');
        Route::post('/jobseeker/jobs/{id}/apply',             [ApplicationController::class, 'apply'])->name('jobseeker.jobs.apply');
        Route::post('/jobseeker/applications/{id}/inhouse-response', [ApplicationController::class, 'respondInhouseParticipation'])->name('jobseeker.applications.inhouseResponse');
        Route::post('/jobseeker/applications/{id}/company-interview-response', [ApplicationController::class, 'respondCompanyInterviewParticipation'])->name('jobseeker.applications.companyInterviewResponse');
        Route::get('/jobseeker/schedules',                    [JobseekerWebController::class, 'schedules'])->name('jobseeker.schedules');
        Route::post('/jobseeker/inhouse/{id}/join',            [JobseekerWebController::class, 'joinInhouse'])->name('jobseeker.inhouse.join');
        Route::post('/jobseeker/jobfair/{id}/join',             [JobseekerWebController::class, 'joinJobFair'])->name('jobseeker.jobfair.join');
        Route::post('/jobseeker/jobfair-registrations/{id}/attendance-response', [JobseekerWebController::class, 'respondJobFairAttendance'])->name('jobseeker.jobfair.attendanceResponse');
        Route::get('/jobseeker/history',                       [JobseekerWebController::class, 'history'])->name('jobseeker.history');
        Route::get('/jobseeker/notifications',                  [JobseekerWebController::class, 'notifications'])->name('jobseeker.notifications.index');
    });

    // ───────────────────────────────
    // REAL-TIME CHECKS (Guest)
    // ───────────────────────────────
    Route::get('/check-name',  [UnifiedAuthController::class, 'checkName'])->name('check.name');
    Route::get('/check-email', [UnifiedAuthController::class, 'checkEmail'])->name('check.email');
    Route::get('/check-company-name', [UnifiedAuthController::class, 'checkCompanyName'])->name('check.companyName');

    // ───────────────────────────────
    // PH ADDRESS DATA (Guest) — hardcoded JSON sa storage, walay external API
    // ───────────────────────────────
    Route::get('/ph-address/provinces',                  [UnifiedAuthController::class, 'addressProvinces'])->name('address.provinces');
    Route::get('/ph-address/provinces/{code}/cities',     [UnifiedAuthController::class, 'addressCities'])->name('address.cities');
    Route::get('/ph-address/cities/{code}/barangays',     [UnifiedAuthController::class, 'addressBarangays'])->name('address.barangays');

    // ── Ang /seed-address nga route gikuha na. Bukas siya sa bisan kinsa nga
    // ── bisita, ug ang seedAddressData() nag-set_time_limit(300) ug mo-loop sa
    // ── PSGC API para sa matag probinsya — lima ka minuto kada pindot, ug
    // ── mabalik-balik. Ang method nagpabilin sa controller: dagana siya sa
    // ── `php artisan tinker` kung kinahanglan ipatindog pag-usab ang address
    // ── data. Ang bukas nga pultahan ra ang gisirad-an. ──

    // ── Ang /test-ocr nga route gikuha na. Tesseract siya, ug ang Tesseract wala
    // ── nay lugar dinhi — ang pagbasa sa NSRP naa na sa Python nga serbisyo sa
    // ── ocr_service/. Bukas pud siya sa bisan kinsa nga bisita, nga dili angay
    // ── sa usa ka na-deploy nga site. ──