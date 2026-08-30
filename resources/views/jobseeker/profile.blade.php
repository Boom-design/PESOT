@extends('jobseeker.layouts.app')

@section('page-title', 'My Profile')

@section('content')

@php
    // `users` stores one `name` column — there is no users.first_name,
    // users.last_name or users.date_of_birth. The split name and the birth date
    // live on the NSRP registration, which is also where this form saves them.
    // Reading them off the account is why these boxes came up empty.
    $nameParts     = preg_split('/\s+/', trim($jobseeker->name ?? ''), 2);
    $fallbackFirst = $nameParts[0] ?? '';
    $fallbackLast  = $nameParts[1] ?? '';

    $firstName  = $registration->first_name  ?? $fallbackFirst;
    $lastName   = $registration->surname     ?? $fallbackLast;
    $middleName = $registration->middle_name ?? $jobseeker->middle_name ?? '';

    $displayName = trim($firstName . ' ' . $lastName) ?: ($jobseeker->name ?? 'Jobseeker');

    $birthDate = $registration?->date_of_birth
        ? \Carbon\Carbon::parse($registration->date_of_birth)->format('Y-m-d')
        : '';
@endphp

{{-- One centred column. This was a 4/8 split, but the left card is short and the
     right one is tall, so most of the left half was empty space. --}}
<div style="max-width:820px;margin:0 auto;">

    {{-- ── IDENTITY ── --}}
    <div class="peso-card fade-in mb-4">
        <div class="peso-card-body">
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <div style="width:72px;height:72px;background:var(--g-600);flex:0 0 auto;
                            border-radius:50%;display:flex;align-items:center;justify-content:center;
                            font-size:32px;color:#fff;overflow:hidden;">
                    @if($jobseeker->profile_photo)
                        <img src="{{ asset('storage/'.$jobseeker->profile_photo) }}" alt="Profile photo"
                             style="width:100%;height:100%;object-fit:cover;">
                    @else
                        <i class="ph ph-user"></i>
                    @endif
                </div>

                <div style="flex:1 1 220px;min-width:0;">
                    <h5 style="font-size:17px;font-weight:700;color:var(--g-700);margin:0 0 6px;">
                        {{ $displayName }}
                    </h5>
                    <span style="color:var(--g-700);font-size:11px;font-weight:600;">
                        Jobseeker
                    </span>
                    <div class="mt-2" style="font-size:12px;color:var(--n-500);word-break:break-all;">
                        <i class="ph ph-envelope-simple me-1"></i>{{ $jobseeker->email }}
                    </div>
                </div>

                <div style="flex:0 0 auto;">
                    <label class="btn btn-sm fw-semibold mb-0" for="jobseekerPhotoInput"
                        style="border:1px solid var(--n-200);color:var(--g-700);
                               border-radius:8px;font-size:12px;cursor:pointer;">
                        <i class="ph ph-camera me-1"></i>Change Photo
                    </label>
                    {{-- Sits outside the form on purpose; `form=` posts it with the rest. --}}
                    <input type="file" name="profile_photo" id="jobseekerPhotoInput"
                           form="jobseekerProfileForm" accept=".jpg,.jpeg,.png" style="display:none;">
                    @error('profile_photo')
                        <div class="text-danger" style="font-size:11px;margin-top:4px;">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    {{-- ── EDIT PROFILE ── --}}
    <div class="peso-card fade-in mb-4">
        <div class="peso-card-header">
            <h6 class="mb-0"><i class="ph ph-user-gear me-2" style="color:var(--g-600);"></i>Edit Profile</h6>
        </div>
        <div class="peso-card-body">
            <form action="{{ route('jobseeker.profile.update') }}" method="POST"
                  enctype="multipart/form-data" id="jobseekerProfileForm">
                @csrf
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="peso-label">First Name *</label>
                        <input type="text" name="first_name" class="form-control peso-input"
                               value="{{ old('first_name', $firstName) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="peso-label">Middle Name</label>
                        <input type="text" name="middle_name" class="form-control peso-input"
                               value="{{ old('middle_name', $middleName) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="peso-label">Last Name *</label>
                        <input type="text" name="last_name" class="form-control peso-input"
                               value="{{ old('last_name', $lastName) }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="peso-label">Email *</label>
                        <input type="email" name="email" class="form-control peso-input"
                               value="{{ old('email', $jobseeker->email) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="peso-label">Date of Birth</label>
                        <input type="date" name="date_of_birth" class="form-control peso-input"
                               max="{{ now()->subYears(18)->format('Y-m-d') }}"
                               value="{{ old('date_of_birth', $birthDate) }}">
                        @unless($registration)
                            <div style="font-size:11px;color:var(--n-500);margin-top:5px;">
                                Saved once you complete your NSRP Registration Form.
                            </div>
                        @endunless
                    </div>

                    <div class="col-md-6">
                        <label class="peso-label">Mobile Number</label>
                        <input type="text" name="phone" class="form-control peso-input"
                               inputmode="numeric" maxlength="11" pattern="09[0-9]{9}"
                               placeholder="09171234567"
                               value="{{ old('phone', $registration->contact_number ?? $jobseeker->phone) }}">
                        <div style="font-size:11px;color:var(--n-500);margin-top:5px;">
                            11 digits starting with 09. PESO text messages about job fairs go to this number.
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check" style="margin-top:26px;">
                            <input class="form-check-input" type="checkbox" name="sms_opt_in" value="1"
                                id="smsOptIn" {{ old('sms_opt_in', $registration->sms_opt_in ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="smsOptIn"
                                style="font-size:13px;color:var(--n-700);">
                                Receive SMS notifications from PESO
                            </label>
                            <div style="font-size:11px;color:var(--n-500);margin-top:2px;">
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
    <div class="peso-card fade-in">
        <div class="peso-card-header">
            <h6 class="mb-0"><i class="ph ph-lock-simple me-2" style="color:var(--g-600);"></i>Change Password</h6>
        </div>
        <div class="peso-card-body">
            @include('partials.password-gate')

            <form action="{{ route('jobseeker.profile.password') }}" method="POST"
                  id="passwordForm" style="display:none;">
                @csrf
                <div class="row g-3">
                    <div class="col-12">
                        <label class="peso-label">Current Password *</label>
                        <input type="password" name="current_password" class="form-control peso-input" required>
                    </div>
                    <div class="col-md-6">
                        <label class="peso-label">New Password *</label>
                        <input type="password" name="new_password" class="form-control peso-input" required>
                        @include('partials.password-hint')
                    </div>
                    <div class="col-md-6">
                        <label class="peso-label">Confirm Password *</label>
                        <input type="password" name="new_password_confirmation" class="form-control peso-input" required>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4 pt-3" style="border-top:1px solid var(--n-100);">
                    <button type="button" class="btn btn-sm fw-semibold px-4"
                            style="border:1px solid var(--n-200);color:var(--g-700);background:#fff;border-radius:8px;"
                            onclick="togglePasswordForm(false)">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-peso px-4">
                        <i class="ph ph-lock-simple me-1"></i>Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

@endsection
