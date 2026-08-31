@extends('company.layouts.app')

@section('page-title', '')

@section('content')

@php
    $slotsFull = $job->applications()->where('status', 'hired')->count() >= $job->slots;
@endphp

<div class="mb-4">
    <a href="{{ route('company.reports', ['view' => 'archived']) }}" style="font-size:13px;color:var(--g-600);text-decoration:none;">
        <i class="ph ph-arrow-left me-1"></i> Back to Archived Job Postings
    </a>
    <h5 class="fw-bold mt-2 mb-1" style="color:var(--g-700);">
        <i class="ph-fill ph-briefcase me-2" style="color:var(--g-600);"></i>{{ $job->title }}
    </h5>
    <p class="mb-0" style="font-size:13px;color:var(--n-500);">
        Closed
        @if($slotsFull)
            because every slot was filled.
        @else
            because the deadline passed on {{ $job->deadline?->format('F j, Y') ?? 'an unset date' }}.
        @endif
    </p>
</div>

@include('partials.job-details', ['job' => $job])

@endsection
