@extends('company.layouts.app')

@section('page-title', 'My Profile')

@section('content')

<div class="mb-4 fade-in">
    <h5 class="fw-bold mb-1" style="color:#2d7a5f;">My Profile</h5>
    <p class="mb-0" style="font-size:13px;color:#888;">Manage your company information and password</p>
</div>

<div class="row g-4">

    {{-- ── PROFILE INFO ── --}}
    <div class="col-lg-7">
        <div class="peso-card fade-in-1">
            <div class="peso-card-header">
                <h6><i class="bi bi-building me-2" style="color:#4dd9c0;"></i>Company Information</h6>
            </div>
            <div class="peso-card-body">
                <form method="POST" action="{{ route('company.profile.update') }}">
                    @csrf
                    <div class="row g-3">

                        <div class="col-12">
                            <label class="peso-label">Company Name *</label>
                            <input type="text" name="company_name" class="peso-input"
                                value="{{ old('company_name', $company->company_name) }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="peso-label">Contact Person *</label>
                            <input type="text" name="contact_person" class="peso-input"
                                value="{{ old('contact_person', $company->contact_person) }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="peso-label">Position / Title *</label>
                            <input type="text" name="position_title" class="peso-input"
                                value="{{ old('position_title', $company->position_title) }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="peso-label">Mobile Number *</label>
                            <input type="text" name="mobile_number" class="peso-input"
                                value="{{ old('mobile_number', $company->mobile_number) }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="peso-label">Email Address *</label>
                            <input type="email" name="email" class="peso-input"
                                value="{{ old('email', $company->email) }}" required>
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn btn-peso px-4">
                                <i class="bi bi-check-lg me-1"></i> Save Changes
                            </button>
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ── RIGHT COLUMN ── --}}
    <div class="col-lg-5">

        {{-- Account Card --}}
        <div class="peso-card fade-in-2 mb-4">
            <div class="peso-card-body text-center" style="padding:28px;">
                <div style="width:72px;height:72px;background:linear-gradient(135deg,#90d870,#4dd9c0);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;font-size:32px;color:#fff;">
                    <i class="bi bi-building"></i>
                </div>
                <div class="fw-bold mb-1" style="font-size:16px;color:#2d7a5f;">
                    {{ $company->company_name ?? '—' }}
                </div>
                <div style="font-size:12px;color:#888;">{{ $company->email }}</div>
                <div class="mt-2">
                    <span style="background:#e8f8f3;color:#2d7a5f;font-size:11px;padding:4px 12px;border-radius:20px;font-weight:600;">
                        {{ strtoupper($company->status ?? 'APPROVED') }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Change Password --}}
        <div class="peso-card fade-in-3">
            <div class="peso-card-header">
                <h6><i class="bi bi-lock me-2" style="color:#4dd9c0;"></i>Change Password</h6>
            </div>
            <div class="peso-card-body">
                <form method="POST" action="{{ route('company.profile.password') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="peso-label">Current Password</label>
                        <div style="position:relative;">
                            <input type="password" name="current_password" id="cp" class="peso-input"
                                placeholder="Enter current password" required>
                            <button type="button" onclick="toggleField('cp','cp-icon')"
                                style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:#888;cursor:pointer;font-size:15px;">
                                <i class="bi bi-eye" id="cp-icon"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="peso-label">New Password</label>
                        <div style="position:relative;">
                            <input type="password" name="new_password" id="np" class="peso-input"
                                placeholder="Min. 6 characters" required>
                            <button type="button" onclick="toggleField('np','np-icon')"
                                style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:#888;cursor:pointer;font-size:15px;">
                                <i class="bi bi-eye" id="np-icon"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="peso-label">Confirm New Password</label>
                        <input type="password" name="new_password_confirmation" class="peso-input"
                            placeholder="Re-type new password" required>
                    </div>
                    <button type="submit" class="btn btn-peso w-100">
                        <i class="bi bi-lock me-1"></i> Change Password
                    </button>
                </form>
            </div>
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
            icon.className = 'bi bi-eye-slash';
        } else {
            input.type = 'password';
            icon.className = 'bi bi-eye';
        }
    }
</script>
@endsection