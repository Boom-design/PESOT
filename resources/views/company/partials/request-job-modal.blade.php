{{--
    Shared "Post a Job" modal.

    Markup only — the behaviour lives in request-job-modal-scripts.blade.php,
    which must be included from @section('scripts') because the layout loads
    Bootstrap and flatpickr after @yield('content').

    Expects: $company. Optionally $reqStatus ('none'|'pending'|'approved'|'rejected')
    and $jobFairId (an event the employer just confirmed).
--}}
@php
    $reqStatus = $reqStatus ?? (
        \App\Models\EmployerRequirement::where(
            'user_id',
            optional($company->activeCompany())->employer_nsrp_registrations_id ?? 0
        )->first()?->status ?? 'none'
    );

@endphp

<div class="modal fade" id="requestJobModal" tabindex="-1">
    <div class="modal-dialog modal-lg" style="max-height:90vh;margin-top:5vh;margin-bottom:5vh;">
        <div class="modal-content" style="border-radius:16px;border:none;max-height:90vh;display:flex;flex-direction:column;">
            <div class="modal-header" style="background:var(--g-600);flex-shrink:0;">
                <h5 class="modal-title text-white fw-bold">
                    <i class="ph ph-briefcase me-2"></i>Post a Job
                </h5>
                {{-- Walay X kung gikan siya sa gidawat nga imbitasyon.

                     Ang pag-ingon ug oo sa usa ka fair ug ang pagsira sa porma
                     nagbilin sa opisina ug booth nga walay bakante — ang
                     employer naa sa listahan, ug walay jobseeker nga naay
                     adtoan didto. Ang tubag sa imbitasyon dili kompleto hangtod
                     naa nay bakante, mao nga usa ra ka gutlo ang duha. --}}
                @unless($jobFairId ?? null)
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                @endunless
            </div>
            <form action="{{ route('company.jobs.request') }}" method="POST" enctype="multipart/form-data" style="display:flex;flex-direction:column;overflow:hidden;flex:1;">
                @csrf
                @if($jobFairId ?? null)
                    <input type="hidden" name="job_fair_id" value="{{ $jobFairId }}">
                @endif
                <div class="modal-body p-4" style="overflow-y:auto;flex:1;">

                    @if($jobFairId ?? null)
                    <div class="d-flex align-items-start gap-2 p-3 mb-4 rounded-3"
                         style="background:var(--g-50);border:1px solid var(--n-200);">
                        <i class="ph-fill ph-calendar-dots" style="color:var(--g-700);font-size:18px;margin-top:1px;"></i>
                        <div style="font-size:12px;color:var(--g-700);line-height:1.6;">
                            {{-- "just confirmed" was wrong for an overseas agency: its yes
                                 stops at accepted while the SRA decides on the slot, and the
                                 form now opens there too. What is true at both moments is
                                 that the invitation was accepted. --}}
                            <strong>Asking to bring this vacancy to the job fair invitation you just accepted.</strong><br>
                            Add the position you want to fill at the fair. PESO staff confirm it
                            onto the event.
                        </div>
                    </div>

                    @endif

                    <div class="d-flex align-items-start gap-2 p-3 mb-4 rounded-3"
                         style="background:var(--n-50);border:1px solid var(--n-200);">
                        <i class="ph ph-info" style="color:var(--g-600);font-size:18px;margin-top:1px;"></i>
                        <div style="font-size:12px;color:var(--n-700);line-height:1.6;">
                            <strong style="color:var(--g-700);">When this posting closes.</strong><br>
                            It stays open until the deadline you set passes, or until every slot is
                            filled — whichever comes first. The interview date you pick below is the
                            day you meet applicants, not the day the posting ends. A posting can run
                            for at most one year. Nothing is deleted: a closed posting moves
                            to Archived Job Postings in Reports, with its requirements and hiring
                            record intact.
                        </div>
                    </div>

                    @if($reqStatus !== 'approved')
                    <div class="d-flex align-items-start gap-2 p-3 mb-4 rounded-3"
                         style="background:var(--warn-bg);border:1px solid var(--warn-br);">
                        <i class="ph ph-hourglass-medium" style="color:var(--warn);font-size:18px;margin-top:1px;"></i>
                        <div style="font-size:12px;color:var(--warn);line-height:1.6;">
                            <strong style="color:var(--warn);">Your requirements are still under review.</strong><br>
                            You can submit a job posting now. It will stay pending until PESO staff approve both your
                            requirements and this posting, and will only be visible to jobseekers after that.
                        </div>
                    </div>
                    @endif

                    {{-- STEP 1 — the position itself. Filled once, no matter how
                         many schedule types it is later posted to. --}}
                    <div class="mb-3 pb-2" style="border-bottom:2px solid var(--n-200);">
                        <span class="fw-bold" style="color:var(--g-700);font-size:13px;">
                            <i class="ph ph-briefcase me-1" style="color:var(--g-600);"></i>
                            III-IV. Vacancy Details &amp; Qualification Requirements
                        </span>
                    </div>
                    <div style="font-size:12px;color:var(--n-500);margin-bottom:12px;">
                        You may add more than one position under this request — each can have its own description and qualifications.
                    </div>

                    <div id="requestPositionsContainer"></div>

                    <button type="button" id="addRequestPositionBtn" class="btn btn-sm fw-semibold mb-2"
                        style="border:1px solid var(--g-500);color:var(--g-700);background:var(--g-50);border-radius:8px;font-size:12px;padding:8px 18px;">
                        <i class="ph-fill ph-plus-circle me-1"></i> Add Position
                    </button>

                    <div id="emptyRequestError" class="mb-4"
                         style="display:none;font-size:12px;color:var(--danger);font-weight:600;">
                        <i class="ph-fill ph-warning-circle me-1"></i>
                        Add at least one position.
                    </div>

                    {{-- STEP 2 — where the position(s) above get posted. More than
                         one may be ticked; each ticked schedule gets its own date
                         and its own applicant list, but they share the vacancy
                         count entered above. --}}
                    <div class="mb-3 pb-2" style="border-bottom:2px solid var(--n-200);">
                        <span class="fw-bold" style="color:var(--g-700);font-size:13px;">
                            <i class="ph ph-calendar-dots me-1" style="color:var(--g-600);"></i> V. Schedule Type
                        </span>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold" style="color:var(--g-700);font-size:13px;">
                            Where should the position(s) above be posted? *
                        </label>
                        <div style="font-size:11px;color:var(--n-500);margin-bottom:8px;">
                            Tick as many as you want — one fill-up covers all of them. The vacancy count
                            you entered is shared: once it is filled, every schedule closes together.
                        </div>

                        <div class="row g-2">
                            @foreach([
                                'company_interview' => ['Company Interview', 'ph ph-buildings',       'Applicants apply and are screened at your office.'],
                                'inhouse'      => ['In-house', 'ph ph-calendar-check',      'Interview day hosted through PESO.'],
                                'job_fair'     => ['Job Fair', 'ph ph-calendar-dots',       'Asks PESO to bring this vacancy to a job fair.'],
                            ] as $val => $opt)
                            <div class="col-md-4">
                                <div class="form-check p-3 h-100" style="border:1px solid var(--n-200);border-radius:10px;">
                                    <input class="form-check-input schedule-type-check" type="checkbox"
                                        name="schedule_types[]" value="{{ $val }}" id="sched_{{ $val }}"
                                        {{ ($jobFairId ?? null) ? ($val === 'job_fair' ? 'checked disabled' : 'disabled') : ($val === 'company_interview' ? 'checked' : '') }}>
                                    <label class="form-check-label" for="sched_{{ $val }}"
                                        style="font-size:12px;color:var(--g-700);font-weight:600;">
                                        <i class="{{ $opt[1] }} me-1"></i>{{ $opt[0] }}
                                    </label>
                                    <div style="font-size:10.5px;color:var(--n-500);line-height:1.4;margin-top:2px;">{{ $opt[2] }}</div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        {{-- A disabled checkbox posts nothing, so the locked job fair
                             flow needs its value carried by a hidden field. --}}
                        @if($jobFairId ?? null)
                            <input type="hidden" name="schedule_types[]" value="job_fair">
                        @endif

                        <div id="scheduleTypeError" class="mt-2" style="display:none;font-size:11.5px;color:var(--danger);">
                            Pick at least one schedule type.
                        </div>
                    </div>

                    {{-- Company Interview date --}}
                    <div id="companyInterviewDateWrap" class="mb-4" style="display:{{ ($jobFairId ?? null) ? 'none' : 'block' }};">
                        <label class="form-label fw-semibold" style="color:var(--g-700);font-size:12px;">
                            <i class="ph ph-buildings me-1" style="color:var(--g-600);"></i>Company Interview — Preferred Date *
                        </label>
                        <input type="text" name="company_interview_date" id="companyInterviewDateInput" class="form-control" autocomplete="off"
                            placeholder="Select a date" style="border-color:var(--n-200);font-size:13px;border-radius:8px;max-width:320px;">
                        <small style="font-size:11px;color:var(--n-500);">
                            The day applicants come in. PESO is closed on holidays, so those
                            dates cannot be picked; weekends can still be requested.
                        </small>
                    </div>

                    {{-- In-house date + venue --}}
                    <div id="inhouseFields" class="row g-3 mb-4" style="display:none;">
                        <div class="col-12">
                            @include('partials.overseas-inhouse-notice')
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="color:var(--g-700);font-size:12px;">
                                <i class="ph ph-calendar-check me-1" style="color:var(--g-600);"></i>In-house — Available Dates *
                            </label>
                            {{-- Ang makita nga input kay pang-display ra; ang gipadala kay
                                 ang duha ka hidden. Range ni: ang employer motanyag kung
                                 kanus-a sila libre, ang LRA/SRA mopili sa aktwal nga adlaw. --}}
                            <input type="text" id="preferredDateInput" class="form-control" autocomplete="off"
                                placeholder="Select a date or a range" style="border-color:var(--n-200);font-size:13px;border-radius:8px;">
                            <input type="hidden" name="inhouse_date"     id="inhouseDateValue">
                            <input type="hidden" name="inhouse_date_end" id="inhouseDateEndValue">
                            <small style="font-size:11px;color:var(--n-500);display:block;margin-top:4px;">
                                Pick one day, or click twice to book a range. Every day you pick is held
                                for you — no other company can take it, and you choose which one to
                                interview on. Interview time is between 8:00 AM – 5:00 PM.
                                The PESO Office takes {{ config('peso.schedule.inhouse_daily_companies') }} companies a day. Holidays cannot be picked; weekends can.
                            </small>
                            <div id="dateAvailabilityNote" style="font-size:11px;margin-top:4px;"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="color:var(--g-700);font-size:12px;">Venue *</label>
                            <select name="venue_type" id="venueTypeSelect" class="form-select"
                                style="border-color:var(--n-200);font-size:13px;border-radius:8px;">
                                <option value="peso_office">PESO Office</option>
                                <option value="other">Other Venue</option>
                            </select>
                            <small style="font-size:11px;color:var(--n-500);display:block;margin-top:4px;">
                                The PESO Office takes {{ config('peso.schedule.inhouse_daily_companies') }} companies a day. Other Venue is your own place —
                                no limit, so a date that is full here is still yours to use there.
                            </small>
                        </div>
                        <div class="col-12" id="venueAddressWrap" style="display:none;">
                            <label class="form-label fw-semibold" style="color:var(--g-700);font-size:12px;">Venue Address *</label>
                            <input type="text" name="venue_address" id="venueAddressInput" class="form-control"
                                placeholder="e.g. SM CDO Downtown Premier, 3rd Floor"
                                style="border-color:var(--n-200);font-size:13px;border-radius:8px;">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold" style="color:var(--g-700);font-size:12px;">
                            <i class="ph ph-image me-1" style="color:var(--g-600);"></i>Hiring Poster / Image <span style="font-weight:400;color:var(--n-500);">(optional)</span>
                        </label>
                        <div style="font-size:11px;color:var(--n-500);margin-bottom:6px;">
                            Upload a hiring poster or flyer image for this posting (e.g. "Hiring Waiter" with a photo) — this will be visible to jobseekers.
                        </div>
                        <input type="file" name="poster_image" class="form-control" accept=".jpg,.jpeg,.png"
                            style="border-color:var(--n-200);font-size:13px;border-radius:8px;">
                    </div>

                </div>
                <div class="modal-footer" style="border-top:1px solid var(--n-200);flex-shrink:0;">
                    @if($jobFairId ?? null)
                        <div class="me-auto" style="font-size:11.5px;color:var(--n-500);">
                            <i class="ph-fill ph-info me-1" style="color:var(--g-600);"></i>
                            Your booth needs a vacancy. Add one to finish accepting.
                        </div>
                    @else
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    @endif
                    <button type="submit" class="btn btn-peso btn-sm px-4">
                        <i class="ph ph-paper-plane-tilt me-1"></i> Post Job
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
