{{-- ── ARCHIVED JOB POSTINGS TAB — nahuman na nga posting: milabay ang deadline o napuno ang slots ── --}}
<div class="mb-3">
    <p class="mb-0" style="font-size:12px;color:var(--n-500);">
        Closed job postings — the deadline passed, or every slot was filled.
        This is the record of the posting itself; placements are under the Placed tab.
    </p>
</div>

@if(($archivedJobs ?? collect())->isEmpty())
    <div class="card border-0 shadow-sm rounded-3 p-5 text-center">
        <i class="ph ph-archive" style="font-size:48px;color:var(--n-300);"></i>
        <div class="mt-3 fw-semibold" style="color:var(--g-700);">No archived postings yet</div>
    </div>
@else
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="background:var(--warn-bg);color:var(--warn);font-size:12px;border:none;padding:10px 16px;">Job Position</th>
                        <th style="background:var(--warn-bg);color:var(--warn);font-size:12px;border:none;padding:10px 16px;">Company</th>
                        <th style="background:var(--warn-bg);color:var(--warn);font-size:12px;border:none;padding:10px 16px;">Posted Month</th>
                        <th style="background:var(--warn-bg);color:var(--warn);font-size:12px;border:none;padding:10px 16px;">Closed Because</th>
                        <th style="background:var(--warn-bg);color:var(--warn);font-size:12px;border:none;padding:10px 16px;text-align:center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($archivedJobs as $archived)
                    <tr style="font-size:13px;">
                        <td style="padding:10px 16px;font-weight:600;color:var(--g-700);">{{ $archived->title }}</td>
                        <td style="padding:10px 16px;color:var(--n-700);">{{ $archived->company->company_name ?? 'None' }}</td>
                        <td style="padding:10px 16px;color:var(--n-500);">{{ $archived->created_at->format('F Y') }}</td>
                        <td style="padding:10px 16px;color:var(--n-700);">
                            @if($archived->hired_count >= $archived->slots)
                                Slots filled
                            @else
                                Deadline passed
                                <div style="font-size:11px;color:var(--n-500);">{{ $archived->deadline?->format('M j, Y') ?? 'None' }}</div>
                            @endif
                        </td>
                        <td style="padding:10px 16px;text-align:center;">
                            <a href="{{ route('staff.reports.job', $archived->job_qualifications_id) }}" class="btn btn-peso-outline btn-sm">
                                <i class="ph ph-eye"></i> View
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($archivedJobs->hasPages())
        <div class="d-flex justify-content-between align-items-center px-3 py-3" style="border-top:1px solid var(--warn-bg);">
            <div style="font-size:12px;color:var(--n-500);">
                Showing {{ $archivedJobs->firstItem() }}–{{ $archivedJobs->lastItem() }} of {{ $archivedJobs->total() }} results
            </div>
            <nav>
                <ul class="pagination pagination-sm mb-0 gap-1">
                    <li class="page-item {{ $archivedJobs->onFirstPage() ? 'disabled' : '' }}">
                        <a class="page-link rounded-2" style="border-color:var(--warn-br);color:var(--warn);" href="{{ $archivedJobs->previousPageUrl() }}"><i class="ph ph-caret-left"></i></a>
                    </li>
                    @foreach($archivedJobs->getUrlRange(1, $archivedJobs->lastPage()) as $page => $url)
                    <li class="page-item {{ $page == $archivedJobs->currentPage() ? 'active' : '' }}">
                        <a class="page-link rounded-2"
                           style="{{ $page == $archivedJobs->currentPage() ? 'background:var(--warn);border-color:transparent;color:#fff;' : 'border-color:var(--warn-br);color:var(--warn);' }}"
                           href="{{ $url }}">{{ $page }}</a>
                    </li>
                    @endforeach
                    <li class="page-item {{ !$archivedJobs->hasMorePages() ? 'disabled' : '' }}">
                        <a class="page-link rounded-2" style="border-color:var(--warn-br);color:var(--warn);" href="{{ $archivedJobs->nextPageUrl() }}"><i class="ph ph-caret-right"></i></a>
                    </li>
                </ul>
            </nav>
        </div>
        @endif
    </div>
@endif
