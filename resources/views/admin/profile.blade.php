@extends('admin.layouts.app')

@section('content')

<div class="row g-4">

    {{-- Profile Info --}}
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-3 p-4">
            <h6 class="fw-bold mb-4" style="color:#2d7a5f;">
                <i class="bi bi-person-circle me-2"></i>My Profile
            </h6>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form action="{{ route('admin.profile.update') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label fw-semibold text-muted small">Full Name</label>
                    <input type="text" name="name" value="{{ old('name', $admin->name) }}"
                        class="form-control @error('name') is-invalid @enderror"
                        style="border-color:#a8e6cf;">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold text-muted small">Email Address</label>
                    <input type="email" name="email" value="{{ old('email', $admin->email) }}"
                        class="form-control @error('email') is-invalid @enderror"
                        style="border-color:#a8e6cf;">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold text-muted small">Phone Number</label>
                    <input type="text" name="phone" value="{{ old('phone', $admin->phone) }}"
                        class="form-control @error('phone') is-invalid @enderror"
                        style="border-color:#a8e6cf;"
                        placeholder="None">
                    @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold text-muted small">Role</label>
                    <input type="text" value="Administrator" class="form-control" disabled
                        style="background:#f8f9fa; border-color:#dee2e6;">
                </div>

                <button type="submit" class="btn w-100 fw-semibold"
                    style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;border:none;border-radius:10px;padding:10px;">
                    <i class="bi bi-save me-1"></i> Save Changes
                </button>
            </form>
        </div>
    </div>

    {{-- Change Password --}}
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-3 p-4">
            <h6 class="fw-bold mb-4" style="color:#2d7a5f;">
                <i class="bi bi-lock me-2"></i>Change Password
            </h6>

            <form action="{{ route('admin.profile.password') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label fw-semibold text-muted small">Current Password</label>
                    <div class="input-group">
                        <input type="password" name="current_password" id="cur_pass"
                            class="form-control @error('current_password') is-invalid @enderror"
                            style="border-color:#a8e6cf;">
                        <button type="button" class="btn btn-outline-secondary"
                            onclick="togglePass('cur_pass', 'eye1')">
                            <i class="bi bi-eye-slash" id="eye1"></i>
                        </button>
                        @error('current_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold text-muted small">New Password</label>
                    <div class="input-group">
                        <input type="password" name="new_password" id="new_pass"
                            class="form-control @error('new_password') is-invalid @enderror"
                            style="border-color:#a8e6cf;">
                        <button type="button" class="btn btn-outline-secondary"
                            onclick="togglePass('new_pass', 'eye2')">
                            <i class="bi bi-eye-slash" id="eye2"></i>
                        </button>
                        @error('new_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold text-muted small">Confirm New Password</label>
                    <div class="input-group">
                        <input type="password" name="new_password_confirmation" id="con_pass"
                            class="form-control"
                            style="border-color:#a8e6cf;">
                        <button type="button" class="btn btn-outline-secondary"
                            onclick="togglePass('con_pass', 'eye3')">
                            <i class="bi bi-eye-slash" id="eye3"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn w-100 fw-semibold"
                    style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;border:none;border-radius:10px;padding:10px;">
                    <i class="bi bi-shield-lock me-1"></i> Change Password
                </button>
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
        icon.classList.replace('bi-eye-slash', 'bi-eye');
    } else {
        input.type = 'password';
        icon.classList.replace('bi-eye', 'bi-eye-slash');
    }
}
</script>

@endsection