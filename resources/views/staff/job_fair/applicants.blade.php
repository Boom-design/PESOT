@extends('staff.layouts.app')

@section('content')

@php
    // Usa ka buton para sa tibuok page, dili usa kada tab.
    //
    // Ang padad-an mao ang Highly Qualified UG ang Qualified, sa usa ka
    // pagpindot. Ang tab kay basahonon lang — ang pagbutang ug buton sa matag
    // usa nagpasabot nga duha ka pindot ug duha ka bayad para sa usa ka
    // bakante, ug walay paagi nga masulti kung kinsa ang na-text kaduha.
    //
    // Ang Not Qualified dili gyud maabot sa text. Walay input sa porma nga
    // makapasulod kanila: ang server nagbasa ug fixed nga listahan, dili ug
    // tier nga gikan sa page.
    $notifyTotal = $totalHighly + $totalQualified;
@endphp

<div class="mb-4">
    <a href="{{ route('staff.jobfair.postings', ['status' => 'open']) }}"
       style="font-size:13px;color:var(--g-600);text-decoration:none;">
        <i class="ph ph-arrow-left me-1"></i> Back to Job Fair Vacancies
    </a>
    <h5 class="fw-bold mt-2 mb-1" style="color:var(--g-700);">
        <i class="ph-fill ph-user-list me-2" style="color:var(--g-600);"></i>
        Applicants
    </h5>
    <p class="mb-0" style="font-size:13px;color:var(--n-500);">
        {{ $job->title }} — {{ $job->company->company_name ?? 'None' }}
        @if($event)
            · <i class="ph ph-flag-banner"></i> {{ $event->title }}
            ({{ $event->event_date?->format('M d, Y') ?? 'None' }})
        @endif
    </p>
</div>

{{-- STAT CARDS --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-3 p-3 text-center">
            <div class="fs-2 fw-bold" style="color:var(--g-700);">{{ $totalHighly }}</div>
            <div class="text-muted small">Highly Qualified (75–100%)</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-3 p-3 text-center">
            <div class="fs-2 fw-bold" style="color:var(--warn);">{{ $totalQualified }}</div>
            <div class="text-muted small">Qualified (50–74%)</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-3 p-3 text-center">
            <div class="fs-2 fw-bold" style="color:var(--danger);">{{ $totalNotQualified }}</div>
            <div class="text-muted small">Not Qualified (below 50%)</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-3 p-3 text-center">
            <div class="fs-2 fw-bold" style="color:var(--g-600);">{{ $totalHighly + $totalQualified + $totalNotQualified }}</div>
            <div class="text-muted small">Total Applicants</div>
        </div>
    </div>
</div>

{{-- ── ANG BUTON ──
     Ang staff makakita sa tulo ka butang sa dili pa siya mopindot: pila ang
     tinuod nga maabot, unsa gyud ang mensahe, ug kung nabayran ba ni o test
     mode. Ang na-text na para niini nga bakante wala na apila, mao nga ang
     pagpindot pag-usab dili mag-doble sa gasto. --}}
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-4">
        <div class="d-flex align-items-start gap-3 flex-wrap">
            <div class="flex-grow-1" style="min-width:260px;">
                <div class="fw-bold mb-1" style="color:var(--g-700);font-size:14px;">
                    <i class="ph-fill ph-paper-plane-tilt me-1" style="color:var(--g-600);"></i>
                    Notify the qualified applicants
                </div>
                <div style="font-size:12.5px;color:var(--n-500);">
                    One message to the <strong>Highly Qualified</strong> and the
                    <strong>Qualified</strong> together — {{ $notifyTotal }} applicant(s),
                    of whom <strong>{{ $reachable }}</strong> can receive a text right now.
                    @if($alreadyNotified > 0)
                        {{ $alreadyNotified }} were already notified about this vacancy and are skipped.
                    @endif
                    <div class="mt-1" style="color:var(--n-400);">
                        <i class="ph-fill ph-prohibit me-1"></i>
                        Not Qualified applicants are never texted about this vacancy.
                    </div>
                </div>

                @if($smsText)
                    <div class="mt-3 p-3 rounded-3"
                         style="background:var(--n-50);border:1px solid var(--n-200);
                                font-size:12.5px;color:var(--n-700);">
                        {{ $smsText }}
                    </div>
                    <div class="mt-1" style="font-size:11px;color:var(--n-500);">
                        {{ mb_strlen($smsText) }} characters ·
                        {{ \App\Support\PhilSms::segments($smsText) }} message part(s) per recipient ·
                        recipients cannot reply to this number.
                    </div>
                @endif

                @unless($smsLive)
                    <div class="mt-2 p-2 rounded-3"
                         style="background:var(--warn-bg);border:1px solid var(--warn-br);
                                font-size:12px;color:var(--warn);">
                        <i class="ph-fill ph-flask me-1"></i>
                        Test mode — the SMS gateway is switched off, so nothing is actually sent
                        and nothing is charged. The in-app notification still goes out.
                    </div>
                @endunless
            </div>

            <div style="min-width:200px;">
                @if(!$event)
                    <div class="p-3 rounded-3"
                         style="background:var(--warn-bg);border:1px solid var(--warn-br);
                                font-size:12px;color:var(--warn);">
                        This vacancy is not on a job fair yet. Post it to a fair first.
                    </div>
                @elseif(!$gateMet)
                    <div class="p-3 rounded-3"
                         style="background:var(--warn-bg);border:1px solid var(--warn-br);
                                font-size:12px;color:var(--warn);">
                        {{ $confirmedCount }} of {{ $threshold }} employers confirmed for
                        {{ $event->title }} — jobseekers cannot be notified yet.
                    </div>
                @elseif($reachable === 0)
                    <div class="p-3 rounded-3"
                         style="background:var(--n-50);border:1px solid var(--n-200);
                                font-size:12px;color:var(--n-500);">
                        No qualified applicant left to notify.
                    </div>
                @else
                    <form method="POST"
                          action="{{ route('staff.jobfair.postings.notify', $job->job_qualifications_id) }}"
                          id="notifyForm">
                        @csrf
                        <button type="button" id="notifyButton" class="btn w-100 fw-semibold"
                            data-count="{{ $reachable }}"
                            style="background:var(--g-600);color:#fff;border:none;border-radius:10px;
                                   padding:11px;font-size:13px;">
                            <i class="ph-fill ph-chat-circle-text me-1"></i>
                            Send SMS Notification
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- FILTER --}}
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div class="d-flex gap-2 flex-wrap">
        @foreach(['highly' => 'Highly Qualified', 'qualified' => 'Qualified', 'not_qualified' => 'Not Qualified'] as $val => $label)
        <a href="{{ route('staff.jobfair.postings.applicants', ['id' => $job->job_qualifications_id, 'filter' => $val]) }}"
           class="btn btn-sm fw-semibold"
           style="{{ $filter === $val
               ? 'background:var(--g-600);color:#fff;border:none;'
               : 'border:1px solid var(--n-200);color:var(--g-700);background:#fff;' }}
               border-radius:8px;font-size:12px;padding:5px 16px;">
            {{ $label }}
        </a>
        @endforeach
    </div>
    <div class="input-group" style="max-width:260px;">
        <span class="input-group-text" style="border-color:var(--n-200);background:var(--n-50);">
            <i class="ph ph-magnifying-glass" style="color:var(--g-600);"></i>
        </span>
        <input type="text" id="searchInput" class="form-control"
            placeholder="Search name..."
            style="border-color:var(--n-200);font-size:13px;"
            value="{{ request('search') }}">
    </div>
</div>

{{-- TABLE --}}
<div class="card border-0 shadow-sm rounded-3 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th style="background:var(--g-600);color:#fff;font-size:12px;font-weight:700;border:none;padding:12px 16px;">#</th>
                    <th style="background:var(--g-600);color:#fff;font-size:12px;font-weight:700;border:none;padding:12px 16px;">Jobseeker</th>
                    <th style="background:var(--g-600);color:#fff;font-size:12px;font-weight:700;border:none;padding:12px 16px;">Contact</th>
                    <th style="background:var(--g-600);color:#fff;font-size:12px;font-weight:700;border:none;padding:12px 16px;text-align:center;">Match %</th>
                    <th style="background:var(--g-600);color:#fff;font-size:12px;font-weight:700;border:none;padding:12px 16px;text-align:center;">Qualification</th>
                    <th style="background:var(--g-600);color:#fff;font-size:12px;font-weight:700;border:none;padding:12px 16px;text-align:center;">Date Applied</th>
                </tr>
            </thead>
            <tbody>
                @forelse($applicants as $app)
                    @php
                        $match    = $app->match_percentage ?? 0;
                        $reg      = $app->jobseeker;
                        $fullName = trim(($reg->first_name ?? '') . ' ' . ($reg->surname ?? ''));
                        $regEmail = $reg->reg_email ?? ($reg->user->email ?? null);
                    @endphp
                    <tr style="font-size:13px;">
                        <td style="padding:12px 16px;color:var(--n-500);">
                            {{ $applicants->firstItem() + $loop->index }}
                        </td>
                        <td style="padding:12px 16px;">
                            <div class="d-flex align-items-center gap-2">
                                <div style="width:34px;height:34px;background:var(--g-600);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:13px;font-weight:700;flex-shrink:0;">
                                    {{ strtoupper(substr($fullName ?: 'J', 0, 1)) }}
                                </div>
                                <div>
                                    <div class="fw-semibold" style="color:var(--g-700);">
                                        {{ $fullName ?: 'None' }}
                                    </div>
                                    <div style="font-size:11px;color:var(--n-500);">
                                        {{ $regEmail ?? 'None' }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td style="padding:12px 16px;">
                            <div style="font-size:12px;color:var(--n-700);">
                                <i class="ph ph-phone me-1" style="color:var(--g-600);"></i>
                                {{ $reg->contact_number ?: 'None' }}
                            </div>
                            <div style="font-size:12px;color:var(--n-500);">
                                <i class="ph ph-envelope-simple me-1" style="color:var(--g-600);"></i>
                                {{ $regEmail ?? 'None' }}
                            </div>
                        </td>
                        <td style="padding:12px 16px;text-align:center;">
                            <span class="fw-bold" style="font-size:15px;color:{{ $match >= 75 ? 'var(--g-700)' : ($match >= 50 ? 'var(--warn)' : 'var(--danger)') }}">
                                {{ $match }}%
                            </span>
                        </td>
                        <td style="padding:12px 16px;text-align:center;">
                            @if($match >= 75)
                                <span class="fw-semibold" style="color:var(--g-700);font-size:11px;">Highly Qualified</span>
                            @elseif($match >= 50)
                                <span class="fw-semibold" style="color:var(--warn);font-size:11px;">Qualified</span>
                            @else
                                <span class="fw-semibold" style="color:var(--danger);font-size:11px;">Not Qualified</span>
                            @endif
                        </td>
                        <td style="padding:12px 16px;color:var(--n-500);text-align:center;">
                            {{ $app->created_at?->format('M d, Y') ?? 'None' }}
                        </td>
                    </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-5">
                        <i class="ph ph-tray" style="font-size:40px;color:var(--n-300);"></i>
                        <div class="mt-2 fw-semibold" style="color:var(--g-700);font-size:13px;">No applicants found</div>
                        <div class="text-muted small mt-1">Applicants under this category will appear here</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($applicants->hasPages())
    <div class="d-flex justify-content-between align-items-center px-3 py-3" style="border-top:1px solid var(--n-50);">
        <div style="font-size:12px;color:var(--n-500);">
            Showing {{ $applicants->firstItem() }}–{{ $applicants->lastItem() }} of {{ $applicants->total() }} results
        </div>
        <nav>
            <ul class="pagination pagination-sm mb-0 gap-1">
                <li class="page-item {{ $applicants->onFirstPage() ? 'disabled' : '' }}">
                    <a class="page-link rounded-2" style="border-color:var(--n-200);color:var(--g-700);" href="{{ $applicants->previousPageUrl() }}"><i class="ph ph-caret-left"></i></a>
                </li>
                @foreach($applicants->getUrlRange(1, $applicants->lastPage()) as $page => $url)
                <li class="page-item {{ $page == $applicants->currentPage() ? 'active' : '' }}">
                    <a class="page-link rounded-2"
                       style="{{ $page == $applicants->currentPage() ? 'background:var(--g-600);border-color:transparent;color:#fff;' : 'border-color:var(--n-200);color:var(--g-700);' }}"
                       href="{{ $url }}">{{ $page }}</a>
                </li>
                @endforeach
                <li class="page-item {{ !$applicants->hasMorePages() ? 'disabled' : '' }}">
                    <a class="page-link rounded-2" style="border-color:var(--n-200);color:var(--g-700);" href="{{ $applicants->nextPageUrl() }}"><i class="ph ph-caret-right"></i></a>
                </li>
            </ul>
        </nav>
    </div>
    @endif
</div>

@push('scripts')
<script>
    let searchTimer;
    document.getElementById('searchInput')?.addEventListener('input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
            const url = new URL(window.location.href);
            const value = this.value.trim();
            if (value) {
                url.searchParams.set('search', value);
            } else {
                url.searchParams.delete('search');
            }
            url.searchParams.set('page', 1);
            window.location.href = url.toString();
        }, 500);
    });

    // Usa ka pangutana sa dili pa mogasto. Ang numero mao gyud ang ipadala,
    // gikwenta sa server, dili gikan sa gipakita nga pahina.
    document.getElementById('notifyButton')?.addEventListener('click', function () {
        const count = this.dataset.count;

        Swal.fire({
            title: 'Send the text message?',
            html: '<div style="font-size:14px;">This texts <strong>' + count + '</strong> applicant(s) — '
                  + 'the highly qualified and the qualified together. Not Qualified applicants are not texted. '
                  + 'Every message is paid for and cannot be taken back.</div>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Send now',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#2e7d32',
        }).then((result) => {
            if (result.isConfirmed) {
                this.disabled = true;
                document.getElementById('notifyForm').submit();
            }
        });
    });
</script>
@endpush

@endsection
