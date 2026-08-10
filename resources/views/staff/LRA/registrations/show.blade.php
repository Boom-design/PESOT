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

{{-- ── APPLY TO JOB (para sa walk-in jobseekers nga walay email/phone para self-apply) ── --}}
<div class="card border-0 shadow-sm rounded-3 mt-4">
    <div class="card-header border-0 py-3 px-4" style="background:linear-gradient(90deg,#90d870,#4dd9c0);border-radius:12px 12px 0 0;">
        <h6 class="mb-0 fw-bold text-white"><i class="bi bi-send-fill me-2"></i>Apply to a Job Posting</h6>
    </div>
    <div class="card-body p-3">
        @if(session('success'))
        <div class="alert alert-success" style="font-size:13px;">{{ session('success') }}</div>
        @endif
        @if(session('error'))
        <div class="alert alert-danger" style="font-size:13px;">{{ session('error') }}</div>
        @endif
        @error('job_id')
        <div class="alert alert-danger" style="font-size:13px;">{{ $message }}</div>
        @enderror
        <div style="font-size:12px;color:#888;margin-bottom:12px;">
            For walk-in jobseekers without an account/contact info to self-apply — staff can apply on their behalf.
        </div>
        @if($openJobs->isEmpty())
            <div style="font-size:13px;color:#888;">No open job postings available at the moment.</div>
        @else
            <form action="{{ route('staff.registrations.apply', $registration->id) }}" method="POST" class="d-flex gap-2 flex-wrap">
                @csrf
                <select name="job_id" class="form-select form-select-sm" style="max-width:400px;border-color:#a8e6cf;font-size:13px;" required>
                    <option value="">— Select a job posting —</option>
                    @foreach($openJobs as $job)
                    <option value="{{ $job->id }}">{{ $job->title }} — {{ $job->company->company_name ?? '—' }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-sm fw-semibold"
                    style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;border:none;border-radius:8px;font-size:12px;padding:8px 18px;">
                    <i class="bi bi-send-fill me-1"></i> Apply
                </button>
            </form>
        @endif
    </div>
</div>

@endsection