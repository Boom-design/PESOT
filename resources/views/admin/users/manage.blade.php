@extends('admin.layouts.app')

@section('content')

@include('partials.temp-password-banner')

{{-- HEADER --}}
<div class="mb-3">
    <h5 class="fw-bold mb-0" style="color:var(--g-700);">List of Users Information</h5>
</div>

{{-- TABS + ADD STAFF BUTTON (same row) --}}
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
<ul class="nav nav-tabs mb-0" id="userTabs">
    <li class="nav-item">
        <button class="nav-link active fw-semibold" data-tab="jobseeker">
            Jobseekers
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link fw-semibold" data-tab="company">
            Companies
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link fw-semibold" data-tab="staff">
            Staff
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link fw-semibold" data-tab="inactive">
            Inactive
        </button>
    </li>
</ul>
    <button class="btn btn-sm fw-semibold"
        style="background:var(--g-600);color:#fff;
               border:none;border-radius:8px;padding:8px 18px;font-size:13px;white-space:nowrap;"
        data-bs-toggle="modal" data-bs-target="#addUserModal">
        <i class="ph-fill ph-user-plus me-1"></i> Add Staff Account
    </button>
</div>

{{-- SEARCH + TABLE --}}
@if($users->isEmpty())
    <div class="alert alert-info">No users registered yet.</div>
@else
    <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
        <span id="tabCountText" style="font-size:13px;color:var(--g-700);font-weight:600;"></span>
        <div class="input-group" style="max-width:280px;">
            <span class="input-group-text" style="border-color:var(--n-200);background:var(--n-50);">
                <i class="ph ph-magnifying-glass" style="color:var(--g-600);"></i>
            </span>
            <input type="text" id="searchInput" class="form-control"
                placeholder="Search name or email..."
                style="border-color:var(--n-200);font-size:13px;">
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="usersTable">
                <thead>
                    <tr style="background:var(--n-500);">
                        <th style="color:var(--g-700);font-size:12px;padding:12px 16px;">#</th>
                        <th style="color:var(--g-700);font-size:12px;padding:12px 16px;">
                            Name
                            <span style="display:inline-flex;flex-direction:column;margin-left:4px;vertical-align:middle;line-height:0.6;">
                                <i class="ph-fill ph-caret-up" id="sortNameAsc" role="button" title="Sort A-Z" style="font-size:9px;color:var(--g-600);"></i>
                                <i class="ph-fill ph-caret-down" id="sortNameDesc" role="button" title="Sort Z-A" style="font-size:9px;color:var(--g-600);"></i>
                            </span>
                        </th>
                        <th style="color:var(--g-700);font-size:12px;padding:12px 16px;">Email</th>
                        <th style="color:var(--g-700);font-size:12px;padding:12px 16px;">Placement Type</th>
                        <th style="color:var(--g-700);font-size:12px;padding:12px 16px;">Phone Number</th>
                        <th style="color:var(--g-700);font-size:12px;padding:12px 16px;text-align:center;">Date Registered</th>
                        <th style="color:var(--g-700);font-size:12px;padding:12px 16px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $index => $user)
                    @php
                        $rowRole = ($user->role === 'staff' && $user->status === 'deactivated') ? 'inactive' : $user->role;

                        if ($user->role === 'jobseeker') {
                            $nsrpType = $user->registration?->nsrp?->type;
                            if (!$nsrpType) {
                                continue;
                            }
                            $placement = ['local' => 'LRA', 'overseas' => 'SRA', 'both' => 'Both'][$nsrpType];
                        } elseif ($user->role === 'company') {
                            $placement = ($user->employerNsrp?->is_overseas ?? false) ? 'Overseas' : 'Local';

                            // ── employer_requirements.user_id naghupot ug
                            // ── employer_nsrp_registrations_id bisan pa sa ngalan niini.
                            // ── Ang pagpangita pinaagi sa users_id nagtandi ug duha ka
                            // ── managlahi nga numero, ug ang nakit-an niini kay
                            // ── aksidente. Ang una nga kompanya ang gipakita dinhi;
                            // ── ang desk sa staff ang naglista sa matag usa. ──
                            $adminCompanies = $user->employerCompanies;
                            $requirement = $user->employerNsrp
                                ? \App\Models\EmployerRequirement::where('user_id', $user->employerNsrp->employer_nsrp_registrations_id)->first()
                                : null;

                            $reqFieldLabels = [
                                'business_permit'             => 'Business Permit',
                                'sec_dti'                      => 'SEC / DTI Registration',
                                'company_profile'               => 'Company Profile',
                                'nsrp_establishment_form'       => 'NSRP Establishment Form',
                                'no_pending_case_certificate'   => 'No Pending Case Certificate',
                                'vacancy_posting'               => 'Vacancy Posting Document',
                            ];

                            $missingReqs   = [];
                            $submittedReqs = [];
                            foreach ($reqFieldLabels as $field => $label) {
                                if (empty($requirement?->{$field})) {
                                    $missingReqs[] = $label;
                                } else {
                                    $submittedReqs[] = $label;
                                }
                            }
                        } else {
                            $placement = ['job_fair' => 'Job Fair', 'job_vacancy' => 'Job Vacancy', 'lra' => 'LRA', 'sra' => 'SRA'][$user->staff_role] ?? strtoupper($user->staff_role ?? 'Staff');
                        }
                    @endphp
                    <tr data-role="{{ $rowRole }}"
                        data-name="{{ strtolower($user->name) }}"
                        data-email="{{ strtolower($user->email) }}">
                        <td class="row-number" style="font-size:13px;padding:12px 16px;">{{ $index + 1 }}</td>
                        <td style="font-size:13px;padding:12px 16px;font-weight:600;">
                            {{ $user->name }}
                            {{-- Usa ka HR mahimong maghawid ug sobra sa usa ka
                                 kompanya. Ang users.name mao ra ang gipangalan sa
                                 rehistro, mao nga ang uban dili gyud makita kung
                                 dili gyud ilista. --}}
                            @if($user->role === 'company' && ($adminCompanies ?? collect())->count() > 1)
                                <div style="font-size:11px;color:var(--n-500);font-weight:400;margin-top:2px;">
                                    {{ $adminCompanies->count() }} companies:
                                    {{ $adminCompanies->pluck('company_name')->implode(', ') }}
                                </div>
                            @endif
                        </td>
                        <td style="font-size:13px;padding:12px 16px;color:var(--n-700);">
                            {{ $user->email }}
                        </td>
                        <td style="font-size:13px;padding:12px 16px;">
                            {{ $placement }}
                        </td>
                        <td style="font-size:13px;padding:12px 16px;color:var(--n-700);">
                            {{ $user->phone ?? 'None' }}
                        </td>
                        <td style="font-size:13px;padding:12px 16px;color:var(--n-500);text-align:center;">
                            {{ $user->created_at->format('M d, Y') }}
                        </td>
                        <td style="padding:12px 16px;">
                            @if($user->role === 'jobseeker' && $user->registration)
                                <a href="{{ route('admin.registration.view', $user->registration->jobseeker_registrations_id) }}"
                                   class="btn btn-sm fw-semibold"
                                   style="background:var(--g-600);
                                          color:#fff;border:none;border-radius:8px;font-size:12px;text-decoration:none;">
                                    <i class="ph-fill ph-eye me-1"></i>View
                                </a>
                            @elseif($user->role === 'staff')
                                <button type="button" class="btn btn-sm fw-semibold update-user-btn"
                                    style="background:var(--g-600);
                                           color:#fff;border:none;border-radius:8px;font-size:12px;"
                                    data-bs-toggle="modal" data-bs-target="#updateUserModal"
                                    data-id="{{ $user->users_id }}"
                                    data-name="{{ $user->name }}"
                                    data-status="{{ $user->status }}"
                                    data-role="{{ $user->staff_role }}">
                                    <i class="ph ph-note-pencil me-1"></i>Update
                                </button>
                            @elseif($user->role === 'company')
                                <button type="button" class="btn btn-sm fw-semibold view-req-btn"
                                    style="background:var(--g-600);
                                           color:#fff;border:none;border-radius:8px;font-size:12px;"
                                    data-bs-toggle="modal" data-bs-target="#viewReqModal"
                                    data-company="{{ $user->name }}"
                                    data-email="{{ $user->email }}"
                                    data-placement="{{ $placement }}"
                                    data-contact="{{ $user->employerNsrp->mobile_number ?? $user->phone ?? 'None' }}"
                                    data-submitted="{{ implode('|', $submittedReqs) }}"
                                    data-missing="{{ implode('|', $missingReqs) }}">
                                    <i class="ph-fill ph-eye me-1"></i>View
                                </button>
                                {{-- Backup ra kini kung wala ang LRA/SRA nga mo-handle
                                     sa nitawag nga bag-ong HR. Ang Admin walay
                                     kinatibuk-ang katungod sa employer account — ang
                                     403 sa updateUser magpabilin. --}}
                                @if($user->employerNsrp)
                                <button type="button" class="btn btn-sm fw-semibold ms-1"
                                    style="background:#fff;color:var(--g-700);border:1px solid var(--n-200);border-radius:8px;font-size:12px;"
                                    title="Change the authorized contact, or reset this employer's password"
                                    data-bs-toggle="modal" data-bs-target="#recoverModal{{ $user->users_id }}">
                                    <i class="ph ph-user-switch me-1"></i>Change Contact
                                </button>
                                @endif
                            @else
                                <span style="font-size:12px;color:var(--n-400);">
                                    <i class="ph-fill ph-lock-simple me-1"></i>N/A
                                </span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Gawas sa <table> gyud: ang modal sulod sa usa ka row nga gitago dili
         gyud mogawas, kay display:none man ang ginikanan. --}}
    @foreach($users as $user)
        @if($user->role === 'company' && $user->employerNsrp)
            @include('partials.employer-recovery-modal', [
                'employer' => $user,
                'action'   => route('admin.employers.recover', $user->users_id),
                // Ang admin makahatag ug bag-ong password nga walay giusab nga
                // contact. Ang staff nga porma wala niini.
                'canResetPassword' => true,
            ])
        @endif
    @endforeach

    <div id="noResults" class="alert alert-info mt-3 text-center" style="display:none;">
        No users found.
    </div>

    <div id="usersPagination" class="d-flex justify-content-center align-items-center gap-3 mt-3">
        <button type="button" id="prevPageBtn" class="btn btn-sm"
            style="border:1px solid var(--n-200);border-radius:8px;color:var(--g-700);padding:6px 14px;">
            <i class="ph ph-caret-left"></i>
        </button>
        <span id="pageInfo" style="font-size:13px;color:var(--g-700);font-weight:600;"></span>
        <button type="button" id="nextPageBtn" class="btn btn-sm"
            style="border:1px solid var(--n-200);border-radius:8px;color:var(--g-700);padding:6px 14px;">
            <i class="ph ph-caret-right"></i>
        </button>
    </div>
@endif

{{-- UPDATE USER MODAL --}}
<div class="modal fade" id="updateUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px;border:none;overflow:hidden;">
            <div class="modal-header border-0 pb-0"
                style="background:var(--g-600);padding:16px 20px;">
                <h6 class="modal-title fw-bold text-white mb-0">
                    <i class="ph ph-note-pencil me-2"></i>Update Account — <span id="updateUserName"></span>
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                @if($errors->has('update_staff_role'))
                    <div class="alert alert-danger rounded-3 py-2 small mb-3">
                        <i class="ph ph-warning-circle me-1"></i>
                        {{ $errors->first('update_staff_role') }}
                    </div>
                @endif
                <form id="updateUserForm" action="" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="update_user_id" value="{{ old('update_user_id') }}">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small" style="color:var(--g-700);">
                            Placement Type
                        </label>
                        <select name="update_staff_role" id="updateUserRole" class="form-select"
                            style="border:1px solid var(--n-200);border-radius:10px;font-size:13px;">
                            <option value="sra">SRA</option>
                            <option value="lra">LRA</option>
                            <option value="job_fair">Job Fair</option>
                            <option value="job_vacancy">Job Vacancy</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small" style="color:var(--g-700);">
                            Account Status
                        </label>
                        <select name="status" id="updateUserStatus" class="form-select"
                            style="border:1px solid var(--n-200);border-radius:10px;font-size:13px;">
                            <option value="approved">Active</option>
                            <option value="deactivated">Inactive</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold small" style="color:var(--g-700);">
                            New Password
                            <span class="text-muted fw-normal">(leave blank if theres no changes)</span>
                        </label>
                        <div class="position-relative">
                            <input type="password" name="password" id="updateUserPassword"
                                class="form-control"
                                style="border:1px solid var(--n-200);border-radius:10px;
                                       font-size:13px;padding-right:40px;"
                                placeholder="Leave blank to keep current" autocomplete="new-password">
                            <button type="button"
                                style="position:absolute;right:12px;top:50%;
                                       transform:translateY(-50%);background:none;
                                       border:none;color:var(--n-500);cursor:pointer;padding:0;"
                                onclick="toggleUpdatePw()">
                                <i class="ph ph-eye" id="updateUserPwIcon"></i>
                            </button>
                        </div>
                        <div class="d-flex align-items-center gap-2 mt-2">
                            <button type="button" id="useFirstNameBtn"
                                style="border:1px solid var(--n-200);border-radius:8px;background:#fff;
                                       color:var(--g-700);font-size:11.5px;font-weight:600;
                                       padding:4px 10px;cursor:pointer;">
                                <i class="ph ph-user me-1"></i>Use their first name
                            </button>
                        </div>
                        {{-- Anything set here is a starter, never a password
                             they keep: saving it turns must_change_password
                             back on, so the next login goes to the change
                             screen and the full rule applies there. --}}
                        <div style="font-size:11px;line-height:1.5;margin-top:5px;color:var(--n-500);">
                            <i class="ph ph-info me-1"></i>Whatever you set here is temporary.
                            They will be asked to replace it — with an uppercase letter, a number
                            and a special character — the moment they log in.
                        </div>
                    </div>
                    <button type="submit" class="btn w-100 fw-semibold"
                        style="background:var(--g-600);
                               color:#fff;border:none;border-radius:10px;
                               padding:10px;font-size:14px;">
                        <i class="ph ph-floppy-disk me-2"></i>Save Changes
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ADD USER MODAL --}}
<div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px;border:none;overflow:hidden;">
            <div class="modal-header border-0 pb-0"
                style="background:var(--g-600);padding:16px 20px;">
                <h6 class="modal-title fw-bold text-white mb-0">
                    <i class="ph-fill ph-user-plus me-2"></i>Add Staff Account
                </h6>
                <button type="button" class="btn-close btn-close-white"
                    data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                @if(session('staff_error'))
                    <div class="alert alert-danger rounded-3 py-2 small">
                        <i class="ph ph-warning-circle me-1"></i>
                        {{ session('staff_error') }}
                    </div>
                @endif
                <form action="{{ route('admin.users.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold small" style="color:var(--g-700);">
                            Staff Role <span class="text-danger">*</span>
                        </label>
                        <div class="d-flex gap-2 flex-wrap" id="staffRoleGroup">
                            {{-- Ang ihap kay pahibalo, dili utlanan. Usa ka desk
                                 mahimong duha o tulo ka tawo; ang SRA nga naay
                                 katabang dili ikaduhang desk, ikaduha siya ka
                                 tawo sa parehas nga desk. Kaniadto gipatay ang
                                 buton sa rol nga naay tawo, mao nga ang admin
                                 dili gyud makadugang ug ikaduha. --}}
                            @foreach(['sra' => 'SRA', 'lra' => 'LRA', 'job_fair' => 'Job Fair', 'job_vacancy' => 'Job Vacancy'] as $val => $label)
                            @php $held = $staffRoleCounts[$val] ?? 0; @endphp
                            <button type="button" class="staff-role-btn fw-semibold"
                                data-value="{{ $val }}"
                                title="{{ $held ? $held . ' staff member(s) already on this desk' : 'Nobody is on this desk yet' }}"
                                style="border:1px solid var(--n-200);border-radius:8px;
                                       color:var(--g-700);padding:6px 18px;font-size:13px;
                                       background:#fff;cursor:pointer;transition:all 0.2s;">
                                {{ $label }}
                                @if($held)
                                    <span style="font-weight:400;opacity:0.65;">({{ $held }})</span>
                                @endif
                            </button>
                            @endforeach
                            <input type="hidden" name="staff_role" id="staffRoleInput"
                                value="{{ old('staff_role') }}" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small" style="color:var(--g-700);">
                            Full Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="name" class="form-control"
                            style="border:1px solid var(--n-200);border-radius:10px;font-size:13px;"
                            placeholder="e.g. Juan Dela Cruz"
                            value="{{ old('name') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small" style="color:var(--g-700);">
                            Email Address <span class="text-danger">*</span>
                        </label>
                        <input type="email" name="email" class="form-control"
                            style="border:1px solid var(--n-200);border-radius:10px;font-size:13px;"
                            placeholder="e.g. juan@peso.gov.ph"
                            value="{{ old('email') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small" style="color:var(--g-700);">
                            Phone Number
                        </label>
                        <input type="text" name="phone" class="form-control"
                            style="border:1px solid var(--n-200);border-radius:10px;font-size:13px;"
                            inputmode="numeric" maxlength="11" pattern="09[0-9]{9}"
                            placeholder="e.g. 09171234567"
                            value="{{ old('phone') }}">
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold small" style="color:var(--g-700);">
                            Starter Password
                        </label>
                        <div class="position-relative">
                            <input type="text" name="password" id="staffPassword"
                                class="form-control"
                                style="border:1px solid var(--n-200);border-radius:10px;
                                       font-size:13px;padding-right:40px;"
                                placeholder="Fills in from their first name">
                            <button type="button"
                                style="position:absolute;right:12px;top:50%;
                                       transform:translateY(-50%);background:none;
                                       border:none;color:var(--n-500);cursor:pointer;padding:0;"
                                onclick="toggleStaffPw()">
                                <i class="ph ph-eye" id="staffPwIcon"></i>
                            </button>
                        </div>
                        {{-- No strength rule here. This password is a way in,
                             not a way to stay: the account cannot reach a
                             single page until the staff member replaces it,
                             and that replacement is held to the full rule. --}}
                        <div style="font-size:11px;line-height:1.5;margin-top:5px;color:var(--n-500);">
                            <i class="ph ph-info me-1"></i>Their first name, in small letters.
                            Read it to them — they are asked to set their own password,
                            with an uppercase letter, a number and a special character,
                            the first time they log in.
                        </div>
                    </div>
                    <button type="submit" class="btn w-100 fw-semibold"
                        style="background:var(--g-600);
                               color:#fff;border:none;border-radius:10px;
                               padding:10px;font-size:14px;">
                        <i class="ph-fill ph-user-list me-2"></i>Create Account
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- VIEW REQUIREMENTS MODAL --}}
<div class="modal fade" id="viewReqModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius:16px;border:none;overflow:hidden;">
            <div class="modal-header border-0 pb-0"
                style="background:var(--g-600);padding:16px 20px;">
                <h6 class="modal-title fw-bold text-white mb-0">
                    <i class="ph-fill ph-file-text me-2"></i>Company Details
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4" style="padding-top:24px !important;">

                {{-- COMPANY INFO --}}
                <div class="row g-3 mb-4" style="font-size:13px;">
                    <div class="col-md-6">
                        <div style="color:var(--n-500);font-size:11px;">Name</div>
                        <div id="viewReqCompanyName" style="color:var(--g-700);font-weight:700;font-size:14px;"></div>
                    </div>
                    <div class="col-md-6">
                        <div style="color:var(--n-500);font-size:11px;">Email</div>
                        <div id="viewReqEmail" style="color:var(--g-700);font-weight:600;"></div>
                    </div>
                    <div class="col-md-6">
                        <div style="color:var(--n-500);font-size:11px;">Placement Type</div>
                        <div id="viewReqPlacement" style="color:var(--g-700);font-weight:600;"></div>
                    </div>
                    <div class="col-md-6">
                        <div style="color:var(--n-500);font-size:11px;">Contact Number</div>
                        <div id="viewReqContact" style="color:var(--g-700);font-weight:600;"></div>
                    </div>
                </div>

                <hr style="border-color:var(--n-50);">

                {{-- REQUIREMENTS — TWO COLUMNS --}}
                <div class="row g-3 mt-1">
                    <div class="col-md-6">
                        <p class="fw-semibold small mb-2" style="color:var(--g-700);">
                            <i class="ph-fill ph-check-circle me-1"></i>Submitted
                        </p>
                        <ul class="mb-0 ps-3" id="viewReqSubmittedList"></ul>
                    </div>
                    <div class="col-md-6">
                        <p class="fw-semibold small mb-2" style="color:var(--danger);">
                            <i class="ph-fill ph-warning-circle me-1"></i>Missing
                        </p>
                        <ul class="mb-0 ps-3" id="viewReqMissingList"></ul>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<style>
    .nav-tabs .nav-link { color:var(--g-700);border:none;border-bottom:2px solid transparent; }
    .nav-tabs .nav-link.active {
        color:var(--g-700);border-bottom:2px solid var(--g-600);
        background:none;font-weight:700;
    }
    .nav-tabs .nav-link:hover { color:var(--g-600);background:none; }
    .nav-tabs { border-bottom:1px solid var(--n-200); }
    .staff-role-btn:disabled {
        opacity: 0.55;
        cursor: not-allowed;
    }
</style>

<script>
    let activeTab   = 'jobseeker';
    let currentPage = 1;
    const perPage   = 5;

    document.querySelectorAll('#userTabs .nav-link').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('#userTabs .nav-link').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            activeTab   = this.dataset.tab;
            currentPage = 1;
            filterTable();
        });
    });

    document.getElementById('searchInput')?.addEventListener('input', function () {
        currentPage = 1;
        filterTable();
    });

    document.getElementById('sortNameAsc')?.addEventListener('click', () => sortByName('asc'));
    document.getElementById('sortNameDesc')?.addEventListener('click', () => sortByName('desc'));

    document.getElementById('prevPageBtn')?.addEventListener('click', () => {
        if (currentPage > 1) { currentPage--; filterTable(); }
    });
    document.getElementById('nextPageBtn')?.addEventListener('click', () => {
        currentPage++; filterTable();
    });

    function sortByName(direction) {
        const tbody = document.querySelector('#usersTable tbody');
        const rows  = Array.from(tbody.querySelectorAll('tr'));
        rows.sort((a, b) => {
            const nameA = a.dataset.name;
            const nameB = b.dataset.name;
            return direction === 'asc' ? nameA.localeCompare(nameB) : nameB.localeCompare(nameA);
        });
        rows.forEach(row => tbody.appendChild(row));
        currentPage = 1;
        filterTable();
    }

    const tabLabels = { jobseeker: 'jobseeker', company: 'company', staff: 'staff', inactive: 'inactive account' };

    function filterTable() {
        const searchEl = document.getElementById('searchInput');
        if (!searchEl) return;
        const search   = searchEl.value.toLowerCase().trim();
        const allRows  = Array.from(document.querySelectorAll('#usersTable tbody tr'));

        const matched = allRows.filter(row => {
            const role  = row.dataset.role;
            const name  = row.dataset.name;
            const email = row.dataset.email;
            const tabMatch    = role === activeTab;
            const searchMatch = search === '' || name.includes(search) || email.includes(search);
            return tabMatch && searchMatch;
        });

        const totalPages = Math.max(1, Math.ceil(matched.length / perPage));
        if (currentPage > totalPages) currentPage = totalPages;

        const start = (currentPage - 1) * perPage;
        const pageRows = matched.slice(start, start + perPage);

        allRows.forEach(row => row.style.display = 'none');
        pageRows.forEach((row, i) => {
            row.style.display = '';
            const numCell = row.querySelector('.row-number');
            if (numCell) numCell.textContent = start + i + 1;
        });

        document.getElementById('noResults').style.display = matched.length === 0 ? 'block' : 'none';

        const countText = document.getElementById('tabCountText');
        if (countText) {
            const label = tabLabels[activeTab] || activeTab;
            countText.textContent = `${matched.length} ${label}${matched.length === 1 ? '' : 's'} total`;
        }

        const pagination = document.getElementById('usersPagination');
        if (pagination) {
            pagination.style.display = matched.length === 0 ? 'none' : 'flex';
            document.getElementById('pageInfo').textContent = `Page ${currentPage} of ${totalPages}`;
            document.getElementById('prevPageBtn').disabled = currentPage <= 1;
            document.getElementById('nextPageBtn').disabled = currentPage >= totalPages;
        }
    }

    if (document.getElementById('searchInput')) {
        filterTable();
    }

    // Ang password sa bag-ong staff mosunod sa ngalan hangtod nga
    // maghilabot ang admin. Parehas gyud ang lagda sa StarterPassword sa
    // server, mao nga ang makita dinhi mao gyud ang ma-save.
    (function () {
        const nameInput = document.querySelector('#addUserModal input[name="name"]');
        const pwInput   = document.getElementById('staffPassword');
        if (!nameInput || !pwInput) return;

        let edited = pwInput.value.trim() !== '';
        pwInput.addEventListener('input', function () { edited = true; });

        nameInput.addEventListener('input', function () {
            if (edited) return;
            pwInput.value = firstNameKey(nameInput.value);
        });
    })();

    // First word, small letters, letters only — StarterPassword::fromName.
    function firstNameKey(name) {
        const first = (name || '').trim().split(/\s+/)[0] || '';
        const key   = first.toLowerCase().replace(/[^a-z]/g, '');
        return key !== '' ? key : 'peso';
    }

    function toggleStaffPw() {
        const pw   = document.getElementById('staffPassword');
        const icon = document.getElementById('staffPwIcon');
        if (pw.type === 'password') {
            pw.type = 'text';
            icon.className = 'ph ph-eye-slash';
        } else {
            pw.type = 'password';
            icon.className = 'ph ph-eye';
        }
    }

    @if(session('staff_error') || ($errors->any() && old('staff_role')))
        document.addEventListener('DOMContentLoaded', function() {
            new bootstrap.Modal(document.getElementById('addUserModal')).show();
        });
    @endif

    document.querySelectorAll('.staff-role-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            if (this.disabled) return;

            document.querySelectorAll('.staff-role-btn').forEach(b => {
                b.style.background = '#fff';
                b.style.color = 'var(--g-700)';
                b.style.borderColor = 'var(--n-200)';
            });
            this.style.background = 'var(--g-600)';
            this.style.color = '#fff';
            this.style.borderColor = 'transparent';
            document.getElementById('staffRoleInput').value = this.dataset.value;
        });
    });

    const oldRole = '{{ old('staff_role') }}';
    if (oldRole) {
        const btn = document.querySelector(`.staff-role-btn[data-value="${oldRole}"]`);
        if (btn) btn.click();
    }

    // ── UPDATE USER MODAL ──
    document.querySelectorAll('.update-user-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const id     = this.dataset.id;
            const name   = this.dataset.name;
            const status = this.dataset.status;
            const role   = this.dataset.role;

            document.getElementById('updateUserName').textContent = name;
            document.getElementById('updateUserStatus').value = status;
            document.getElementById('updateUserRole').value = role;
            document.getElementById('updateUserPassword').value = '';
            document.getElementById('updateUserPassword').dataset.firstName = firstNameKey(name);
            document.getElementById('updateUserForm').action = `/admin/users/${id}`;
            document.querySelector('#updateUserForm input[name="update_user_id"]').value = id;
        });
    });

    @if($errors->has('update_staff_role') && old('update_user_id'))
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('updateUserForm').action = '/admin/users/{{ old('update_user_id') }}';
            document.getElementById('updateUserRole').value = '{{ old('update_staff_role') }}';
            new bootstrap.Modal(document.getElementById('updateUserModal')).show();
        });
    @endif

    document.getElementById('useFirstNameBtn')?.addEventListener('click', function () {
        const pw = document.getElementById('updateUserPassword');
        pw.value = pw.dataset.firstName || 'peso';
        pw.type  = 'text';
        document.getElementById('updateUserPwIcon').className = 'ph ph-eye-slash';
    });

    function toggleUpdatePw() {
        const pw   = document.getElementById('updateUserPassword');
        const icon = document.getElementById('updateUserPwIcon');
        if (pw.type === 'password') {
            pw.type = 'text';
            icon.className = 'ph ph-eye-slash';
        } else {
            pw.type = 'password';
            icon.className = 'ph ph-eye';
        }
    }

    // ── VIEW REQUIREMENTS MODAL ──
    document.querySelectorAll('.view-req-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('viewReqCompanyName').textContent = this.dataset.company;
            document.getElementById('viewReqEmail').textContent       = this.dataset.email;
            document.getElementById('viewReqPlacement').textContent   = this.dataset.placement;
            document.getElementById('viewReqContact').textContent     = this.dataset.contact;

            const submitted = this.dataset.submitted ? this.dataset.submitted.split('|') : [];
            const missing   = this.dataset.missing   ? this.dataset.missing.split('|')   : [];

            const submittedList = document.getElementById('viewReqSubmittedList');
            const missingList   = document.getElementById('viewReqMissingList');

            submittedList.innerHTML = submitted.length > 0
                ? submitted.map(label => `<li style="font-size:12.5px;color:var(--n-700);margin-bottom:6px;">${label}</li>`).join('')
                : `<li style="font-size:12.5px;color:var(--n-400);list-style:none;margin-left:-18px;">None yet</li>`;

            missingList.innerHTML = missing.length > 0
                ? missing.map(label => `<li style="font-size:12.5px;color:var(--n-700);margin-bottom:6px;">${label}</li>`).join('')
                : `<li style="font-size:12.5px;color:var(--n-400);list-style:none;margin-left:-18px;">None — all complete</li>`;
        });
    });
</script>

@endsection