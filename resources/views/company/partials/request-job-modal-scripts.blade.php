{{--
    Behaviour for the shared "Post a Job" modal.

    Include from @section('scripts') — the layout loads Bootstrap and flatpickr
    after @yield('content'), so this cannot live next to the markup.

    Also exposes bookedDatesList, fpDayCreate(), initFlatpickrs() and
    checkDateAvailability() for the legacy initial-vacancy modal on the jobs page.

    Expects: $company. Optionally $autoOpenRequestJobModal (bool).
--}}
<script>
    const COMPANY_INDUSTRY_GROUP = @json($company->activeCompany()->industry_group ?? '');

    // ── Pila ka kompanya ang kasyahan sa PESO Office kada adlaw. Gikan sa
    // ── config aron usa ra ka lugar ang mausab kung mausab ang numero — ang
    // ── AJAX mo-uli pud niini sa `limit`, mao nga bisan ang mensahe nga
    // ── gitukod sa server ug ang tukod dinhi magkauyon. ──
    const INHOUSE_DAILY_COMPANIES = @json((int) config('peso.schedule.inhouse_daily_companies'));

    // ── Fetch booked (fully occupied) PESO Office dates, i-disable+i-style sa calendar ──
    let bookedDatesList = [];
    fetch(`{{ route('company.inhouse.bookedDates') }}`)
        .then(res => res.json())
        .then(data => { bookedDatesList = data.booked_dates || []; initFlatpickrs(); })
        .catch(() => { initFlatpickrs(); });

    // ── Sarado ang PESO sa holiday, mao nga dili gyud mapili ang maong petsa
    // ── (PESO interview 2026-08-13). Ang weekend mapili gihapon — mang-hangyo
    // ── man ang ubang employer ug ang LRA/SRA maoy mo-desisyon. Ang server
    // ── nag-susi pud niini; ang disable dinhi para sa dali nga tubag ra. ──
    const PESO_HOLIDAYS = @json(\App\Support\Holidays::aroundNow());

    // ── Dili ma-book karong adlawa: naay preparasyon ang PESO sa dili pa
    // ── moabot ang employer. Ang server nagpugong pud niini — ang picker
    // ── para sa dali nga tubag ra. ──
    const PESO_EARLIEST_BOOKABLE = @json(\App\Support\OfficeCalendar::earliestBookableDate());

    function isoOf(date) {
        const y = date.getFullYear();
        const m = String(date.getMonth() + 1).padStart(2, '0');
        const d = String(date.getDate()).padStart(2, '0');
        return `${y}-${m}-${d}`;
    }

    function isHoliday(date) {
        return Object.prototype.hasOwnProperty.call(PESO_HOLIDAYS, isoOf(date));
    }

    function fpDayCreate(dObj, dStr, fp, dayElem) {
        const iso = isoOf(dayElem.dateObj);
        if (PESO_HOLIDAYS[iso]) {
            dayElem.classList.add('fp-date-holiday');
            dayElem.title = PESO_HOLIDAYS[iso] + ' — PESO is closed';
            return;
        }
        if (bookedDatesList.includes(iso)) {
            dayElem.classList.add('fp-date-booked');
            dayElem.title = `PESO Office is full (${INHOUSE_DAILY_COMPANIES}/${INHOUSE_DAILY_COMPANIES} companies) — pick Other Venue to use this date`;
        }
    }

    // ── Ang mga flatpickr instance, ginganlan, aron mausab ang mode sa
    // ── ulahi: ang in-house kay range (window nga gitanyag sa employer), ang
    // ── company interview kay usa ra ka adlaw. ──
    window.pesoPickers = {};

    // Ang duha ka hidden nga field kada picker: didto masulat ang tinuod nga
    // petsa. Ang makita nga input kay pang-display ra.
    const PESO_DATE_TARGETS = {
        preferredDateInput:        { start: 'inhouseDateValue',           end: 'inhouseDateEndValue' },
        initialPreferredDateInput: { start: 'initialPreferredDateValue',  end: 'initialPreferredDateEndValue' },
    };

    function writePesoDates(id, selectedDates) {
        const target = PESO_DATE_TARGETS[id];
        if (!target) return;

        const startEl = document.getElementById(target.start);
        const endEl   = document.getElementById(target.end);
        if (!startEl || !endEl) return;

        startEl.value = selectedDates.length ? isoOf(selectedDates[0]) : '';
        // Usa ka adlaw ra ang gipili: blangko ang end — kana ang porma sa
        // datos nga gipaabot sa server para sa usa ka adlaw nga pangayo.
        endEl.value   = selectedDates.length > 1 ? isoOf(selectedDates[1]) : '';
    }

    function initFlatpickrs() {
        // Ang 3-companies-per-day nga limit sa PESO Office kay para sa In-house
        // ra, mao nga ang booked-date nga disable naa ra sa in-house nga input.
        ['preferredDateInput', 'initialPreferredDateInput'].forEach(function(id) {
            const el = document.getElementById(id);
            if (!el) return;
            window.pesoPickers[id] = flatpickr(el, {
                minDate: PESO_EARLIEST_BOOKABLE,
                dateFormat: 'Y-m-d',
                // Ang in-house nga input sa Request modal kay permi range. Ang
                // initial nga input mag-alagad pud sa company interview, mao nga
                // magsugod siya nga usa ra ka adlaw ug mobalhin sa range kung
                // in-house ang gipili nga schedule type.
                mode: id === 'preferredDateInput' ? 'range' : 'single',
                disable: [function(date) {
                    // Holiday: sarado ang PESO bisan asa nga venue.
                    if (isHoliday(date)) return true;
                    const venueSelId = id === 'initialPreferredDateInput' ? 'initialVenueTypeSelect' : 'venueTypeSelect';
                    const venueEl = document.getElementById(venueSelId);
                    const venueVal = venueEl ? venueEl.value : 'peso_office';
                    if (venueVal !== 'peso_office') return false;
                    return bookedDatesList.includes(isoOf(date));
                }],
                onDayCreate: fpDayCreate,
                onChange: function(selectedDates, dateStr) {
                    writePesoDates(id, selectedDates);
                    el.dispatchEvent(new Event('change'));
                }
            });
        });

        // Ang initial nga porma usa ra ka input alang sa duha ka schedule type.
        // Kung mausab ang gipili, mausab pud ang mode — ug limpyohan ang daan
        // nga pinili, kay ang usa ka range dili masabtan isip usa ka adlaw.
        window.pesoSetDateMode = function (id, mode) {
            const fp = window.pesoPickers[id];
            if (!fp || fp.config.mode === mode) return;
            fp.clear();
            fp.set('mode', mode);
            writePesoDates(id, []);
        };

        const companyInterviewEl = document.getElementById('companyInterviewDateInput');
        if (companyInterviewEl) {
            flatpickr(companyInterviewEl, {
                minDate: PESO_EARLIEST_BOOKABLE,
                dateFormat: 'Y-m-d',
                disable: [isHoliday],
                onDayCreate: fpDayCreate,
            });
        }
    }

    // ── Schedule Type: checkbox na, dili radio — mahimong daghan ang pilion sa
    // ── usa ka request. Kada gipili nga channel mo-abli sa iyang kaugalingon
    // ── nga petsa. Ang per-position nga Deadline field para ra sa Job Fair,
    // ── kay ang In-house ug Company Interview mo-sira sa adlaw sa ilang schedule. ──
    function selectedScheduleTypes() {
        return Array.from(document.querySelectorAll('.schedule-type-check:checked')).map(c => c.value);
    }

    function syncScheduleTypeFields() {
        const types = selectedScheduleTypes();
        const wantsCompanyInterview   = types.includes('company_interview');
        const wantsInhouse  = types.includes('inhouse');
        const wantsJobFair  = types.includes('job_fair');

        const companyInterviewWrap    = document.getElementById('companyInterviewDateWrap');
        const companyInterviewInput   = document.getElementById('companyInterviewDateInput');
        const inhouseFields = document.getElementById('inhouseFields');
        const inhouseInput  = document.getElementById('preferredDateInput');
        const venueSelect   = document.getElementById('venueTypeSelect');
        const venueAddress  = document.getElementById('venueAddressInput');
        const venueWrap     = document.getElementById('venueAddressWrap');

        if (companyInterviewWrap) companyInterviewWrap.style.display = wantsCompanyInterview ? 'block' : 'none';
        if (companyInterviewInput) {
            if (wantsCompanyInterview) companyInterviewInput.setAttribute('required', 'required');
            else { companyInterviewInput.removeAttribute('required'); companyInterviewInput.value = ''; }
        }

        if (inhouseFields) inhouseFields.style.display = wantsInhouse ? 'flex' : 'none';
        if (inhouseInput) {
            if (wantsInhouse) inhouseInput.setAttribute('required', 'required');
            else {
                inhouseInput.removeAttribute('required');
                inhouseInput.value = '';
                // Limpyohi pud ang hidden — kung dili siya i-clear, mopadayon
                // ang daan nga petsa bisan wala nay in-house nga gipili.
                if (window.pesoPickers && window.pesoPickers.preferredDateInput) {
                    window.pesoPickers.preferredDateInput.clear();
                }
                writePesoDates('preferredDateInput', []);
            }
        }
        if (venueSelect) {
            if (wantsInhouse) venueSelect.setAttribute('required', 'required');
            else venueSelect.removeAttribute('required');
        }
        if (!wantsInhouse && venueWrap && venueAddress) {
            venueWrap.style.display = 'none';
            venueAddress.removeAttribute('required');
        }

        // Ang Deadline makita sa tanan nga schedule type. Ang petsa sa
        // interview kay adlaw ra sa pagtagbo — dili kini ang katapusan sa
        // bakante (PESO interview 2026-08-13). Ang employer maoy mopili kung
        // hangtod kanus-a magpabilin ang posting, hangtod usa ka tuig.
        document.querySelectorAll('.deadline-field-wrap').forEach(function(wrap) {
            wrap.style.display = 'block';
        });

        const err = document.getElementById('scheduleTypeError');
        if (err && types.length > 0) err.style.display = 'none';
    }

    document.querySelectorAll('.schedule-type-check').forEach(function(box) {
        box.addEventListener('change', syncScheduleTypeFields);
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
            // Ang puno nga adlaw naka-disable sa PESO Office ra. Pag-usab sa
            // venue, i-redraw ang kalendaryo aron mahimong mapili balik ang
            // maong adlaw — kay sa Other Venue wala man siyay limit.
            if (window.pesoPickers && window.pesoPickers.preferredDateInput) {
                window.pesoPickers.preferredDateInput.redraw();
            }
            checkDateAvailability(this.value);
        });
    }

    // ── Post a Job — Dynamic Add Position ──
    let requestPositionCount = 0;

    function buildRequestPositionRow(idx) {
        return `
        <div class="request-position-row mb-3 p-3" style="border:1px solid var(--n-200);border-radius:12px;background:var(--n-50);position:relative;">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span style="font-size:12px;font-weight:700;color:var(--g-700);">Position #<span class="req-pos-num">${idx + 1}</span></span>
                <button type="button" class="btn btn-sm remove-request-position-btn"
                    style="background:var(--danger-bg);color:var(--danger);border:1px solid var(--danger-br);border-radius:8px;font-size:11px;padding:2px 10px;">
                    <i class="ph-fill ph-trash"></i>
                </button>
            </div>
            <div class="row g-2">
                <div class="col-12">
                    <label class="form-label fw-semibold" style="color:var(--g-700);font-size:12px;">Position Title *</label>
                    <input type="text" name="positions[${idx}][title]" class="form-control" required
                        placeholder="e.g. Sales Associate" style="border-color:var(--n-200);font-size:13px;border-radius:8px;">
                </div>
                <div class="col-md-8">
                    <label class="form-label fw-semibold" style="color:var(--g-700);font-size:12px;">Job Description *</label>
                    <textarea name="positions[${idx}][description]" class="form-control" rows="3" required
                        placeholder="Describe the job responsibilities..."
                        style="border-color:var(--n-200);font-size:13px;border-radius:8px;"></textarea>
                </div>
                <input type="hidden" name="positions[${idx}][industry_group]" value="${COMPANY_INDUSTRY_GROUP}">
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="color:var(--g-700);font-size:12px;">Nature of Work *</label>
                    <select name="positions[${idx}][type]" class="form-select" required
                        style="border-color:var(--n-200);font-size:13px;border-radius:8px;">
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
                    <label class="form-label fw-semibold" style="color:var(--g-700);font-size:12px;">Place of Work *</label>
                    <input type="text" name="positions[${idx}][location]" class="form-control" required
                        placeholder="e.g. Cagayan de Oro City" style="border-color:var(--n-200);font-size:13px;border-radius:8px;">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="color:var(--g-700);font-size:12px;">Salary</label>
                    <input type="text" name="positions[${idx}][salary]" class="form-control"
                        placeholder="e.g. 15,000 / Negotiable" style="border-color:var(--n-200);font-size:13px;border-radius:8px;">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="color:var(--g-700);font-size:12px;">Vacancy Count *</label>
                    <input type="number" name="positions[${idx}][slots]" class="form-control" required min="1"
                        placeholder="e.g. 3" style="border-color:var(--n-200);font-size:13px;border-radius:8px;">
                </div>
                <div class="col-md-12 deadline-field-wrap">
                    <label class="form-label fw-semibold" style="color:var(--g-700);font-size:12px;">Deadline</label>
                    {{-- Default usa ka bulan, max usa ka tuig (PESO interview
                         2026-08-13). Ang server nagsusi pud niini — dili igo
                         ang min/max sa browser. --}}
                    <input type="date" name="positions[${idx}][deadline]" class="form-control"
                        value="{{ now()->addMonth()->toDateString() }}"
                        min="{{ now()->toDateString() }}"
                        max="{{ now()->addYear()->toDateString() }}"
                        style="border-color:var(--n-200);font-size:13px;border-radius:8px;">
                    <small style="font-size:11px;color:var(--n-500);">
                        Defaults to one month. A posting can run for at most one year.
                    </small>
                </div>

                <div class="col-12 mt-2 pt-2" style="border-top:1px dashed var(--n-200);">
                    <div style="font-size:11px;font-weight:700;color:var(--g-700);margin-bottom:8px;">IV. Qualification Requirements</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="color:var(--g-700);font-size:12px;">Work Experience (months)</label>
                    <input type="number" name="positions[${idx}][experience_months]" class="form-control" min="0"
                        style="border-color:var(--n-200);font-size:13px;border-radius:8px;">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="color:var(--g-700);font-size:12px;">Religion</label>
                    <input type="text" name="positions[${idx}][religion]" class="form-control" placeholder="e.g. Any"
                        style="border-color:var(--n-200);font-size:13px;border-radius:8px;">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="color:var(--g-700);font-size:12px;">Sex</label>
                    <select name="positions[${idx}][sex_preference]" class="form-select" style="border-color:var(--n-200);font-size:13px;border-radius:8px;">
                        <option value="Any" selected>No Preference</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="color:var(--g-700);font-size:12px;">Civil Status</label>
                    <select name="positions[${idx}][civil_status]" class="form-select" style="border-color:var(--n-200);font-size:13px;border-radius:8px;">
                        <option value="Any" selected>No Preference</option>
                        <option value="Single">Single</option>
                        <option value="Married">Married</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold" style="color:var(--g-700);font-size:12px;">Other Qualifications</label>
                    <textarea name="positions[${idx}][other_qualifications]" class="form-control" rows="2"
                        style="border-color:var(--n-200);font-size:13px;border-radius:8px;"></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="color:var(--g-700);font-size:12px;">Educational Level</label>
                    <select name="positions[${idx}][education_required]" class="form-select" style="border-color:var(--n-200);font-size:13px;border-radius:8px;">
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
                    <label class="form-label fw-semibold" style="color:var(--g-700);font-size:12px;">Course / Major</label>
                    <input type="text" name="positions[${idx}][course_major]" class="form-control"
                        placeholder="e.g. Business Administration" style="border-color:var(--n-200);font-size:13px;border-radius:8px;">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="color:var(--g-700);font-size:12px;">License</label>
                    <input type="text" name="positions[${idx}][license]" class="form-control"
                        placeholder="e.g. Driver's License" style="border-color:var(--n-200);font-size:13px;border-radius:8px;">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="color:var(--g-700);font-size:12px;">Eligibility</label>
                    <input type="text" name="positions[${idx}][eligibility]" class="form-control"
                        placeholder="e.g. Civil Service Eligible" style="border-color:var(--n-200);font-size:13px;border-radius:8px;">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="color:var(--g-700);font-size:12px;">Certification</label>
                    <input type="text" name="positions[${idx}][certification]" class="form-control"
                        placeholder="e.g. TESDA NC II" style="border-color:var(--n-200);font-size:13px;border-radius:8px;">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="color:var(--g-700);font-size:12px;">Language / Dialect Spoken</label>
                    <input type="text" name="positions[${idx}][language]" class="form-control"
                        placeholder="e.g. Filipino, English, Bisaya" style="border-color:var(--n-200);font-size:13px;border-radius:8px;">
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold" style="color:var(--g-700);font-size:12px;">Preferred Residence</label>
                    <input type="text" name="positions[${idx}][preferred_residence]" class="form-control"
                        placeholder="e.g. Cagayan de Oro City" style="border-color:var(--n-200);font-size:13px;border-radius:8px;">
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold" style="color:var(--g-700);font-size:12px;">Accepts Disability?</label>
                    <div class="d-flex gap-4 mb-2">
                        <div class="form-check">
                            <input class="form-check-input req-disability-yes" type="radio" name="positions[${idx}][accepts_disability]" value="yes">
                            <label class="form-check-label" style="font-size:12px;color:var(--n-700);">Yes</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input req-disability-no" type="radio" name="positions[${idx}][accepts_disability]" value="no" checked>
                            <label class="form-check-label" style="font-size:12px;color:var(--n-700);">No</label>
                        </div>
                    </div>
                    <div class="req-disability-types-wrap d-none gap-3 flex-wrap">
                        ${['Visual','Hearing','Speech','Physical','Others'].map(t => `
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="positions[${idx}][disability_types][]" value="${t}">
                            <label class="form-check-label" style="font-size:12px;color:var(--n-700);">${t}</label>
                        </div>`).join('')}
                    </div>
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold" style="color:var(--g-700);font-size:12px;">Accepts</label>
                    <div class="d-flex gap-3 flex-wrap">
                        ${['PESO','SPES','GIP','JobStart Philippines','K-12 AMP','TraBAJO'].map(p => `
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="positions[${idx}][accepts_programs][]" value="${p}">
                            <label class="form-check-label" style="font-size:12px;color:var(--n-700);">${p}</label>
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
        // Ang bag-o nga row naay Deadline field pud — i-sync dayon sa gipili
        // nga schedule type, kay dili siya maapil sa checkbox nga listener.
        syncScheduleTypeFields();
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

    // ── Naa bay ma-dala nga daan nga vacancy? Kung naa, dili awtomatik nga
    // ── mag-abli ug blangko nga position form — mahimong ang employer mo-check
    // ── ra sa iyang daan nga posting ug wala nay bag-o nga i-type. ──
    function hasBringableList() {
        return document.querySelectorAll('.bring-existing-check').length > 0;
    }

    function checkedExistingCount() {
        return document.querySelectorAll('.bring-existing-check:checked').length;
    }

    // ── Siguraduhon nga naa'y at least usa ka position sa pag-open sa modal, ug
    // ── i-sync ang mga schedule field sa pre-checked nga checkbox (ang listener
    // ── 'change' ra man ang naka-trigger, dili automatic sa pag-load) ──
    document.getElementById('requestJobModal').addEventListener('show.bs.modal', function() {
        if (document.querySelectorAll('.request-position-row').length === 0 && !hasBringableList()) {
            addRequestPosition();
        }
        syncScheduleTypeFields();
    });

    // ── Ang checkbox walay "required", mao nga ang browser dili mo-pugong sa
    // ── blangko nga schedule type — dinhi na i-pugong, dili sa server pa. ──
    document.getElementById('requestJobModal').querySelector('form').addEventListener('submit', function(e) {
        if (selectedScheduleTypes().length === 0) {
            e.preventDefault();
            const err = document.getElementById('scheduleTypeError');
            if (err) {
                err.style.display = 'block';
                err.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            return;
        }

        // Blangko nga request: walay gi-check nga daan ug walay bag-ong position.
        if (checkedExistingCount() === 0 && document.querySelectorAll('.request-position-row').length === 0) {
            e.preventDefault();
            const err = document.getElementById('emptyRequestError');
            if (err) {
                err.style.display = 'block';
                err.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    });

    // Pagkahuman ug check sa daan nga vacancy, tagoon na ang error.
    document.querySelectorAll('.bring-existing-check').forEach(function(box) {
        box.addEventListener('change', function() {
            const err = document.getElementById('emptyRequestError');
            if (err && checkedExistingCount() > 0) err.style.display = 'none';
        });
    });

    // ── Check date availability (AJAX) — venue-aware, ang limit sa PESO
    // ── Office gikan sa config ──
    const preferredDateInputRef = document.getElementById('preferredDateInput');

    // Ang tinuod nga petsa naa sa hidden nga field: ang makita nga input
    // nagpakita ug "2026-08-13 to 2026-08-17" kung range ang gipili.
    function inhousePickedDates() {
        return {
            start: document.getElementById('inhouseDateValue')?.value || '',
            end:   document.getElementById('inhouseDateEndValue')?.value || '',
        };
    }

    function checkDateAvailability(venueType) {
        const note = document.getElementById('dateAvailabilityNote');
        const { start, end } = inhousePickedDates();
        if (!start) { note.textContent = ''; return; }

        note.textContent = 'Checking availability...';
        note.style.color = 'var(--n-500)';

        const query = `date=${start}&date_end=${end || start}&venue_type=${venueType}`;

        fetch(`{{ route('company.inhouse.checkDate') }}?${query}`)
            .then(res => res.json())
            .then(data => {
                // Ang miting sa PESO mo-una: kung wala nay libre nga adlaw
                // sulod sa window, wala nay pulos ang bilang sa venue.
                if (data.office_full) {
                    // Bisan usa ka adlaw nga puliki mobabag sa tibuok range:
                    // ang employer mo-reserba man sa kada adlaw sulod niini.
                    note.innerHTML = (data.office_note || 'PESO is not available on those dates.')
                        + ' Please pick dates that do not include it.';
                    note.style.color = 'var(--danger)';
                    return;
                }

                const officeNote = data.office_note ? ' ' + data.office_note : '';

                if (venueType === 'other') {
                    note.textContent = 'No limit for other venues.' + officeNote;
                    note.style.color = officeNote ? 'var(--warn)' : 'var(--g-700)';
                    return;
                }

                if (data.occupied) {
                    // Ang limit kay sa lawak, dili sa petsa. Ang employer
                    // makakuha gihapon sa iyang gusto nga adlaw kung mag-Other
                    // Venue siya — kana ang unang gitanyag.
                    const cap = data.limit ?? INHOUSE_DAILY_COMPANIES;
                    note.innerHTML = `PESO Office is full (${cap}/${cap} companies) on <strong>`
                        + (data.full_days || []).join(', ') + '</strong>. '
                        + 'Switch the venue to <strong>Other Venue</strong> to keep these dates — '
                        + 'there is no company limit outside the PESO Office.';
                    note.style.color = 'var(--danger)';
                } else {
                    const cap = data.limit ?? INHOUSE_DAILY_COMPANIES;
                    note.textContent = `Available at PESO Office (${data.count}/${cap} at the busiest day).` + officeNote;
                    note.style.color = officeNote ? 'var(--warn)' : 'var(--g-700)';
                }
            })
            .catch(() => { note.textContent = ''; });
    }

    if (preferredDateInputRef) {
        preferredDateInputRef.addEventListener('change', function() {
            const venueType = document.getElementById('venueTypeSelect')?.value || 'peso_office';
            checkDateAvailability(venueType);
        });
    }

@if($autoOpenRequestJobModal ?? false)
    // ── Auto-open on load: the company has not posted any job yet ──
    new bootstrap.Modal(document.getElementById('requestJobModal')).show();
@endif
</script>
