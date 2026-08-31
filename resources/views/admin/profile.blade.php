@extends('admin.layouts.app')

@section('content')

{{-- One centred column, same as the jobseeker, staff and employer profiles.
     This was a 6/6 split with the photo buried inside the left form. --}}
<div style="max-width:820px;margin:0 auto;">

    <div class="mb-4">
        <h5 class="fw-bold mb-1" style="color:var(--g-700);">My Profile</h5>
        <p class="mb-0" style="font-size:13px;color:var(--n-500);">
            Manage your account information and password
        </p>
    </div>

    {{-- ── IDENTITY ── --}}
    <div class="peso-card mb-4">
        <div class="peso-card-body">
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <div style="width:72px;height:72px;background:var(--g-600);flex:0 0 auto;
                            border-radius:50%;display:flex;align-items:center;justify-content:center;
                            font-size:32px;color:#fff;overflow:hidden;">
                    @if($admin->profile_photo)
                        <img src="{{ asset('storage/'.$admin->profile_photo) }}" alt="Profile photo"
                             style="width:100%;height:100%;object-fit:cover;">
                    @else
                        <i class="ph ph-user-circle"></i>
                    @endif
                </div>

                <div style="flex:1 1 220px;min-width:0;">
                    <h5 style="font-size:17px;font-weight:700;color:var(--g-700);margin:0 0 6px;">
                        {{ $admin->name }}
                    </h5>
                    <span style="color:var(--g-600);font-size:11px;font-weight:600;">
                        ADMINISTRATOR
                    </span>
                    <div class="mt-2" style="font-size:12px;color:var(--n-500);word-break:break-all;">
                        <i class="ph ph-envelope-simple me-1"></i>{{ $admin->email }}
                    </div>
                </div>

                <div style="flex:0 0 auto;">
                    <label class="btn btn-sm fw-semibold mb-0" for="profilePhotoInput"
                        style="border:1px solid var(--n-200);color:var(--g-700);
                               border-radius:8px;font-size:12px;cursor:pointer;">
                        <i class="ph ph-camera me-1"></i>Change Photo
                    </label>
                    {{-- Sits outside the form on purpose; `form=` posts it with the rest. --}}
                    <input type="file" name="profile_photo" id="profilePhotoInput"
                           form="adminProfileForm" accept=".jpg,.jpeg,.png" style="display:none;">
                    @error('profile_photo')
                        <div class="peso-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    {{-- ── ACCOUNT INFORMATION ── --}}
    <div class="peso-card mb-4">
        <div class="peso-card-header">
            <h6 class="mb-0"><i class="ph ph-user-circle me-2" style="color:var(--g-600);"></i>Account Information</h6>
        </div>
        <div class="peso-card-body">
            <form action="{{ route('admin.profile.update') }}" method="POST"
                  enctype="multipart/form-data" id="adminProfileForm">
                @csrf
                <div class="row g-3">
                    <div class="col-12">
                        <label class="peso-label">Full Name *</label>
                        <input type="text" name="name" value="{{ old('name', $admin->name) }}"
                               class="peso-input @error('name') is-invalid @enderror">
                        @error('name')
                            <div class="peso-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="peso-label">Email Address *</label>
                        <input type="email" name="email" value="{{ old('email', $admin->email) }}"
                               class="peso-input @error('email') is-invalid @enderror">
                        @error('email')
                            <div class="peso-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="peso-label">Phone Number</label>
                        <input type="text" name="phone" value="{{ old('phone', $admin->phone) }}"
                               class="peso-input @error('phone') is-invalid @enderror"
                               inputmode="numeric" maxlength="11" pattern="09[0-9]{9}"
                               placeholder="09171234567">
                        @error('phone')
                            <div class="peso-error">{{ $message }}</div>
                        @enderror
                        <div class="peso-help">11 digits starting with 09.</div>
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
    <div class="peso-card">
        <div class="peso-card-header">
            <h6 class="mb-0"><i class="ph ph-lock-simple me-2" style="color:var(--g-600);"></i>Change Password</h6>
        </div>
        <div class="peso-card-body">
            @include('partials.password-gate')

            <form action="{{ route('admin.profile.password') }}" method="POST"
                  id="passwordForm" style="display:none;">
                @csrf
                <div class="row g-3">
                    <div class="col-12">
                        <label class="peso-label">Current Password *</label>
                        <div style="position:relative;">
                            <input type="password" name="current_password" id="cur_pass"
                                   class="peso-input @error('current_password') is-invalid @enderror"
                                   style="padding-right:40px;" placeholder="Enter current password" required>
                            <button type="button" onclick="togglePass('cur_pass','eye1')"
                                style="position:absolute;right:12px;top:50%;transform:translateY(-50%);
                                       background:none;border:none;color:var(--n-500);cursor:pointer;font-size:15px;">
                                <i class="ph ph-eye" id="eye1"></i>
                            </button>
                        </div>
                        @error('current_password')
                            <div class="peso-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="peso-label">New Password *</label>
                        <div style="position:relative;">
                            <input type="password" name="new_password" id="new_pass"
                                   class="peso-input @error('new_password') is-invalid @enderror"
                                   style="padding-right:40px;" placeholder="Set a strong password" required>
                            <button type="button" onclick="togglePass('new_pass','eye2')"
                                style="position:absolute;right:12px;top:50%;transform:translateY(-50%);
                                       background:none;border:none;color:var(--n-500);cursor:pointer;font-size:15px;">
                                <i class="ph ph-eye" id="eye2"></i>
                            </button>
                        </div>
                        @error('new_password')
                            <div class="peso-error">{{ $message }}</div>
                        @enderror
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
                        <i class="ph ph-shield-check me-1"></i>Change Password
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
function togglePass(inputId, iconId) {
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
