@extends('staff.layouts.app')

@section('content')

<div class="mb-4">
    <a href="{{ route('staff.registrations') }}" style="font-size:13px;color:#4dd9c0;text-decoration:none;">
        <i class="bi bi-arrow-left me-1"></i> Back to Local Jobseekers
    </a>
    <h5 class="fw-bold mt-2 mb-0" style="color:#2d7a5f;">Jobseeker Registration Form</h5>
    <div style="font-size:12px;color:#888;">
        Submitted: {{ $registration->created_at->format('F d, Y h:i A') }}
    </div>
</div>

@include('staff.shared.show-content')

@endsection