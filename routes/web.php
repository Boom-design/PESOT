<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CompanyWebController;
use App\Http\Controllers\UnifiedAuthController;
use App\Http\Controllers\StaffWebController;
use App\Http\Controllers\EmployerRequirementController;
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

// ───────────────────────────────
// FORGOT PASSWORD
// ───────────────────────────────
Route::get('/forgot-password',  [UnifiedAuthController::class, 'showForgotPassword'])->name('password.request');
Route::post('/forgot-password', [UnifiedAuthController::class, 'sendResetCode'])->name('password.email');
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
// ADMIN — Auth Routes
// ───────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/admin/dashboard',                    [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::post('/admin/logout',                      [AdminController::class, 'logout'])->name('admin.logout');
    Route::get('/admin/users',                        [AdminController::class, 'manageUsers'])->name('admin.users.manage');
    Route::post('/admin/users/store',                 [AdminController::class, 'storeUser'])->name('admin.users.store');
    Route::put('/admin/users/{id}',                   [AdminController::class, 'updateUser'])->name('admin.users.update');
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

    // ── COMPANY REQUIREMENTS ──
    Route::get('/company/requirements',                  [EmployerRequirementController::class, 'index'])->name('company.requirements');
    Route::post('/company/requirements',                 [EmployerRequirementController::class, 'store'])->name('company.requirements.store');

    // ── COMPANY JOB FAIR ──
    Route::get('/company/jobs/{id}/qualified',           [CompanyWebController::class, 'qualifiedApplicants'])->name('company.jobs.qualified');
    Route::get('/company/jobfair',                       [CompanyWebController::class, 'jobFairInvitations'])->name('company.jobfair');
    Route::post('/company/jobfair/{id}/respond',         [CompanyWebController::class, 'respondJobFair'])->name('company.jobfair.respond');
    Route::get('/company/jobfair/{id}/select-jobs',      [CompanyWebController::class, 'showJobFairJobSelect'])->name('company.jobfair.selectJobs');
    Route::post('/company/jobfair/{id}/select-jobs',     [CompanyWebController::class, 'storeJobFairJobSelect'])->name('company.jobfair.storeJobSelect');

    // ── COMPANY IN-HOUSE ──
    Route::get('/company/inhouse',                       [CompanyWebController::class, 'inhouseSchedules'])->name('company.inhouse');
    Route::get('/company/inhouse/check-date',             [CompanyWebController::class, 'checkInhouseDateAvailability'])->name('company.inhouse.checkDate');
    Route::get('/company/inhouse/create',                [CompanyWebController::class, 'createInhouse'])->name('company.inhouse.create');
    Route::post('/company/inhouse',                      [CompanyWebController::class, 'storeInhouse'])->name('company.inhouse.store');

    // ── COMPANY JOBSEEKERS ──
    Route::get('/company/jobseekers',                    [CompanyWebController::class, 'jobseekers'])->name('company.jobseekers');

    // ── COMPANY REPORTS ──
    Route::get('/company/reports',                       [CompanyWebController::class, 'reports'])->name('company.reports');
});

// ───────────────────────────────
// STAFF — Auth Routes
// ───────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/staff/dashboard',                        [StaffWebController::class, 'dashboard'])->name('staff.dashboard');
    Route::get('/staff/registrations',                    [StaffWebController::class, 'registrations'])->name('staff.registrations');
    Route::get('/staff/registrations/{id}',               [StaffWebController::class, 'viewRegistration'])->name('staff.registrations.view');
    Route::get('/staff/profile',                          [StaffWebController::class, 'showProfile'])->name('staff.profile');
    Route::post('/staff/profile',                         [StaffWebController::class, 'updateProfile'])->name('staff.profile.update');
    Route::post('/staff/profile/password',                [StaffWebController::class, 'changePassword'])->name('staff.profile.password');
    Route::post('/staff/notifications/{id}/read',         [StaffWebController::class, 'markNotificationRead'])->name('staff.notifications.markRead');
    Route::post('/staff/notifications/mark-all-read',     [StaffWebController::class, 'markAllNotificationsRead'])->name('staff.notifications.markAllRead');
    Route::get('/staff/notifications',                    [StaffWebController::class, 'notifications'])->name('staff.notifications.index');

    // ── STAFF EMPLOYERS ──
    Route::get('/staff/employers',                        [StaffWebController::class, 'employers'])->name('staff.employers');
    Route::get('/staff/employers/{id}',                   [StaffWebController::class, 'viewEmployer'])->name('staff.employers.view');

    // ── STAFF REQUIREMENTS ──
    Route::get('/staff/requirements',                     [StaffWebController::class, 'employerRequirements'])->name('staff.requirements');
    Route::get('/staff/requirements/{id}',                [StaffWebController::class, 'viewEmployerRequirement'])->name('staff.requirements.view');
    Route::post('/staff/requirements/{id}/approve',       [StaffWebController::class, 'approveRequirement'])->name('staff.requirements.approve');
    Route::post('/staff/requirements/{id}/reject',        [StaffWebController::class, 'rejectRequirement'])->name('staff.requirements.reject');

    // ── STAFF JOB FAIR ──
    Route::get('/staff/jobfair/send',                     [StaffWebController::class, 'showJobFairSend'])->name('staff.jobfair.send');
    Route::post('/staff/jobfair/send',                    [StaffWebController::class, 'sendJobFairNotification'])->name('staff.jobfair.send.post');
    Route::get('/staff/jobfair/events',                   [StaffWebController::class, 'jobFairEvents'])->name('staff.jobfair.events');
    Route::get('/staff/jobfair/events/create',            [StaffWebController::class, 'createJobFairEvent'])->name('staff.jobfair.events.create');
    Route::post('/staff/jobfair/events',                  [StaffWebController::class, 'storeJobFairEvent'])->name('staff.jobfair.events.store');
    Route::get('/staff/jobfair/events/{id}/edit',         [StaffWebController::class, 'editJobFairEvent'])->name('staff.jobfair.events.edit');
    Route::put('/staff/jobfair/events/{id}',              [StaffWebController::class, 'updateJobFairEvent'])->name('staff.jobfair.events.update');
    Route::delete('/staff/jobfair/events/{id}',           [StaffWebController::class, 'deleteJobFairEvent'])->name('staff.jobfair.events.delete');
    Route::post('/staff/jobfair/events/{id}/invite',      [StaffWebController::class, 'sendJobFairInvitation'])->name('staff.jobfair.events.invite');
    Route::get('/staff/jobfair/events/{id}/participants', [StaffWebController::class, 'jobFairParticipants'])->name('staff.jobfair.events.participants');
    Route::post('/staff/jobfair/events/{id}/check-notify',[StaffWebController::class, 'checkAndNotifyJobFair'])->name('staff.jobfair.events.checkNotify');

    // ── STAFF IN-HOUSE ──
    Route::get('/staff/inhouse/jobfair',                  [StaffWebController::class, 'jobFairViewOnly'])->name('staff.inhouse.jobfair');
    Route::get('/staff/inhouse',                          [StaffWebController::class, 'inhouseSchedules'])->name('staff.inhouse');
    Route::get('/staff/inhouse/{id}',                     [StaffWebController::class, 'viewInhouseSchedule'])->name('staff.inhouse.view');
    Route::post('/staff/inhouse/{id}/accept',             [StaffWebController::class, 'acceptInhouse'])->name('staff.inhouse.accept');
    Route::post('/staff/inhouse/{id}/reject',             [StaffWebController::class, 'rejectInhouse'])->name('staff.inhouse.reject');
    

    // ── STAFF JOB VACANCIES ──
    Route::post('/staff/jobs/{id}/approve',               [StaffWebController::class, 'approveJob'])->name('staff.jobs.approve');
    Route::post('/staff/jobs/{id}/reject',                [StaffWebController::class, 'rejectJob'])->name('staff.jobs.reject');
    Route::get('/staff/jobs/{id}/qualified',              [StaffWebController::class, 'qualifiedApplicants'])->name('staff.jobs.qualified');
    Route::get('/staff/jobs',                             [StaffWebController::class, 'jobVacancies'])->name('staff.jobs');
    Route::get('/staff/jobs/create',                      [StaffWebController::class, 'createJobVacancy'])->name('staff.jobs.create');
    Route::post('/staff/jobs',                            [StaffWebController::class, 'storeJobVacancy'])->name('staff.jobs.store');
    Route::get('/staff/jobs/{id}/edit',                   [StaffWebController::class, 'editJobVacancy'])->name('staff.jobs.edit');
    Route::put('/staff/jobs/{id}',                        [StaffWebController::class, 'updateJobVacancy'])->name('staff.jobs.update');
    Route::delete('/staff/jobs/{id}',                     [StaffWebController::class, 'deleteJobVacancy'])->name('staff.jobs.delete');

    // ── STAFF JOBSEEKERS ──
    Route::get('/staff/jobseekers',                       [StaffWebController::class, 'jobseekers'])->name('staff.jobseekers');

    // ── STAFF WALK-IN NSRP (LRA/SRA) ──
    Route::get('/staff/nsrp',                              [StaffWebController::class, 'walkinNsrp'])->name('staff.nsrp');
    Route::post('/staff/nsrp',                             [StaffWebController::class, 'storeWalkinNsrp'])->name('staff.nsrp.store');
    Route::post('/staff/nsrp/scan',                        [StaffWebController::class, 'nsrpScan'])->name('staff.nsrp.scan');

    Route::post('/staff/jobfair/registrations/{id}/attend',   [StaffWebController::class, 'markJobFairAttendance'])->name('staff.jobfair.attendance.mark');
    Route::post('/staff/jobfair/registrations/{id}/unattend', [StaffWebController::class, 'unmarkJobFairAttendance'])->name('staff.jobfair.attendance.unmark');

    // ── STAFF REPORTS ──
    Route::get('/staff/reports',                          [StaffWebController::class, 'reports'])->name('staff.reports');
    Route::get('/staff/reports/employers',                [StaffWebController::class, 'jobVacancyReports'])->name('staff.reports.employers');
});

// ───────────────────────────────
// JOBSEEKER — Auth Routes
// ───────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/jobseeker/dashboard',                    [JobseekerWebController::class, 'dashboard'])->name('jobseeker.dashboard');
    Route::get('/jobseeker/nsrp',                         [JobseekerWebController::class, 'nsrp'])->name('jobseeker.nsrp');
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
    Route::get('/jobseeker/schedules',                    [JobseekerWebController::class, 'schedules'])->name('jobseeker.schedules');
    Route::post('/jobseeker/inhouse/{id}/join',            [JobseekerWebController::class, 'joinInhouse'])->name('jobseeker.inhouse.join');
    Route::post('/jobseeker/jobfair/{id}/join',             [JobseekerWebController::class, 'joinJobFair'])->name('jobseeker.jobfair.join');
    Route::get('/jobseeker/history',                       [JobseekerWebController::class, 'history'])->name('jobseeker.history');
    Route::post('/jobseeker/nsrp/scan',                     [JobseekerWebController::class, 'nsrpScan'])->name('jobseeker.nsrp.scan');
});

// ───────────────────────────────
// REAL-TIME CHECKS (Guest)
// ───────────────────────────────
Route::get('/check-name',  [UnifiedAuthController::class, 'checkName'])->name('check.name');
Route::get('/check-email', [UnifiedAuthController::class, 'checkEmail'])->name('check.email');
Route::get('/check-company-name', [UnifiedAuthController::class, 'checkCompanyName'])->name('check.companyName');

// ── TEMPORARY TEST ROUTE — OCR (tangtangon ra ni human sa testing) ──
Route::get('/test-ocr', function () {
    $imagePath = public_path('test-image.jpg');

    if (!file_exists($imagePath)) {
        return 'Wala nakit-i ang test-image.jpg sa public folder!';
    }

    $text = (new \thiagoalessio\TesseractOCR\TesseractOCR($imagePath))->run();

    return '<pre>' . htmlspecialchars($text) . '</pre>';
});