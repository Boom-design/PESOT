@extends('company.layouts.app')

@section('page-title', 'Qualified Applicants')

@section('content')

<div class="mb-4 fade-in">
    <a href="{{ route('company.jobs') }}" style="font-size:13px;color:#4dd9c0;text-decoration:none;">
        <i class="bi bi-arrow-left me-1"></i> Back to Job Posts
    </a>
    <h5 class="fw-bold mt-2 mb-1" style="color:#2d7a5f;">
        <i class="bi bi-person-check-fill me-2" style="color:#4dd9c0;"></i>
        Qualified Applicants
    </h5>
    <p class="mb-0" style="font-size:13px;color:#888;">{{ $job->title }}</p>
</div>

{{-- STAT CARDS --}}
<div class="row g-3 mb-4 fade-in">
    <div class="col-6 col-md-4">
        <div class="peso-card p-3 text-center">
            <div class="fs-2 fw-bold" style="color:#2d7a5f;">{{ $totalHighly }}</div>
            <div class="text-muted small">Highly Qualified (75–100%)</div>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="peso-card p-3 text-center">
            <div class="fs-2 fw-bold" style="color:#f59e0b;">{{ $totalQualified }}</div>
            <div class="text-muted small">Qualified (50–74%)</div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="peso-card p-3 text-center">
            <div class="fs-2 fw-bold" style="color:#4dd9c0;">{{ $totalHighly + $totalQualified }}</div>
            <div class="text-muted small">Total Qualified</div>
        </div>
    </div>
</div>

{{-- SEARCH --}}
<div class="peso-card mb-3 fade-in">
    <div class="peso-card-body py-2">
        <input type="text" id="searchInput" class="peso-input w-100"
            placeholder="🔍  Search applicant name or email...">
    </div>
</div>

{{-- TABLE --}}
<div class="peso-card fade-in-1">
    @if($applicants->isEmpty())
        <div class="empty-state">
            <div class="empty-icon"><i class="bi bi-person-x"></i></div>
            <h6>No qualified applicants yet</h6>
            <p>Applicants with 50% match and above will appear here.</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="table peso-table mb-0" id="qualifiedTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Applicant</th>
                        <th>Contact</th>
                        <th style="text-align:center;">Match %</th>
                        <th style="text-align:center;">Qualification</th>
                        <th style="text-align:center;">Date Applied</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($applicants as $i => $app)
                    @php
                        $match = $app->match_percentage ?? 0;
                        $reg   = $app->jobseeker->registration ?? null;
                    @endphp
                    <tr class="qualified-row">
                        <td style="color:#888;">{{ $applicants->firstItem() + $loop->index }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div style="width:32px;height:32px;background:linear-gradient(135deg,#90d870,#4dd9c0);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:13px;font-weight:700;flex-shrink:0;">
                                    {{ strtoupper(substr($app->jobseeker->name ?? 'J', 0, 1)) }}
                                </div>
                                <div>
                                    <div class="fw-semibold" style="font-size:13px;color:#2d7a5f;">
                                        {{ $reg->first_name ?? '' }} {{ $reg->surname ?? $app->jobseeker->name ?? '—' }}
                                    </div>
                                    <div style="font-size:11px;color:#888;">
                                        {{ $app->jobseeker->email ?? '—' }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div style="font-size:12px;color:#555;">
                                <i class="bi bi-telephone me-1" style="color:#4dd9c0;"></i>
                                {{ $reg->contact_number ?? $app->jobseeker->phone ?? '—' }}
                            </div>
                            <div style="font-size:12px;color:#888;">
                                <i class="bi bi-envelope me-1" style="color:#4dd9c0;"></i>
                                {{ $app->jobseeker->email ?? '—' }}
                            </div>
                        </td>
                        <td style="text-align:center;">
                            <span class="fw-bold" style="font-size:15px;color:{{ $match >= 75 ? '#2d7a5f' : '#f59e0b' }}">
                                {{ $match }}%
                            </span>
                        </td>
                        <td style="text-align:center;">
                            @if($match >= 75)
                                <span class="badge fw-semibold"
                                    style="background:#2d7a5f;font-size:11px;padding:4px 10px;border-radius:20px;">
                                    Highly Qualified
                                </span>
                            @else
                                <span class="badge fw-semibold"
                                    style="background:#f59e0b;font-size:11px;padding:4px 10px;border-radius:20px;">
                                    Qualified
                                </span>
                            @endif
                        </td>
                        <td style="text-align:center;color:#888;">
                            {{ $app->created_at->format('M d, Y') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- PAGINATION --}}
        @if($applicants->hasPages())
        <div class="d-flex justify-content-between align-items-center px-3 py-3"
            style="border-top:1px solid #f0f9f6;">
            <div style="font-size:12px;color:#888;">
                Showing {{ $applicants->firstItem() }}–{{ $applicants->lastItem() }}
                of {{ $applicants->total() }} results
            </div>
            {{ $applicants->links() }}
        </div>
        @endif
    @endif
</div>

@endsection

@section('scripts')
<script>
    document.getElementById('searchInput')?.addEventListener('input', function () {
        const q = this.value.toLowerCase();
        document.querySelectorAll('.qualified-row').forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(q) ? '' : 'none';
        });
    });
</script>
@endsection