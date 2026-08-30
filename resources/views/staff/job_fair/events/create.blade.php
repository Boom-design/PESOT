@extends('staff.layouts.app')

@section('page-title', 'Create Job Fair Event')

@section('content')

{{-- Header and card share one centred column so the gap is even on both sides
     instead of the form hugging the left edge. --}}
<div style="max-width:640px;margin:0 auto;">

    <div class="mb-4 text-center">
        <h5 class="fw-bold mb-1" style="color:var(--g-700);">
            <i class="ph-fill ph-calendar-plus me-2" style="color:var(--g-600);"></i>Create Job Fair Event
        </h5>
        <p class="mb-0" style="font-size:13px;color:var(--n-500);">Set a new job fair schedule and venue</p>
    </div>

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-4">
            <form action="{{ route('staff.jobfair.events.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label fw-semibold small" style="color:var(--g-700);">
                        Event Title <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="title" class="form-control"
                        style="border:1px solid var(--n-200);border-radius:10px;font-size:13px;"
                        placeholder="e.g. PESO CDO Job Fair 2026"
                        value="{{ old('title') }}" required>
                    @error('title')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                @php
                    $leadDays = config('peso.jobfair.create_lead_days');
                    $earliest = now()->addDays($leadDays);
                @endphp
                <div class="mb-3">
                    <label class="form-label fw-semibold small" style="color:var(--g-700);">
                        Event Date <span class="text-danger">*</span>
                    </label>
                    <input type="date" name="event_date" class="form-control"
                        style="border:1px solid var(--n-200);border-radius:10px;font-size:13px;"
                        value="{{ old('event_date') }}"
                        min="{{ $earliest->format('Y-m-d') }}" required>
                    <div class="mt-1" style="font-size:11px;color:var(--n-500);">
                        <i class="ph ph-info me-1"></i>
                        Event date must be at least {{ $leadDays }} days from today
                        (earliest: {{ $earliest->format('M d, Y') }}). Invitations go out
                        the moment the event is created, so the employers have that long
                        to decide and prepare.
                    </div>
                    @error('event_date')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold small" style="color:var(--g-700);">
                        Event Time <span class="text-danger">*</span>
                    </label>
                    <input type="time" name="event_time" class="form-control"
                        style="border:1px solid var(--n-200);border-radius:10px;font-size:13px;"
                        value="{{ old('event_time') }}" min="09:00" max="17:00" required>
                    <div class="mt-1" style="font-size:11px;color:var(--n-500);">
                        <i class="ph ph-info me-1"></i>
                        Must be between 9:00 AM and 5:00 PM.
                    </div>
                    @error('event_time')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold small" style="color:var(--g-700);">
                        Employers to Invite <span class="text-danger">*</span>
                    </label>
                    @php
                        $checkedCater = old('cater', ['local', 'overseas']);
                    @endphp
                    <div class="p-3 rounded-3" style="background:var(--n-50);">
                        @foreach([
                            ['local',    'caterLocal',    'local_capacity',    'Local Employers',    'e.g. 20'],
                            ['overseas', 'caterOverseas', 'overseas_capacity', 'Overseas Employers', 'e.g. 10'],
                        ] as [$val, $boxId, $field, $label, $ph])
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
                                placeholder="{{ $ph }}" value="{{ old($field) }}"
                                {{ $isChecked ? '' : 'disabled' }}>
                            <span style="font-size:12px;color:var(--n-500);">target (optional)</span>
                        </div>
                        @error($field)
                            <div class="text-danger small mb-2">{{ $message }}</div>
                        @enderror
                        @endforeach
                    </div>
                    <div class="mt-1" style="font-size:11px;color:var(--n-500);">
                        <i class="ph ph-info me-1"></i>
                        Check at least one — only checked type(s) receive the invitation.
                        The target is a note to yourself: how many the fair is closed at is
                        the sponsor's call, so nobody is turned away when it is reached.
                    </div>
                    @error('cater')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- ── WHAT THE FAIR IS LOOKING FOR ──
                     PESO Job Fair staff, 2026-08-23: the invitation goes to the
                     employers the fair is actually after, not to everyone who
                     happens to be approved. The count beside each industry is
                     how many would be invited today, so the staff is not
                     guessing before they save. --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold small" style="color:var(--g-700);">
                        Industries This Event Is Looking For <span class="text-danger">*</span>
                    </label>

                    @php
                        $allIndustries    = old('all_industries', '1') && !old('target_industries');
                        $checkedIndustries = (array) old('target_industries', []);
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

                        <div id="industryList" style="max-height:260px;overflow-y:auto;">
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
                                id="pwdOnly" {{ old('pwd_only') ? 'checked' : '' }}>
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

                <div class="mb-3">
                    <label class="form-label fw-semibold small" style="color:var(--g-700);">
                        Venue <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="venue" class="form-control"
                        style="border:1px solid var(--n-200);border-radius:10px;font-size:13px;"
                        placeholder="e.g. SM City CDO Event Center"
                        value="{{ old('venue') }}" required>
                    @error('venue')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex gap-2 justify-content-center">
                    <button type="submit" class="btn fw-semibold"
                        style="background:var(--g-600);
                               color:#fff;border:none;border-radius:10px;
                               padding:10px 24px;font-size:14px;">
                        <i class="ph-fill ph-calendar-check me-2"></i>Create Event
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

</div>

@endsection

@push('scripts')
<script>
    // The target only applies to a type that is actually being invited, so
    // the input follows its checkbox rather than sitting there enabled.
    document.querySelectorAll('.cater-box').forEach(function (box) {
        box.addEventListener('change', function () {
            const input = document.getElementById(box.dataset.target);
            if (!input) return;
            input.disabled = !box.checked;
            if (!box.checked) input.value = '';
            else input.focus();
        });
    });

    // "All industries" and a specific list are the same answer given two ways,
    // so only one of them can be active. Disabled boxes are not submitted,
    // which is what makes the server read this as "all".
    const allBox   = document.getElementById('allIndustries');
    const oneBoxes = document.querySelectorAll('.industry-box');

    function syncIndustries() {
        oneBoxes.forEach(function (box) {
            box.disabled = allBox.checked;
            if (allBox.checked) box.checked = false;
        });
    }

    allBox.addEventListener('change', syncIndustries);

    // Ticking a specific industry means the fair is not after all of them.
    oneBoxes.forEach(function (box) {
        box.addEventListener('change', function () {
            if (box.checked) allBox.checked = false;
        });
    });
</script>
@endpush
