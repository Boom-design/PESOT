{{--
    Behaviour for the shared "Request Job Posting" modal.

    Include from @section('scripts') — the layout loads Bootstrap and flatpickr
    after @yield('content'), so this cannot live next to the markup.

    Also exposes bookedDatesList, fpDayCreate(), initFlatpickrs() and
    checkDateAvailability() for the legacy initial-vacancy modal on the jobs page.

    Expects: $company. Optionally $autoOpenRequestJobModal (bool).
--}}
<script>
    const COMPANY_INDUSTRY_GROUP = @json($company->employerNsrp->industry_group ?? '');

    // ── Fetch booked (fully 3/3) PESO Office dates, i-disable+i-style sa calendar ──
    let bookedDatesList = [];
    fetch(`{{ route('company.inhouse.bookedDates') }}`)
        .then(res => res.json())
        .then(data => { bookedDatesList = data.booked_dates || []; initFlatpickrs(); })
        .catch(() => { initFlatpickrs(); });

    function fpDayCreate(dObj, dStr, fp, dayElem) {
        const y = dayElem.dateObj.getFullYear();
        const m = String(dayElem.dateObj.getMonth() + 1).padStart(2, '0');
        const d = String(dayElem.dateObj.getDate()).padStart(2, '0');
        const iso = `${y}-${m}-${d}`;
        if (bookedDatesList.includes(iso)) {
            dayElem.classList.add('fp-date-booked');
            dayElem.title = 'Fully booked (3/3 companies)';
        }
    }

    function initFlatpickrs() {
        ['preferredDateInput', 'initialPreferredDateInput'].forEach(function(id) {
            const el = document.getElementById(id);
            if (!el) return;
            flatpickr(el, {
                minDate: 'today',
                dateFormat: 'Y-m-d',
                disable: [function(date) {
                    const y = date.getFullYear();
                    const m = String(date.getMonth() + 1).padStart(2, '0');
                    const d = String(date.getDate()).padStart(2, '0');
                    const venueSelId = id === 'initialPreferredDateInput' ? 'initialVenueTypeSelect' : 'venueTypeSelect';
                    const venueEl = document.getElementById(venueSelId);
                    const venueVal = venueEl ? venueEl.value : 'peso_office';
                    if (venueVal !== 'peso_office') return false;
                    return bookedDatesList.includes(`${y}-${m}-${d}`);
                }],
                onDayCreate: fpDayCreate,
                onChange: function(selectedDates, dateStr) {
                    el.dispatchEvent(new Event('change'));
                }
            });
        });
    }

    // ── Schedule Type toggle ── (Preferred Date: office_based + inhouse; Venue: inhouse ra; Deadline field per-position: itago kung job_fair)
    function toggleDeadlineFieldsForScheduleType(scheduleType) {
        document.querySelectorAll('.deadline-field-wrap').forEach(function(wrap) {
            wrap.style.display = scheduleType === 'job_fair' ? 'none' : 'block';
        });
    }

    document.querySelectorAll('.schedule-type-radio').forEach(function(radio) {
        radio.addEventListener('change', function() {
            const dateFields  = document.getElementById('dateFields');
            const dateInput   = document.getElementById('preferredDateInput');
            const dateHint    = document.getElementById('preferredDateHint');
            const venueFields = document.getElementById('venueFields');
            const venueSelect = document.getElementById('venueTypeSelect');

            toggleDeadlineFieldsForScheduleType(this.value);

            if (this.value === 'job_fair') {
                dateFields.style.display = 'none';
                dateInput.removeAttribute('required');
                venueSelect.removeAttribute('required');
                document.getElementById('venueAddressInput').removeAttribute('required');
                document.getElementById('venueAddressWrap').style.display = 'none';
            } else {
                dateFields.style.display = 'flex';
                dateInput.setAttribute('required', 'required');

                if (this.value === 'inhouse') {
                    venueFields.style.display = 'block';
                    dateHint.style.display = 'block';
                    venueSelect.setAttribute('required', 'required');
                } else {
                    venueFields.style.display = 'none';
                    dateHint.style.display = 'none';
                    venueSelect.removeAttribute('required');
                    document.getElementById('venueAddressInput').removeAttribute('required');
                    document.getElementById('venueAddressWrap').style.display = 'none';
                }
            }
        });
    });

    // ── Venue Type toggle (PESO Office vs Other) ──
    const venueTypeSelect = document.getElementById('venueTypeSelect');
    if (venueTypeSelect) {
        venueTypeSelect.addEventListener('change', function() {
            const wrap = document.getElementById('venueAddressWrap');
            const addressInput = document.getElementById('venueAddressInput');
            if (this.value === 'other') {
                wrap.style.display = 'block';
                addressInput.setAttribute('required', 'required');
            } else {
                wrap.style.display = 'none';
                addressInput.removeAttribute('required');
            }
            if (preferredDateInputRef && preferredDateInputRef.value) {
                checkDateAvailability(preferredDateInputRef.value, this.value);
            }
        });
    }

    // ── Request Job Posting — Dynamic Add Position ──
    let requestPositionCount = 0;

    function buildRequestPositionRow(idx) {
        return `
        <div class="request-position-row mb-3 p-3" style="border:1.5px solid #a8e6cf;border-radius:12px;background:#f8fdfc;position:relative;">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span style="font-size:12px;font-weight:700;color:#2d7a5f;">Position #<span class="req-pos-num">${idx + 1}</span></span>
                <button type="button" class="btn btn-sm remove-request-position-btn"
                    style="background:#fff5f5;color:#e05252;border:1px solid #ffcdd2;border-radius:8px;font-size:11px;padding:2px 10px;">
                    <i class="bi bi-trash-fill"></i>
                </button>
            </div>
            <div class="row g-2">
                <div class="col-12">
                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Position Title *</label>
                    <input type="text" name="positions[${idx}][title]" class="form-control" required
                        placeholder="e.g. Sales Associate" style="border-color:#a8e6cf;font-size:13px;border-radius:8px;">
                </div>
                <div class="col-md-8">
                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Job Description *</label>
                    <textarea name="positions[${idx}][description]" class="form-control" rows="3" required
                        placeholder="Describe the job responsibilities..."
                        style="border-color:#a8e6cf;font-size:13px;border-radius:8px;"></textarea>
                </div>
                <input type="hidden" name="positions[${idx}][industry_group]" value="${COMPANY_INDUSTRY_GROUP}">
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Nature of Work *</label>
                    <select name="positions[${idx}][type]" class="form-select" required
                        style="border-color:#a8e6cf;font-size:13px;border-radius:8px;">
                        <option value="" disabled selected>Select type</option>
                        <option value="permanent">Permanent</option>
                        <option value="contractual">Contractual</option>
                        <option value="project_based">Project-based</option>
                        <option value="internship">Internship / OJT</option>
                        <option value="part_time">Part-time</option>
                        <option value="work_from_home">Work from home / online job</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Place of Work *</label>
                    <input type="text" name="positions[${idx}][location]" class="form-control" required
                        placeholder="e.g. Cagayan de Oro City" style="border-color:#a8e6cf;font-size:13px;border-radius:8px;">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Salary</label>
                    <input type="text" name="positions[${idx}][salary]" class="form-control"
                        placeholder="e.g. 15,000 / Negotiable" style="border-color:#a8e6cf;font-size:13px;border-radius:8px;">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Vacancy Count *</label>
                    <input type="number" name="positions[${idx}][slots]" class="form-control" required min="1"
                        placeholder="e.g. 3" style="border-color:#a8e6cf;font-size:13px;border-radius:8px;">
                </div>
                <div class="col-md-12 deadline-field-wrap">
                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Deadline</label>
                    <input type="date" name="positions[${idx}][deadline]" class="form-control"
                        style="border-color:#a8e6cf;font-size:13px;border-radius:8px;">
                </div>

                <div class="col-12 mt-2 pt-2" style="border-top:1px dashed #a8e6cf;">
                    <div style="font-size:11px;font-weight:700;color:#2d7a5f;margin-bottom:8px;">IV. Qualification Requirements</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Work Experience (months)</label>
                    <input type="number" name="positions[${idx}][experience_months]" class="form-control" min="0"
                        style="border-color:#a8e6cf;font-size:13px;border-radius:8px;">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Religion</label>
                    <input type="text" name="positions[${idx}][religion]" class="form-control" placeholder="e.g. Any"
                        style="border-color:#a8e6cf;font-size:13px;border-radius:8px;">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Sex</label>
                    <select name="positions[${idx}][sex_preference]" class="form-select" style="border-color:#a8e6cf;font-size:13px;border-radius:8px;">
                        <option value="Any" selected>No Preference</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Civil Status</label>
                    <select name="positions[${idx}][civil_status]" class="form-select" style="border-color:#a8e6cf;font-size:13px;border-radius:8px;">
                        <option value="Any" selected>No Preference</option>
                        <option value="Single">Single</option>
                        <option value="Married">Married</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Other Qualifications</label>
                    <textarea name="positions[${idx}][other_qualifications]" class="form-control" rows="2"
                        style="border-color:#a8e6cf;font-size:13px;border-radius:8px;"></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Educational Level</label>
                    <select name="positions[${idx}][education_required]" class="form-select" style="border-color:#a8e6cf;font-size:13px;border-radius:8px;">
                        <option value="">Any</option>
                        <option value="Elementary">Elementary</option>
                        <option value="High School">High School</option>
                        <option value="Senior High">Senior High</option>
                        <option value="Undergraduate">Undergraduate</option>
                        <option value="College Level">College Level</option>
                        <option value="Tertiary / College">Tertiary / College</option>
                        <option value="Graduate Studies">Graduate Studies</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Course / Major</label>
                    <input type="text" name="positions[${idx}][course_major]" class="form-control"
                        placeholder="e.g. Business Administration" style="border-color:#a8e6cf;font-size:13px;border-radius:8px;">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">License</label>
                    <input type="text" name="positions[${idx}][license]" class="form-control"
                        placeholder="e.g. Driver's License" style="border-color:#a8e6cf;font-size:13px;border-radius:8px;">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Eligibility</label>
                    <input type="text" name="positions[${idx}][eligibility]" class="form-control"
                        placeholder="e.g. Civil Service Eligible" style="border-color:#a8e6cf;font-size:13px;border-radius:8px;">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Certification</label>
                    <input type="text" name="positions[${idx}][certification]" class="form-control"
                        placeholder="e.g. TESDA NC II" style="border-color:#a8e6cf;font-size:13px;border-radius:8px;">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Language / Dialect Spoken</label>
                    <input type="text" name="positions[${idx}][language]" class="form-control"
                        placeholder="e.g. Filipino, English, Bisaya" style="border-color:#a8e6cf;font-size:13px;border-radius:8px;">
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Preferred Residence</label>
                    <input type="text" name="positions[${idx}][preferred_residence]" class="form-control"
                        placeholder="e.g. Cagayan de Oro City" style="border-color:#a8e6cf;font-size:13px;border-radius:8px;">
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Accepts Disability?</label>
                    <div class="d-flex gap-4 mb-2">
                        <div class="form-check">
                            <input class="form-check-input req-disability-yes" type="radio" name="positions[${idx}][accepts_disability]" value="yes">
                            <label class="form-check-label" style="font-size:12px;color:#555;">Yes</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input req-disability-no" type="radio" name="positions[${idx}][accepts_disability]" value="no" checked>
                            <label class="form-check-label" style="font-size:12px;color:#555;">No</label>
                        </div>
                    </div>
                    <div class="req-disability-types-wrap d-none gap-3 flex-wrap">
                        ${['Visual','Hearing','Speech','Physical','Others'].map(t => `
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="positions[${idx}][disability_types][]" value="${t}">
                            <label class="form-check-label" style="font-size:12px;color:#555;">${t}</label>
                        </div>`).join('')}
                    </div>
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Accepts</label>
                    <div class="d-flex gap-3 flex-wrap">
                        ${['PESO','SPES','GIP','JobStart Philippines','K-12 AMP','TraBAJO'].map(p => `
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="positions[${idx}][accepts_programs][]" value="${p}">
                            <label class="form-check-label" style="font-size:12px;color:#555;">${p}</label>
                        </div>`).join('')}
                    </div>
                </div>
            </div>
        </div>`;
    }

    function addRequestPosition() {
        document.getElementById('requestPositionsContainer').insertAdjacentHTML('beforeend', buildRequestPositionRow(requestPositionCount));
        requestPositionCount++;
        updateRequestRemoveButtons();
    }

    function updateRequestRemoveButtons() {
        const rows = document.querySelectorAll('.request-position-row');
        document.querySelectorAll('.remove-request-position-btn').forEach(btn => {
            btn.style.display = rows.length > 1 ? 'inline-block' : 'none';
        });
    }

    function renumberRequestPositions() {
        document.querySelectorAll('.req-pos-num').forEach((el, i) => { el.textContent = i + 1; });
    }

    document.getElementById('addRequestPositionBtn').addEventListener('click', addRequestPosition);

    document.getElementById('requestPositionsContainer').addEventListener('click', function(e) {
        const btn = e.target.closest('.remove-request-position-btn');
        if (btn && !btn.disabled) {
            btn.closest('.request-position-row').remove();
            renumberRequestPositions();
            updateRequestRemoveButtons();
        }
    });

    document.getElementById('requestPositionsContainer').addEventListener('change', function(e) {
        if (e.target.classList.contains('req-disability-yes') || e.target.classList.contains('req-disability-no')) {
            const wrap = e.target.closest('.request-position-row').querySelector('.req-disability-types-wrap');
            if (e.target.classList.contains('req-disability-yes') && e.target.checked) {
                wrap.classList.remove('d-none'); wrap.classList.add('d-flex');
            } else if (e.target.classList.contains('req-disability-no') && e.target.checked) {
                wrap.classList.remove('d-flex'); wrap.classList.add('d-none');
            }
        }
    });

    // ── Siguraduhon nga naa'y at least usa ka position sa pag-open sa modal ──
    document.getElementById('requestJobModal').addEventListener('show.bs.modal', function() {
        if (document.querySelectorAll('.request-position-row').length === 0) {
            addRequestPosition();
        }
    });

    // ── Check date availability (AJAX) — venue-aware, max 3 companies sa PESO Office ──
    const preferredDateInputRef = document.getElementById('preferredDateInput');

    function checkDateAvailability(date, venueType) {
        const note = document.getElementById('dateAvailabilityNote');
        if (venueType === 'other') {
            note.textContent = 'No limit for other venues.';
            note.style.color = '#2d7a5f';
            return;
        }
        note.textContent = 'Checking availability...';
        note.style.color = '#888';

        fetch(`{{ route('company.inhouse.checkDate') }}?date=${date}&venue_type=${venueType}`)
            .then(res => res.json())
            .then(data => {
                if (data.occupied) {
                    note.textContent = 'This date at PESO Office is fully booked (3/3 companies). Please choose another date or venue.';
                    note.style.color = '#e53935';
                } else {
                    note.textContent = `This date is available at PESO Office (${data.count}/3).`;
                    note.style.color = '#2d7a5f';
                }
            })
            .catch(() => { note.textContent = ''; });
    }

    if (preferredDateInputRef) {
        preferredDateInputRef.addEventListener('change', function() {
            const venueType = document.getElementById('venueTypeSelect')?.value || 'peso_office';
            checkDateAvailability(this.value, venueType);
        });
    }

@if($autoOpenRequestJobModal ?? false)
    // ── Auto-open on load: the company has not posted any job yet ──
    new bootstrap.Modal(document.getElementById('requestJobModal')).show();
@endif
</script>
