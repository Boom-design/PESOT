{{--
    Shared "Request Job Posting" modal.

    Markup only — the behaviour lives in request-job-modal-scripts.blade.php,
    which must be included from @section('scripts') because the layout loads
    Bootstrap and flatpickr after @yield('content').

    Expects: $company. Optionally $reqStatus ('none'|'pending'|'approved'|'rejected').
--}}
@php
    $reqStatus = $reqStatus ?? (
        \App\Models\EmployerRequirement::where(
            'user_id',
            optional($company->employerNsrp)->employer_nsrp_registrations_id ?? 0
        )->first()?->status ?? 'none'
    );
@endphp

<div class="modal fade" id="requestJobModal" tabindex="-1">
    <div class="modal-dialog modal-lg" style="max-height:90vh;margin-top:5vh;margin-bottom:5vh;">
        <div class="modal-content" style="border-radius:16px;border:none;max-height:90vh;display:flex;flex-direction:column;">
            <div class="modal-header" style="background:linear-gradient(90deg,#90d870,#4dd9c0);flex-shrink:0;">
                <h5 class="modal-title text-white fw-bold">
                    <i class="bi bi-briefcase me-2"></i>Request Job Posting
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('company.jobs.request') }}" method="POST" enctype="multipart/form-data" style="display:flex;flex-direction:column;overflow:hidden;flex:1;">
                @csrf
                <div class="modal-body p-4" style="overflow-y:auto;flex:1;">

                    @if($reqStatus !== 'approved')
                    <div class="d-flex align-items-start gap-2 p-3 mb-4 rounded-3"
                         style="background:#fffbeb;border:1px solid #fde68a;">
                        <i class="bi bi-hourglass-split" style="color:#a16207;font-size:18px;margin-top:1px;"></i>
                        <div style="font-size:12px;color:#7c5b0a;line-height:1.6;">
                            <strong style="color:#a16207;">Your requirements are still under review.</strong><br>
                            You can submit a job posting now. It will stay pending until PESO staff approve both your
                            requirements and this posting, and will only be visible to jobseekers after that.
                        </div>
                    </div>
                    @endif

                    <div class="mb-4">
                        <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:13px;">Schedule Type *</label>
                        <div class="row g-2">
                            @foreach([
                                'office_based' => ['Office Based', 'bi-building'],
                                'inhouse'      => ['In-house', 'bi-calendar-check'],
                                'job_fair'     => ['Job Fair', 'bi-calendar-event'],
                            ] as $val => $opt)
                            <div class="col-md-4">
                                <div class="form-check p-3" style="border:1.5px solid #a8e6cf;border-radius:10px;">
                                    <input class="form-check-input schedule-type-radio" type="radio"
                                        name="schedule_type" value="{{ $val }}" id="sched_{{ $val }}"
                                        {{ $val === 'office_based' ? 'checked' : '' }} required>
                                    <label class="form-check-label" for="sched_{{ $val }}"
                                        style="font-size:12px;color:#2d7a5f;font-weight:600;">
                                        <i class="{{ $opt[1] }} me-1"></i>{{ $opt[0] }}
                                    </label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div id="dateFields" class="row g-3 mb-4" style="display:flex;">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Preferred Date *</label>
                            <input type="text" name="preferred_date" id="preferredDateInput" class="form-control" autocomplete="off"
                                placeholder="Select a date"
                                style="border-color:#a8e6cf;font-size:13px;border-radius:8px;">
                            <small id="preferredDateHint" style="font-size:11px;color:#888;display:none;margin-top:4px;">
                                Interview time will be scheduled between 8:00 AM – 5:00 PM.
                            </small>
                            <div id="dateAvailabilityNote" style="font-size:11px;margin-top:4px;"></div>
                        </div>
                        <div class="col-md-6" id="venueFields" style="display:none;">
                            <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Venue *</label>
                            <select name="venue_type" id="venueTypeSelect" class="form-select"
                                style="border-color:#a8e6cf;font-size:13px;border-radius:8px;">
                                <option value="peso_office">PESO Office</option>
                                <option value="other">Other Venue</option>
                            </select>
                        </div>
                        <div class="col-12" id="venueAddressWrap" style="display:none;">
                            <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Venue Address *</label>
                            <input type="text" name="venue_address" id="venueAddressInput" class="form-control"
                                placeholder="e.g. SM CDO Downtown Premier, 3rd Floor"
                                style="border-color:#a8e6cf;font-size:13px;border-radius:8px;">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">
                            <i class="bi bi-image me-1" style="color:#4dd9c0;"></i>Hiring Poster / Image <span style="font-weight:400;color:#888;">(optional)</span>
                        </label>
                        <div style="font-size:11px;color:#888;margin-bottom:6px;">
                            Upload a hiring poster or flyer image for this posting (e.g. "Hiring Waiter" with a photo) — this will be visible to jobseekers.
                        </div>
                        <input type="file" name="poster_image" class="form-control" accept=".jpg,.jpeg,.png"
                            style="border-color:#a8e6cf;font-size:13px;border-radius:8px;">
                    </div>

                    <div class="mb-3 pb-2" style="border-bottom:2px solid #e8f5f0;">
                        <span class="fw-bold" style="color:#2d7a5f;font-size:13px;">
                            <i class="bi bi-briefcase me-1" style="color:#4dd9c0;"></i> III-IV. Vacancy Details & Qualification Requirements
                        </span>
                    </div>
                    <div style="font-size:12px;color:#888;margin-bottom:12px;">
                        You may add more than one position under this request — each can have its own description and qualifications.
                    </div>

                    <div id="requestPositionsContainer"></div>

                    <button type="button" id="addRequestPositionBtn" class="btn btn-sm fw-semibold mb-4"
                        style="border:1.5px solid #4dd9c0;color:#2d7a5f;background:#e8f8f3;border-radius:8px;font-size:12px;padding:8px 18px;">
                        <i class="bi bi-plus-circle-fill me-1"></i> Add Position
                    </button>

                </div>
                <div class="modal-footer" style="border-top:1px solid #e8f5f0;flex-shrink:0;">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-peso btn-sm px-4">
                        <i class="bi bi-send me-1"></i> Submit Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
