@extends('jobseeker.layouts.app')

@section('page-title', 'My Profile')

@section('content')

<div class="row g-4">

    {{-- ── PROFILE HEADER ── --}}
    <div class="col-md-4">
        <div class="peso-card fade-in text-center">
            <div class="peso-card-body">
                <div style="width:80px; height:80px; background:linear-gradient(135deg,#90d870,#4dd9c0);
                            border-radius:50%; display:flex; align-items:center; justify-content:center;
                            margin:0 auto 16px; font-size:36px; color:#fff;">
                    <i class="bi bi-person"></i>
                </div>
                <h5 style="font-size:16px; font-weight:700; color:#2d7a5f;">
                    {{ $jobseeker->first_name ?? '' }} {{ $jobseeker->last_name ?? $jobseeker->name ?? '' }}
                </h5>
                <span style="background:#e8f8f3; color:#2d7a5f; font-size:11px;
                             padding:4px 12px; border-radius:20px; font-weight:600;">
                    Jobseeker
                </span>
                <div class="mt-3" style="font-size:12px; color:#888;">
                    <i class="bi bi-envelope me-1"></i> {{ $jobseeker->email }}
                </div>
            </div>
        </div>
    </div>

    {{-- ── EDIT PROFILE ── --}}
    <div class="col-md-8">
        <div class="peso-card fade-in">
            <div class="peso-card-header">
                <h6><i class="bi bi-person-gear me-2" style="color:#4dd9c0;"></i>Edit Profile</h6>
            </div>
            <div class="peso-card-body">
                <form action="{{ route('jobseeker.profile.update') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="peso-label">First Name *</label>
                            <input type="text" name="first_name" class="form-control peso-input"
                                   value="{{ $jobseeker->first_name }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="peso-label">Last Name *</label>
                            <input type="text" name="last_name" class="form-control peso-input"
                                   value="{{ $jobseeker->last_name }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="peso-label">Middle Name</label>
                            <input type="text" name="middle_name" class="form-control peso-input"
                                   value="{{ $jobseeker->middle_name }}">
                        </div>
                        <div class="col-md-6">
                            <label class="peso-label">Email *</label>
                            <input type="email" name="email" class="form-control peso-input"
                                   value="{{ $jobseeker->email }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="peso-label">Phone</label>
                            <input type="text" name="phone" class="form-control peso-input"
                                   value="{{ $jobseeker->phone }}">
                        </div>
                        <div class="col-md-6">
                            <label class="peso-label">Date of Birth</label>
                            <input type="date" name="date_of_birth" class="form-control peso-input"
                                   value="{{ $jobseeker->date_of_birth }}">
                        </div>
                    </div>
                    <div class="d-flex justify-content-end mt-3">
                        <button type="submit" class="btn btn-peso px-4">
                            <i class="bi bi-save me-1"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ── CHANGE PASSWORD ── --}}
        <div class="peso-card fade-in mt-4">
            <div class="peso-card-header">
                <h6><i class="bi bi-lock me-2" style="color:#4dd9c0;"></i>Change Password</h6>
            </div>
            <div class="peso-card-body">
                <form action="{{ route('jobseeker.profile.password') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="peso-label">Current Password *</label>
                            <input type="password" name="current_password" class="form-control peso-input" required>
                        </div>
                        <div class="col-md-6">
                            <label class="peso-label">New Password *</label>
                            <input type="password" name="new_password" class="form-control peso-input" required>
                        </div>
                        <div class="col-md-6">
                            <label class="peso-label">Confirm Password *</label>
                            <input type="password" name="new_password_confirmation" class="form-control peso-input" required>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end mt-3">
                        <button type="submit" class="btn btn-peso px-4">
                            <i class="bi bi-lock me-1"></i> Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection