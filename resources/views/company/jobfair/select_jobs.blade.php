@extends('company.layouts.app')

@section('page-title', 'Select Jobs for Job Fair')

@section('content')

<div class="mb-4">
    <a href="{{ route('company.jobfair') }}" style="font-size:13px;color:#4dd9c0;text-decoration:none;">
        <i class="bi bi-arrow-left me-1"></i> Back to Job Fair Invitations
    </a>
    <h5 class="fw-bold mt-2 mb-1" style="color:#2d7a5f;">
        <i class="bi bi-briefcase-fill me-2" style="color:#4dd9c0;"></i>
        Select Jobs to Bring — {{ $jobFair->title }}
    </h5>
    <p class="mb-0" style="font-size:13px;color:#888;">
        Pick which of your open job posts you'll be offering at this job fair.
    </p>
</div>

<div class="card border-0 shadow-sm rounded-3 p-4">
    @if($myJobs->isEmpty())
        <div class="text-center py-4">
            <i class="bi bi-briefcase" style="font-size:40px;color:#c0e8dc;"></i>
            <div class="mt-2 fw-semibold" style="color:#2d7a5f;">No open job posts yet</div>
            <div class="text-muted small">Post a job vacancy first before selecting jobs for this job fair.</div>
        </div>
    @else
        <form action="{{ route('company.jobfair.storeJobSelect', $jobFair->id) }}" method="POST">
            @csrf
            @foreach($myJobs as $job)
            @php
                $typeLabel = match($job->schedule_type) {
                    'job_fair' => ['Job Fair', '#2d7a5f', '#e8f8f3'],
                    'inhouse'  => ['In-house', '#e6a817', '#fff8e1'],
                    default    => ['Office Based', '#0ea5e9', '#e0f2fe'],
                };
            @endphp
            <div class="form-check p-3 mb-2 rounded-3" style="background:#f0f9f6;">
                <input class="form-check-input" type="checkbox" name="job_ids[]" value="{{ $job->job_qualifications_id }}"
                    id="job_{{ $job->job_qualifications_id }}"
                    {{ in_array($job->job_qualifications_id, $selectedJobIds) ? 'checked' : '' }}>
                <label class="form-check-label w-100" for="job_{{ $job->job_qualifications_id }}">
                    <div class="d-flex align-items-center gap-2">
                        <span class="fw-semibold" style="color:#2d7a5f;">{{ $job->title }}</span>
                        <span class="badge fw-semibold" style="background:{{ $typeLabel[2] }};color:{{ $typeLabel[1] }};font-size:10px;padding:3px 9px;border-radius:20px;">
                            {{ $typeLabel[0] }}
                        </span>
                    </div>
                    <div style="font-size:12px;color:#888;">{{ $job->location }} · {{ $job->slots }} slot(s)</div>
                </label>
            </div>
            @endforeach

            <button type="submit" class="btn fw-semibold mt-2"
                style="background:linear-gradient(90deg,#90d870,#4dd9c0);
                       color:#fff;border:none;border-radius:10px;padding:10px 24px;">
                <i class="bi bi-check-circle-fill me-2"></i>Save Job Offers
            </button>
        </form>
    @endif
</div>

@endsection