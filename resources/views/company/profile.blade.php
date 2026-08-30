@extends('company.layouts.app')

@section('page-title', 'My Profile')

@section('content')

@php
    // These fields live on the NSRP registration, not on the user account,
    // so they are read from there.
    $nsrp = $company->activeCompany();
@endphp

{{-- One centred column, same as the jobseeker and staff profiles. This was a
     7/5 split, but the short account card left most of its half empty. --}}
<div style="max-width:820px;margin:0 auto;">

    <div class="mb-4 fade-in">
        <h5 class="fw-bold mb-1" style="color:var(--g-700);">My Profile</h5>
        <p class="mb-0" style="font-size:13px;color:var(--n-500);">
            Manage your company information and password
        </p>
    </div>

    {{-- ── IDENTITY ── --}}
    <div class="peso-card fade-in-1 mb-4">
        <div class="peso-card-body">
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <div style="width:72px;height:72px;background:var(--g-600);flex:0 0 auto;
                            border-radius:50%;display:flex;align-items:center;justify-content:center;
                            font-size:32px;color:#fff;overflow:hidden;">
                    @if($company->profile_photo)
                        <img src="{{ asset('storage/'.$company->profile_photo) }}" alt="Profile photo"
                             style="width:100%;height:100%;object-fit:cover;">
                    @else
                        <i class="ph ph-buildings"></i>
                    @endif
                </div>

                <div style="flex:1 1 220px;min-width:0;">
                    <h5 style="font-size:17px;font-weight:700;color:var(--g-700);margin:0 0 6px;">
                        {{ $nsrp->company_name ?? $company->company_name ?? $company->name }}
                    </h5>
                    <span style="color:var(--g-700);font-size:11px;font-weight:600;">
                        {{ strtoupper($company->status ?? 'APPROVED') }}
                    </span>
                    <div class="mt-2" style="font-size:12px;color:var(--n-500);word-break:break-all;">
                        <i class="ph ph-envelope-simple me-1"></i>{{ $company->email }}
                    </div>
                </div>

                <div style="flex:0 0 auto;">
                    <label class="btn btn-sm fw-semibold mb-0" for="companyPhotoInput"
                        style="border:1px solid var(--n-200);color:var(--g-700);
                               border-radius:8px;font-size:12px;cursor:pointer;">
                        <i class="ph ph-camera me-1"></i>Change Photo
                    </label>
                    {{-- Sits outside the form on purpose; `form=` posts it with the rest. --}}
                    <input type="file" name="profile_photo" id="companyPhotoInput"
                           form="companyProfileForm" accept=".jpg,.jpeg,.png" style="display:none;">
                    @error('profile_photo')
                        <div class="peso-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    {{-- ── COMPANY INFORMATION ── --}}
    <div class="peso-card fade-in-2 mb-4">
        <div class="peso-card-header">
            <h6 class="mb-0"><i class="ph ph-buildings me-2" style="color:var(--g-600);"></i>Company Information</h6>
        </div>
        <div class="peso-card-body">
            <form method="POST" action="{{ route('company.profile.update') }}"
                  enctype="multipart/form-data" id="companyProfileForm">
                @csrf
                <div class="row g-3">
                    <div class="col-12">
                        <label class="peso-label">Company Name *</label>
                        <input type="text" name="company_name" class="peso-input"
                               value="{{ old('company_name', $nsrp->company_name ?? $company->name) }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="peso-label">Contact Person *</label>
                        <input type="text" name="contact_person" class="peso-input"
                               value="{{ old('contact_person', $nsrp->contact_person ?? '') }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="peso-label">Position / Title *</label>
                        <input type="text" name="position_title" class="peso-input"
                               value="{{ old('position_title', $nsrp->position_title ?? '') }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="peso-label">Email Address *</label>
                        <input type="email" name="email" class="peso-input"
                               value="{{ old('email', $company->email) }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="peso-label">Mobile Number *</label>
                        <input type="text" name="mobile_number" class="peso-input"
                               inputmode="numeric" maxlength="11" pattern="09[0-9]{9}"
                               placeholder="09171234567"
                               value="{{ old('mobile_number', $nsrp->mobile_number ?? '') }}" required>
                        <div class="peso-help">
                            11 digits starting with 09. PESO text messages about job fairs go to this number.
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="peso-label">Major Industry Group *</label>
                        <select name="industry_group" class="peso-input" required>
                            <option value="" disabled
                                {{ old('industry_group', $nsrp->industry_group ?? '') ? '' : 'selected' }}>
                                Select industry group
                            </option>
                            @foreach(\App\Models\EmployerNsrpRegistration::INDUSTRY_GROUPS as $group)
                            <option value="{{ $group }}"
                                {{ old('industry_group', $nsrp->industry_group ?? '') === $group ? 'selected' : '' }}>
                                {{ $group }}
                            </option>
                            @endforeach
                        </select>
                        <div class="peso-help">
                            PESO invites employers to a job fair by industry, so a job fair
                            looking for your line of work can find you.
                        </div>
                        @error('industry_group')
                            <div class="peso-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="sms_opt_in" value="1"
                                id="smsOptIn" {{ old('sms_opt_in', $nsrp->sms_opt_in ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="smsOptIn"
                                style="font-size:13px;color:var(--n-700);">
                                Receive SMS notifications from PESO
                            </label>
                            <div class="peso-help" style="margin-top:2px;">
                                Turning this off stops text messages only. You still get
                                notifications inside the system.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4 pt-3" style="border-top:1px solid var(--n-100);">
                    <button type="submit" class="btn btn-peso px-4">
                        <i class="ph ph-floppy-disk me-1"></i>Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── CHANGE PASSWORD ── --}}
    <div class="peso-card fade-in-3">
        <div class="peso-card-header">
            <h6 class="mb-0"><i class="ph ph-lock-simple me-2" style="color:var(--g-600);"></i>Change Password</h6>
        </div>
        <div class="peso-card-body">
            @include('partials.password-gate')

            <form method="POST" action="{{ route('company.profile.password') }}"
                  id="passwordForm" style="display:none;">
                @csrf
                <div class="row g-3">
                    <div class="col-12">
                        <label class="peso-label">Current Password *</label>
                        <div style="position:relative;">
                            <input type="password" name="current_password" id="cp" class="peso-input"
                                   style="padding-right:40px;" placeholder="Enter current password" required>
                            <button type="button" onclick="toggleField('cp','cp-icon')"
                                style="position:absolute;right:12px;top:50%;transform:translateY(-50%);
                                       background:none;border:none;color:var(--n-500);cursor:pointer;font-size:15px;">
                                <i class="ph ph-eye" id="cp-icon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="peso-label">New Password *</label>
                        <div style="position:relative;">
                            <input type="password" name="new_password" id="np" class="peso-input"
                                   style="padding-right:40px;" placeholder="Set a strong password" required>
                            <button type="button" onclick="toggleField('np','np-icon')"
                                style="position:absolute;right:12px;top:50%;transform:translateY(-50%);
                                       background:none;border:none;color:var(--n-500);cursor:pointer;font-size:15px;">
                                <i class="ph ph-eye" id="np-icon"></i>
                            </button>
                        </div>
                        @include('partials.password-hint')
                    </div>

                    <div class="col-md-6">
                        <label class="peso-label">Confirm New Password *</label>
                        <input type="password" name="new_password_confirmation" class="peso-input"
                               placeholder="Re-type new password" required>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4 pt-3" style="border-top:1px solid var(--n-100);">
                    <button type="button" class="btn btn-sm fw-semibold px-4"
                            style="border:1px solid var(--n-200);color:var(--g-700);background:#fff;border-radius:8px;"
                            onclick="togglePasswordForm(false)">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-peso px-4">
                        <i class="ph ph-lock-simple me-1"></i>Change Password
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

@endsection

@section('scripts')
<script>
    function toggleField(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon  = document.getElementById(iconId);
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'ph ph-eye-slash';
        } else {
            input.type = 'password';
            icon.className = 'ph ph-eye';
        }
    }
</script>
@endsection
