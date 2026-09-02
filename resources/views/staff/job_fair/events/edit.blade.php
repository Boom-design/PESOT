@extends('staff.layouts.app')

@section('content')

<div class="mb-4">
    <h5 class="fw-bold mb-1" style="color:var(--g-700);">
        <i class="ph-fill ph-calendar-check me-2" style="color:var(--g-600);"></i>Edit Job Fair Event
    </h5>
    <p class="mb-0" style="font-size:13px;color:var(--n-500);">Update job fair schedule and venue</p>
</div>

<div class="card border-0 shadow-sm rounded-3" style="max-width:600px;">
    <div class="card-body p-4">
        <form action="{{ route('staff.jobfair.events.update', $event->job_fair_events_id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label fw-semibold small" style="color:var(--g-700);">
                    Event Title <span class="text-danger">*</span>
                </label>
                <input type="text" name="title" class="form-control"
                    style="border:1px solid var(--n-200);border-radius:10px;font-size:13px;"
                    value="{{ old('title', $event->title) }}" required>
                @error('title')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold small" style="color:var(--g-700);">
                    Event Date <span class="text-danger">*</span>
                </label>
                {{-- Locked until the desk says it is moving the fair.

                     A job fair date is not an ordinary field: employers were
                     invited against it, some have already confirmed, and
                     jobseekers were told when to turn up. It moves when the
                     office has a reason to move it — a typhoon, a venue pulling
                     out — so changing it is a deliberate act, not a stray click
                     on a form opened to fix a typo in the title. --}}
                <div class="d-flex align-items-center gap-2">
                    <input type="date" name="event_date" id="eventDateField" class="form-control"
                        style="border:1px solid var(--n-200);border-radius:10px;font-size:13px;background:var(--n-50);"
                        value="{{ old('event_date', $event->event_date->format('Y-m-d')) }}" required readonly>
                    <button type="button" id="rescheduleBtn"
                        class="btn btn-sm fw-semibold flex-shrink-0"
                        style="border:1px solid var(--n-200);color:var(--g-700);background:#fff;
                               border-radius:8px;font-size:12px;padding:8px 14px;white-space:nowrap;">
                        <i class="ph-fill ph-calendar-x me-1"></i> Reschedule
                    </button>
                </div>
                <div id="rescheduleNote" class="mt-1" style="display:none;font-size:11.5px;color:var(--warn);font-weight:600;">
                    <i class="ph-fill ph-warning-circle me-1"></i>
                    Pick the new date. Everyone already invited to this fair keeps their
                    invitation, so tell them the day has moved.
                </div>
                <div id="eventDateTaken" class="mt-1" style="display:none;font-size:11.5px;color:var(--danger);font-weight:600;">
                    <i class="ph-fill ph-x-circle me-1"></i>
                    Another job fair is already scheduled on this date. Choose another day.
                </div>
                @error('event_date')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            {{-- Ang oras gi-validate isip required sa update, mao nga kinahanglan
                 siya ania. Kung wala, walay bisan unsa nga mapadala sa maong
                 field ug dili gyud ma-save ang porma bisan unsa pa ang usbon. --}}
            <div class="mb-3">
                <label class="form-label fw-semibold small" style="color:var(--g-700);">
                    Event Time <span class="text-danger">*</span>
                </label>
                {{-- A list, not a clock — the same one the create form offers.
                     An event saved at an odd time before this existed keeps its
                     own time in the list, so opening the form does not quietly
                     move it. --}}
                @php
                    $currentTime = old('event_time', \Illuminate\Support\Str::substr($event->event_time, 0, 5));
                    $timeSlots   = \App\Support\JobFairEventHours::slots($currentTime ?: null);
                @endphp
                <select name="event_time" class="form-select"
                    style="border:1px solid var(--n-200);border-radius:10px;font-size:13px;" required>
                    <option value="">— Select time —</option>
                    @foreach($timeSlots as $value => $label)
                        <option value="{{ $value }}" {{ $currentTime === $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                <div class="mt-1" style="font-size:11px;color:var(--n-500);">
                    <i class="ph ph-info me-1"></i>
                    The office day runs 8:00 AM to 5:00 PM.
                </div>
                @error('event_time')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold small" style="color:var(--g-700);">
                    Venue <span class="text-danger">*</span>
                </label>
                <input type="text" name="venue" class="form-control"
                    style="border:1px solid var(--n-200);border-radius:10px;font-size:13px;"
                    value="{{ old('venue', $event->venue) }}" required>
                @error('venue')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            {{-- Ang ngalan sa lugar dili igo para sa tawo nga paadtoon didto.
                 Lain nga linya, mao nga lain pud nga field. --}}
            <div class="mb-3">
                <label class="form-label fw-semibold small" style="color:var(--g-700);">
                    Venue Address
                </label>
                <input type="text" name="venue_address" class="form-control"
                    style="border:1px solid var(--n-200);border-radius:10px;font-size:13px;"
                    placeholder="e.g. Masterson Ave, Upper Balulang, Cagayan de Oro City"
                    value="{{ old('venue_address', $event->venue_address) }}">
                @error('venue_address')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            {{-- Ang tipo ug ang target mahimo nang usbon dinhi.
                 Kaniadto sa create form ra sila, mao nga ang event nga Local ra
                 ang na-tsek dili na gyud maabli sa overseas. Ang pagdugang dinhi
                 mo-invite dayon sa mga employer nga karon pa nahaom. --}}
            <div class="mb-3">
                <label class="form-label fw-semibold small" style="color:var(--g-700);">
                    Employers to Invite <span class="text-danger">*</span>
                </label>
                @php
                    // Gikuha na gikan sa `cater` mismo. Kaniadto gibanabana ni
                    // gikan sa kwota — local_capacity > 0 — nga molihok ra kung
                    // kinahanglanon gyud ang numero. Karon nga opsyonal na siya,
                    // ang event nga nangita ug overseas apan wala pay target
                    // mabasa unta nga dili diay siya nangita.
                    $checkedCater = old('cater', (array) $event->cater);
                @endphp
                <div class="p-3 rounded-3" style="background:var(--n-50);">
                    @foreach([
                        ['local',    'caterLocal',    'local_capacity',    'Local Employers',    $event->local_capacity],
                        ['overseas', 'caterOverseas', 'overseas_capacity', 'Overseas Employers', $event->overseas_capacity],
                    ] as [$val, $boxId, $field, $label, $stored])
                    @php $isChecked = in_array($val, $checkedCater); @endphp
                    <div class="d-flex align-items-center gap-3 {{ !$loop->last ? 'mb-2' : '' }}">
                        <div class="form-check mb-0" style="min-width:180px;">
                            <input class="form-check-input cater-box" type="checkbox" name="cater[]" value="{{ $val }}"
                                id="{{ $boxId }}" data-target="{{ $field }}" {{ $isChecked ? 'checked' : '' }}>
                            <label class="form-check-label" for="{{ $boxId }}" style="font-size:13px;color:var(--g-700);font-weight:600;">
                                {{ $label }}
                            </label>
                        </div>
                        <input type="number" name="{{ $field }}" id="{{ $field }}" min="1"
                            class="form-control form-control-sm"
                            style="max-width:130px;border:1px solid var(--n-200);border-radius:8px;font-size:13px;"
                            value="{{ old($field, $stored ?: '') }}"
                            {{ $isChecked ? '' : 'disabled' }}>
                        <span style="font-size:12px;color:var(--n-500);">target <span class="text-danger">*</span></span>
                    </div>
                    @error($field)
                        <div class="text-danger small mb-2">{{ $message }}</div>
                    @enderror
                    @endforeach
                </div>
                <div class="mt-1" style="font-size:11px;color:var(--n-500);">
                    <i class="ph ph-info me-1"></i>
                    Adding a type invites the employers of that type who are now eligible.
                    The target is a note to yourself — nobody is turned away when it is reached.
                </div>
                @error('cater')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            {{-- ── WHAT THE FAIR IS LOOKING FOR ──
                 Widening this invites the employers who now match, the same way
                 adding an employer type does. Narrowing it does not un-invite
                 anybody: an employer who has already been asked, and may have
                 already said yes, is not withdrawn by an edit. --}}
            <div class="mb-3">
                <label class="form-label fw-semibold small" style="color:var(--g-700);">
                    Industries This Fair Is Looking For <span class="text-danger">*</span>
                </label>

                @php
                    $storedIndustries  = (array) $event->target_industries;
                    $allIndustries     = old('target_industries')
                                            ? false
                                            : (old('all_industries', $storedIndustries ? '0' : '1') === '1');
                    $checkedIndustries = (array) old('target_industries', $storedIndustries);
                @endphp

                <div class="p-3 rounded-3" style="background:var(--n-50);">
                    <div class="form-check mb-2 pb-2" style="border-bottom:1px dashed var(--n-200);">
                        <input class="form-check-input" type="checkbox" name="all_industries" value="1"
                            id="allIndustries" {{ $allIndustries ? 'checked' : '' }}>
                        <label class="form-check-label" for="allIndustries"
                            style="font-size:13px;color:var(--g-700);font-weight:600;">
                            All industries
                        </label>
                        <div style="font-size:11px;color:var(--n-500);">
                            Every approved employer of the checked type(s) is invited.
                        </div>
                    </div>

                    <div style="max-height:260px;overflow-y:auto;">
                        @foreach($industryCounts as $group => $counts)
                        <div class="form-check d-flex align-items-start gap-2 mb-1">
                            <input class="form-check-input industry-box" type="checkbox"
                                name="target_industries[]" value="{{ $group }}"
                                id="industry{{ $loop->index }}"
                                {{ in_array($group, $checkedIndustries) ? 'checked' : '' }}
                                {{ $allIndustries ? 'disabled' : '' }}>
                            <label class="form-check-label" for="industry{{ $loop->index }}"
                                style="font-size:12px;color:var(--n-700);">
                                {{ $group }}
                                <span style="color:{{ $counts['local'] + $counts['overseas'] ? 'var(--g-700)' : 'var(--n-400)' }};font-weight:600;">
                                    — {{ $counts['local'] }} local, {{ $counts['overseas'] }} overseas
                                </span>
                            </label>
                        </div>
                        @endforeach
                    </div>
                </div>

                @if($unclassified > 0)
                <div class="mt-2 p-2 rounded-3" style="background:#FFF7ED;font-size:11px;color:var(--warn);">
                    <i class="ph-fill ph-warning-circle me-1"></i>
                    {{ $unclassified }} approved employer(s) have no industry group set, so they are
                    skipped whenever specific industries are chosen. Set it from
                    <a href="{{ route('staff.employers', ['tab' => 'approved']) }}" style="color:var(--warn);font-weight:600;">Employers</a>
                    — open an employer's Establishment Details.
                </div>
                @endif

                @error('target_industries')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            {{-- ── WHO THE FAIR IS FOR ──
                 PESO Job Fair staff, 2026-08-26: a fair may be held for PWD
                 applicants. When it is, only the vacancies whose employer
                 said they accept PWD applicants can be taken into it — the
                 posting has carried that answer since it was written. --}}
            <div class="mb-3">
                <label class="form-label fw-semibold small" style="color:var(--g-700);">
                    Who This Event Is For
                </label>
                <div class="p-3 rounded-3" style="background:var(--n-50);">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="pwd_only" value="1"
                            id="pwdOnly" {{ old('pwd_only', $event->pwd_only) ? 'checked' : '' }}>
                        <label class="form-check-label" for="pwdOnly"
                            style="font-size:13px;color:var(--g-700);font-weight:600;">
                            For PWD applicants only
                        </label>
                        <div style="font-size:11px;color:var(--n-500);">
                            Only vacancies marked <strong>Accepts Disability: Yes</strong> can be
                            accepted into this event. Leave this alone for an ordinary fair.
                        </div>
                    </div>
                </div>
            </div>

            {{-- Walay Status nga pilianan dinhi.

                 Ang kahimtang sa event gikan sa iyang petsa, ug ang
                 UpdateJobFairEventStatuses nga command mao ang nagbutang niini
                 kada adlaw. Ang pagpapili sa desk niini nagtugot nga ang usa ka
                 fair mahimong "Completed" samtang nagpadayon pa siya, ug ang
                 command mo-usab ra pud niini pagkasunod adlaw. --}}

            <div class="d-flex gap-2">
                <button type="submit" id="eventSubmit" class="btn fw-semibold"
                    style="background:var(--g-600);
                           color:#fff;border:none;border-radius:10px;
                           padding:10px 24px;font-size:14px;">
                    <i class="ph-fill ph-check-circle me-2"></i>Update Event
                </button>
                <a href="{{ route('staff.jobfair.events') }}"
                   class="btn fw-semibold"
                   style="border:1px solid var(--n-200);color:var(--g-700);
                          background:#fff;border-radius:10px;
                          padding:10px 24px;font-size:14px;">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Parehas sa create form: ang target alang ra sa tipo nga tinuod nga
    // ── Ang adlaw nga naay lain nga fair na. Ang kaugalingon nga petsa
    // ── niini nga event wala sa listahan, mao nga ang pag-save nga walay
    // ── pag-usab sa petsa dili mababagan. ──
    const takenDates = @json($takenDates ?? []);
    const dateField  = document.getElementById('eventDateField');
    const dateWarn   = document.getElementById('eventDateTaken');
    const submitBtn  = document.getElementById('eventSubmit');

    const rescheduleBtn  = document.getElementById('rescheduleBtn');
    const rescheduleNote = document.getElementById('rescheduleNote');

    // A form that came back with an error keeps the date the desk had already
    // typed, so it must come back open — locking it again would leave a date on
    // screen that no longer matches the event and no way to correct it.
    const alreadyRescheduling = @json(
        old('event_date') !== null && old('event_date') !== $event->event_date->format('Y-m-d')
    );

    function checkTakenDate() {
        const taken = takenDates.includes(dateField.value);
        const open  = !dateField.readOnly;

        dateField.style.border = taken
            ? '1px solid var(--danger)'
            : (open ? '1px solid var(--warn)' : '1px solid var(--n-200)');
        dateField.style.background = taken
            ? 'var(--danger-bg)'
            : (open ? '#fff' : 'var(--n-50)');

        dateWarn.style.display = taken ? 'block' : 'none';
        if (submitBtn) {
            submitBtn.disabled = taken;
            submitBtn.style.opacity = taken ? '0.55' : '';
            submitBtn.style.cursor = taken ? 'not-allowed' : '';
        }
    }

    // Reschedule opens the field and lights it up, so the desk can see which
    // one field it just took responsibility for.
    function openReschedule(focus) {
        dateField.readOnly = false;
        rescheduleNote.style.display = 'block';
        rescheduleBtn.disabled = true;
        rescheduleBtn.style.opacity = '0.55';
        rescheduleBtn.style.cursor = 'not-allowed';
        checkTakenDate();
        if (focus) {
            dateField.focus();
            if (dateField.showPicker) dateField.showPicker();
        }
    }

    rescheduleBtn.addEventListener('click', function () {
        openReschedule(true);
    });

    dateField.addEventListener('change', checkTakenDate);
    dateField.addEventListener('input', checkTakenDate);

    if (alreadyRescheduling) {
        openReschedule(false);
    } else {
        checkTakenDate();
    }

    // gi-invite, mao nga ang input mosunod sa iyang checkbox.
    document.querySelectorAll('.cater-box').forEach(function (box) {
        box.addEventListener('change', function () {
            const input = document.getElementById(box.dataset.target);
            if (!input) return;
            input.disabled = !box.checked;
            if (!box.checked) input.value = '';
            else input.focus();
        });
    });

    // Parehas usab: "All industries" ug ang piho nga listahan usa ra ka tubag
    // nga gihatag sa duha ka paagi, mao nga usa ra kanila ang mabuhi. Ang
    // naka-disable dili ipadala, ug mao kana ang nagpasabot sa server ug "all".
    const allBox   = document.getElementById('allIndustries');
    const oneBoxes = document.querySelectorAll('.industry-box');

    function syncIndustries() {
        oneBoxes.forEach(function (box) {
            box.disabled = allBox.checked;
            if (allBox.checked) box.checked = false;
        });
    }

    allBox.addEventListener('change', syncIndustries);

    oneBoxes.forEach(function (box) {
        box.addEventListener('change', function () {
            if (box.checked) allBox.checked = false;
        });
    });
</script>
@endpush