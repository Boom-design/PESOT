{{--
    The Job Fair staff's own report, kept beside the system's.

    PESO Job Fair staff, 2026-08-23: they keep a report by hand that the system
    does not produce. Their words — "ilahi ang report nga gikan sa system ug ang
    excel report nga gi import" — the two are separate and stay separate.
    Nothing on this page is added into any figure on the other tabs, and the
    banner below says so where they will read it.
--}}

<div class="card border-0 shadow-sm rounded-3 p-3 mb-3"
     style="border-left:3px solid var(--g-600) !important;">
    <div style="font-size:12px;color:var(--n-700);">
        <i class="ph-fill ph-info me-1" style="color:var(--g-600);"></i>
        <strong style="color:var(--g-700);">This is your own report.</strong>
        The system stores it and shows it here so it sits with the event, but it does
        not read these numbers into the Post Job Fair Summary or any other tab.
    </div>
</div>

{{-- Ang upload ang nagkinahanglan ug fair, dili ang listahan.

     Ang gi-import nga report gi-file batok sa usa ka fair, mao nga ang porma
     nangutana kung asa. Ang listahan sa ubos wala nagkinahanglan niini: ang
     pangutana didto kay "unsa ang akong gi-upload", ug ang ngalan sa fair naa
     sa matag laray. --}}
@php $importEvents = $allEvents ?? collect(); @endphp

    {{-- ── UPLOAD ── --}}
    <div class="card border-0 shadow-sm rounded-3 p-3 mb-4">
        <div class="fw-semibold mb-3" style="color:var(--g-700);font-size:14px;">
            <i class="ph-fill ph-upload-simple me-2" style="color:var(--g-600);"></i>Import a report
        </div>

        <form method="POST" action="{{ route('staff.reports.jobfair.import') }}"
              enctype="multipart/form-data" class="row g-2 align-items-start">
            @csrf
            <div class="col-12 col-md-4">
                <label class="form-label fw-semibold mb-1" style="font-size:11px;color:var(--n-500);">
                    Job fair event
                </label>
                <select name="job_fair_id" class="form-select form-select-sm"
                        style="border-color:var(--n-200);font-size:12.5px;border-radius:8px;" required>
                    <option value="">— Select the fair this report is for —</option>
                    @foreach($importEvents as $importEvent)
                        <option value="{{ $importEvent->job_fair_events_id }}"
                            {{ (string) $eventId === (string) $importEvent->job_fair_events_id ? 'selected' : '' }}>
                            {{ $importEvent->title }} ({{ $importEvent->event_date->format('M d, Y') }})
                        </option>
                    @endforeach
                </select>
            </div>


            <div class="col-12 col-md-4">
                <label class="form-label fw-semibold mb-1" style="font-size:11px;color:var(--n-500);">
                    Report name
                </label>
                <input type="text" name="title" maxlength="120"
                       value="{{ old('title') }}"
                       class="form-control form-control-sm"
                       style="font-size:12.5px;border-radius:8px;border-color:var(--n-200);"
                       placeholder="e.g. DOLE Monthly Submission" required>
                @error('title')
                    <div class="text-danger" style="font-size:11px;">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12 col-md-5">
                <label class="form-label fw-semibold mb-1" style="font-size:11px;color:var(--n-500);">
                    Excel or CSV file
                </label>
                <input type="file" name="file" accept=".xlsx,.xls,.csv"
                       class="form-control form-control-sm"
                       style="font-size:12.5px;border-radius:8px;border-color:var(--n-200);" required>
                <div style="font-size:11px;color:var(--n-500);margin-top:3px;">
                    Upload the workbook as it is — <strong>.xlsx</strong>, .xls or .csv.
                    The first row is read as the column headings.
                </div>
                @error('file')
                    <div class="text-danger" style="font-size:11px;">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12 col-md-3 d-flex align-items-end" style="min-height:52px;">
                <button type="submit" class="btn btn-sm fw-semibold w-100"
                        style="background:var(--g-600);color:#fff;border:none;border-radius:8px;font-size:12px;padding:6px 14px;">
                    <i class="ph ph-upload-simple me-1"></i>Import
                </button>
            </div>
        </form>
    </div>

    {{-- ── WHAT IS ALREADY HERE ── --}}
    @forelse($importedReports as $report)
    <div class="card border-0 shadow-sm rounded-3 mb-3 overflow-hidden">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 p-3"
             style="border-bottom:1px solid var(--n-50);">
            <div style="min-width:0;">
                <div class="fw-bold" style="color:var(--g-700);font-size:14px;">{{ $report->title }}</div>
                <div style="font-size:11px;color:var(--n-500);">
                    {{ $report->original_filename }}
                    &nbsp;•&nbsp; {{ $report->row_count }} row(s)
                    &nbsp;•&nbsp; {{ $report->created_at->format('M d, Y g:i A') }}
                    @if($report->uploader)
                        &nbsp;•&nbsp; {{ $report->uploader->full_name }}
                    @endif
                </div>
            </div>

            <div class="d-flex gap-1 flex-shrink-0">
                <button type="button" class="btn btn-sm fw-semibold"
                        data-bs-toggle="collapse"
                        data-bs-target="#importedRows{{ $report->job_fair_imported_reports_id }}"
                        style="border:1px solid var(--n-200);color:var(--g-700);background:#fff;border-radius:8px;font-size:12px;">
                    <i class="ph ph-table me-1"></i>View
                </button>
                <a href="{{ route('staff.reports.jobfair.import.download', $report->job_fair_imported_reports_id) }}"
                   class="btn btn-sm fw-semibold"
                   style="border:1px solid var(--n-200);color:var(--g-700);background:#fff;border-radius:8px;font-size:12px;">
                    <i class="ph ph-download-simple me-1"></i>Download
                </a>
                {{-- Walay JavaScript confirm dinhi: ang browser dialog mo-block
                     sa page. Ang buton mo-toggle ug usa ka gamay nga pangutana
                     sa ubos niini. --}}
                <button type="button" class="btn btn-sm fw-semibold"
                        data-bs-toggle="collapse"
                        data-bs-target="#importedDelete{{ $report->job_fair_imported_reports_id }}"
                        style="border:1px solid var(--danger);color:var(--danger);background:#fff;border-radius:8px;font-size:12px;">
                    <i class="ph ph-trash"></i>
                </button>
            </div>
        </div>

        <div class="collapse" id="importedDelete{{ $report->job_fair_imported_reports_id }}">
            <div class="d-flex justify-content-end align-items-center gap-2 px-3 py-2"
                 style="background:#FEF2F2;border-bottom:1px solid var(--n-50);">
                <span style="font-size:12px;color:var(--danger);">
                    Remove "{{ $report->title }}"? The file is not kept anywhere else.
                </span>
                <form method="POST"
                      action="{{ route('staff.reports.jobfair.import.delete', $report->job_fair_imported_reports_id) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm fw-semibold"
                            style="background:var(--danger);color:#fff;border:none;border-radius:8px;font-size:12px;">
                        Remove
                    </button>
                </form>
            </div>
        </div>

        <div class="collapse" id="importedRows{{ $report->job_fair_imported_reports_id }}">
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0">
                    <thead>
                        <tr>
                            @foreach($report->headers as $heading)
                            <th style="background:var(--n-50);color:var(--g-700);font-size:11px;border:none;padding:8px 12px;white-space:nowrap;">
                                {{ $heading }}
                            </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($report->rows as $row)
                        <tr style="font-size:12px;">
                            @foreach($row as $cell)
                            <td style="padding:8px 12px;color:var(--n-700);">{{ $cell }}</td>
                            @endforeach
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ max(count($report->headers), 1) }}" class="text-center py-3"
                                style="color:var(--n-500);font-size:12px;">
                                The file had headings but no rows under them.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @empty
    <div class="card border-0 shadow-sm rounded-3 p-5 text-center">
        <i class="ph ph-file-csv" style="font-size:48px;color:var(--n-300);"></i>
        <div class="mt-3 fw-semibold" style="color:var(--g-700);">
            No report imported yet
        </div>
        <div style="font-size:12px;color:var(--n-500);">
            Use the form above to bring in the report you keep yourself.
        </div>
    </div>
    @endforelse
