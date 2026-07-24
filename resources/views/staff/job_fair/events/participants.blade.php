@extends('staff.layouts.app')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h5 class="fw-bold mb-1" style="color:#2d7a5f;">
            <i class="bi bi-people-fill me-2" style="color:#4dd9c0;"></i>
            {{ $event->title }} — Participants
        </h5>
        <p class="mb-0" style="font-size:13px;color:#888;">
            {{ $event->event_date->format('M d, Y') }} | {{ $event->venue }}
        </p>
    </div>
    <a href="{{ route('staff.jobfair.events') }}"
       class="btn btn-sm fw-semibold"
       style="border:1.5px solid #a8e6cf;color:#2d7a5f;
              background:#fff;border-radius:8px;font-size:13px;">
        <i class="bi bi-arrow-left me-1"></i> Back
    </a>
</div>

{{-- STAT CARDS --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3 p-3 text-center">
            <div class="fs-2 fw-bold" style="color:#f59e0b;">{{ $totalPending }}</div>
            <div class="text-muted small">Pending</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3 p-3 text-center">
            <div class="fs-2 fw-bold" style="color:#2d7a5f;">{{ $totalAccepted }}</div>
            <div class="text-muted small">Accepted</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3 p-3 text-center">
            <div class="fs-2 fw-bold" style="color:#e05252;">{{ $totalDeclined }}</div>
            <div class="text-muted small">Declined</div>
        </div>
    </div>
</div>

{{-- TABLE --}}
@if($participants->isEmpty())
    <div class="card border-0 shadow-sm rounded-3 p-5 text-center">
        <i class="bi bi-people" style="font-size:48px;color:#c0e8dc;"></i>
        <div class="mt-3 fw-semibold" style="color:#2d7a5f;">No participants yet</div>
        <div class="text-muted small mt-1">Send invitations to approved employers first</div>
    </div>
@else
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr style="background:linear-gradient(90deg,#90d870,#4dd9c0);">
                        <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;">#</th>
                        <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;">Company</th>
                        <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;">Email</th>
                        <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;text-align:center;">Status</th>
                        <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;text-align:center;">Date Invited</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($participants as $i => $p)
                    <tr style="font-size:13px;">
                        <td style="padding:12px 16px;color:#888;">{{ $participants->firstItem() + $loop->index }}</td>
                        <td style="padding:12px 16px;font-weight:600;color:#2d7a5f;">
                            {{ $p->employer->company_name ?? $p->employer->name ?? '—' }}
                        </td>
                        <td style="padding:12px 16px;color:#555;">
                            {{ $p->employer->email ?? '—' }}
                        </td>
                        <td style="padding:12px 16px;text-align:center;">
                            @php
                                $badge = [
                                    'pending'  => ['bg' => '#f59e0b', 'label' => 'Pending'],
                                    'confirmed' => ['bg' => '#2d7a5f', 'label' => 'Confirmed'],
                                    'declined' => ['bg' => '#e05252', 'label' => 'Declined'],
                                ][$p->confirmation_status] ?? ['bg' => '#888', 'label' => ucfirst($p->confirmation_status)];
                            @endphp
                            <span class="badge fw-semibold"
                                style="background:{{ $badge['bg'] }};font-size:11px;
                                       padding:4px 10px;border-radius:20px;">
                                {{ $badge['label'] }}
                            </span>
                        </td>
                        <td style="padding:12px 16px;color:#888;text-align:center;">
                            {{ $p->created_at->format('M d, Y') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($participants->hasPages())
        <div class="d-flex justify-content-between align-items-center px-3 py-3"
            style="border-top:1px solid #f0f9f6;">
            <div style="font-size:12px;color:#888;">
                Showing {{ $participants->firstItem() }}–{{ $participants->lastItem() }}
                of {{ $participants->total() }} results
            </div>
            <nav>
                <ul class="pagination pagination-sm mb-0 gap-1">
                    <li class="page-item {{ $participants->onFirstPage() ? 'disabled' : '' }}">
                        <a class="page-link rounded-2" style="border-color:#a8e6cf;color:#2d7a5f;"
                           href="{{ $participants->previousPageUrl() }}">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                    </li>
                    @foreach($participants->getUrlRange(1, $participants->lastPage()) as $page => $url)
                    <li class="page-item {{ $page == $participants->currentPage() ? 'active' : '' }}">
                        <a class="page-link rounded-2"
                           style="{{ $page == $participants->currentPage()
                                ? 'background:linear-gradient(90deg,#90d870,#4dd9c0);border-color:transparent;color:#fff;'
                                : 'border-color:#a8e6cf;color:#2d7a5f;' }}"
                           href="{{ $url }}">{{ $page }}</a>
                    </li>
                    @endforeach
                    <li class="page-item {{ !$participants->hasMorePages() ? 'disabled' : '' }}">
                        <a class="page-link rounded-2" style="border-color:#a8e6cf;color:#2d7a5f;"
                           href="{{ $participants->nextPageUrl() }}">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
        @endif
    </div>
@endif

@endsection