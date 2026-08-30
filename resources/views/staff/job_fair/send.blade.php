@extends('staff.layouts.app')

@section('content')

@php
    use App\Support\JobFairAudience;

    $selectedId = $selectedEvent->job_fair_events_id ?? null;
    $oldAudience = old('audience', JobFairAudience::EMPLOYERS_ALL);
@endphp

<div class="mb-4">
    <h5 class="fw-bold mb-1" style="color:var(--g-700);">
        <i class="ph-fill ph-megaphone me-2" style="color:var(--g-600);"></i>Send Job Fair Notification
    </h5>
    <p class="mb-0" style="font-size:13px;color:var(--n-500);">
        Notify employers and jobseekers about an upcoming job fair, in the system and by text message.
    </p>
</div>

@if($events->isEmpty())

    <div class="peso-card">
        <div class="card-body text-center py-5">
            <i class="ph ph-calendar-x" style="font-size:44px;color:var(--n-300);"></i>
            <div class="fw-semibold mt-3" style="color:var(--g-700);">No upcoming job fair events</div>
            <p class="mb-3" style="font-size:13px;color:var(--n-500);">
                Notifications are always tied to an event. Create one first.
            </p>
            <a href="{{ route('staff.jobfair.events.create') }}" class="btn btn-peso">
                <i class="ph ph-plus me-1"></i> Create Job Fair Event
            </a>
        </div>
    </div>

@else

<div class="row g-4">

    {{-- ── COMPOSE ── --}}
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white border-0 pt-3 pb-0 px-4">
                <h6 class="fw-bold mb-0" style="color:var(--g-700);">
                    <i class="ph-fill ph-paper-plane-tilt me-2" style="color:var(--g-600);"></i>Compose Notification
                </h6>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('staff.jobfair.send.preview') }}" id="blastForm">
                    @csrf

                    {{-- Event --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold small" style="color:var(--g-700);">
                            Job Fair Event <span class="text-danger">*</span>
                        </label>
                        <select name="event_id" id="eventSelect" class="form-select"
                            style="border:1px solid var(--n-200);border-radius:10px;font-size:13px;" required>
                            @foreach($events as $event)
                                <option value="{{ $event->job_fair_events_id }}"
                                    data-title="{{ $event->title }}"
                                    data-date="{{ $event->event_date?->format('M d, Y') }}"
                                    data-time="{{ $event->event_time ? \Carbon\Carbon::parse($event->event_time)->format('g:i A') : '' }}"
                                    data-venue="{{ $event->venue }}"
                                    {{ $selectedId == $event->job_fair_events_id ? 'selected' : '' }}>
                                    {{ $event->title }} — {{ $event->event_date?->format('M d, Y') }}
                                </option>
                            @endforeach
                        </select>
                        <div style="font-size:11px;color:var(--n-500);margin-top:5px;">
                            Changing the event reloads the recipient counts below.
                        </div>
                    </div>

                    {{-- Audience --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold small" style="color:var(--g-700);">
                            Recipients <span class="text-danger">*</span>
                        </label>

                        @foreach($audiences as $key => $audience)
                            <label class="d-block mb-2 p-3 rounded-3"
                                style="border:1px solid {{ $audience['unlocked'] ? 'var(--n-200)' : 'var(--n-100)' }};
                                       background:{{ $audience['unlocked'] ? 'var(--n-0)' : 'var(--n-50)' }};
                                       cursor:{{ $audience['unlocked'] ? 'pointer' : 'not-allowed' }};">
                                <div class="d-flex align-items-start gap-2">
                                    <input class="form-check-input mt-1 flex-shrink-0" type="radio" name="audience"
                                        value="{{ $key }}"
                                        {{ $oldAudience === $key && $audience['unlocked'] ? 'checked' : '' }}
                                        {{ $audience['unlocked'] ? '' : 'disabled' }} required>
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold" style="font-size:13px;color:var(--g-700);">
                                            {{ $audience['label'] }}
                                            @unless($audience['unlocked'])
                                                <i class="ph-fill ph-lock-simple ms-1" style="color:var(--n-400);"></i>
                                            @endunless
                                        </div>
                                        <div style="font-size:12px;color:var(--n-500);">{{ $audience['hint'] }}</div>
                                        <div class="mt-1" style="font-size:12px;color:var(--n-700);">
                                            <strong>{{ $audience['total'] }}</strong> recipient(s) ·
                                            <strong>{{ $audience['reachable'] }}</strong> with a valid mobile number
                                        </div>
                                        @unless($audience['unlocked'])
                                            <div class="mt-1" style="font-size:12px;color:var(--warn);">
                                                {{ $confirmedCount }} of {{ $threshold }} employers confirmed —
                                                jobseekers cannot be notified yet.
                                            </div>
                                        @endunless
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>

                    {{-- In-app notification --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold small" style="color:var(--g-700);">
                            Notification Title <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="title" id="blastTitle" class="form-control"
                            style="border:1px solid var(--n-200);border-radius:10px;font-size:13px;"
                            placeholder="e.g. Job Fair 2026 — SM CDO"
                            value="{{ old('title') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small" style="color:var(--g-700);">
                            In-app Message <span class="text-danger">*</span>
                        </label>
                        <textarea name="message" id="blastMessage" class="form-control" rows="4"
                            style="border:1px solid var(--n-200);border-radius:10px;font-size:13px;resize:none;"
                            required>{{ old('message') }}</textarea>
                    </div>

                    {{-- SMS --}}
                    <div class="mb-3 p-3 rounded-3" style="border:1px solid var(--n-200);background:var(--n-25);">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="send_sms" value="1" id="sendSms"
                                {{ old('send_sms') ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="sendSms"
                                style="font-size:13px;color:var(--g-700);">
                                Also send as a text message (SMS)
                            </label>
                        </div>

                        @unless($smsLive)
                            <div class="mb-2 p-2 rounded-3"
                                style="background:var(--warn-bg);border:1px solid var(--warn-br);
                                       font-size:12px;color:var(--warn);">
                                <i class="ph-fill ph-flask me-1"></i>
                                Test mode — the SMS gateway is switched off, so nothing is actually sent
                                and nothing is charged. Everything else works normally.
                            </div>
                        @endunless

                        <div id="smsBlock" style="display:none;">
                            <label class="form-label fw-semibold small" style="color:var(--g-700);">
                                Text Message
                            </label>
                            <textarea name="sms_message" id="smsMessage" class="form-control" rows="3"
                                maxlength="459"
                                style="border:1px solid var(--n-200);border-radius:10px;font-size:13px;resize:none;"
                                placeholder="Keep it short — every 160 characters is charged as a separate message.">{{ old('sms_message') }}</textarea>
                            <div class="d-flex justify-content-between mt-1" style="font-size:11px;color:var(--n-500);">
                                <span id="smsCounter">0 characters · 0 message part(s)</span>
                                <span>Recipients cannot reply to this number.</span>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn w-100 fw-semibold"
                        style="background:var(--g-600);color:#fff;border:none;border-radius:10px;
                               padding:11px;font-size:14px;">
                        <i class="ph-fill ph-eye me-2"></i>Review before sending
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- ── RIGHT: HOW THIS WORKS ── --}}
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white border-0 pt-3 pb-0 px-4">
                <h6 class="fw-bold mb-0" style="color:var(--g-700);">
                    <i class="ph-fill ph-steps me-2" style="color:var(--g-600);"></i>How job fair notices work
                </h6>
            </div>
            <div class="card-body p-4">
                <ol class="mb-3 ps-3" style="font-size:13px;color:var(--n-700);">
                    <li class="mb-2">Employers are invited when the event is created.</li>
                    <li class="mb-2">
                        Once <strong>{{ $threshold }}</strong> employers confirm, jobseekers can be notified.
                        @if($selectedEvent)
                            <div class="mt-1 fw-semibold"
                                style="font-size:12px;color:{{ $gateMet ? 'var(--g-700)' : 'var(--warn)' }};">
                                {{ $confirmedCount }} of {{ $threshold }} confirmed
                                @if($gateMet) — jobseekers can be notified. @else — not yet. @endif
                            </div>
                        @endif
                    </li>
                    <li>Every send is reviewed on a confirmation screen first.</li>
                </ol>

                <ul class="list-unstyled mb-0" style="font-size:13px;color:var(--n-700);">
                    <li class="mb-3 d-flex gap-2">
                        <i class="ph-fill ph-phone mt-1" style="color:var(--g-600);flex-shrink:0;"></i>
                        <span>Text messages go to the mobile number on each person's account. Numbers are never typed in here.</span>
                    </li>
                    <li class="mb-3 d-flex gap-2">
                        <i class="ph-fill ph-coins mt-1" style="color:var(--g-600);flex-shrink:0;"></i>
                        <span>Every 160 characters counts as one paid message per recipient.</span>
                    </li>
                    <li class="d-flex gap-2">
                        <i class="ph-fill ph-prohibit mt-1" style="color:var(--n-400);flex-shrink:0;"></i>
                        <span>Anyone who turned off SMS in their profile is skipped, and shown on the review screen.</span>
                    </li>
                </ul>
            </div>
        </div>

        @if($selectedEvent)
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-4">
                    <div class="fw-semibold mb-1" style="font-size:13px;color:var(--g-700);">
                        {{ $selectedEvent->title }}
                    </div>
                    <div style="font-size:12px;color:var(--n-500);">
                        <i class="ph ph-calendar-dots me-1"></i>{{ $selectedEvent->event_date?->format('M d, Y') ?? 'None' }}
                        @if($selectedEvent->event_time)
                            · {{ \Carbon\Carbon::parse($selectedEvent->event_time)->format('g:i A') }}
                        @endif
                        <br>
                        <i class="ph ph-map-pin me-1"></i>{{ $selectedEvent->venue ?: 'None' }}
                    </div>
                </div>
            </div>
        @endif
    </div>

</div>

@endif

@endsection

@push('scripts')
<script>
(function () {
    const eventSelect = document.getElementById('eventSelect');
    const sendSms     = document.getElementById('sendSms');
    const smsBlock    = document.getElementById('smsBlock');
    const smsMessage  = document.getElementById('smsMessage');
    const counter     = document.getElementById('smsCounter');
    const titleInput  = document.getElementById('blastTitle');
    const msgInput    = document.getElementById('blastMessage');

    if (!eventSelect) return;

    // Counts differ per event, so the page reloads rather than guessing.
    eventSelect.addEventListener('change', function () {
        window.location = '{{ route('staff.jobfair.send') }}?event_id=' + this.value;
    });

    function selected() {
        return eventSelect.options[eventSelect.selectedIndex];
    }

    function fillTemplates() {
        const opt   = selected();
        const title = opt.dataset.title || '';
        const date  = opt.dataset.date || '';
        const time  = opt.dataset.time || '';
        const venue = opt.dataset.venue || '';
        const when  = date + (time ? ' at ' + time : '');

        if (!titleInput.value) {
            titleInput.value = title;
        }
        if (!msgInput.value) {
            msgInput.value = 'You are invited to ' + title + ' on ' + when +
                (venue ? ', held at ' + venue : '') + '. Please bring your resume and a valid ID.';
        }
        if (smsMessage && !smsMessage.value) {
            smsMessage.value = 'PESO CDO: ' + title + ' on ' + when +
                (venue ? ' at ' + venue : '') + '. Bring your resume and valid ID. Do not reply.';
            updateCounter();
        }
    }

    // Same rule the server uses: any character outside plain ASCII forces the
    // whole message into unicode, which cuts 160 characters down to 70.
    function isUnicode(text) {
        return /[^\x20-\x7E\r\n]/.test(text);
    }

    function segments(text) {
        if (!text.length) return 0;
        const unicode = isUnicode(text);
        const single  = unicode ? 70 : 160;
        const concat  = unicode ? 67 : 153;
        return text.length <= single ? 1 : Math.ceil(text.length / concat);
    }

    function updateCounter() {
        if (!smsMessage || !counter) return;
        const text = smsMessage.value;
        const parts = segments(text);
        counter.textContent = text.length + ' characters · ' + parts + ' message part(s)'
            + (isUnicode(text) ? ' · emoji/accents shorten each part to 70 characters' : '');
        counter.style.color = parts > 2 ? 'var(--warn)' : 'var(--n-500)';
    }

    function toggleSms() {
        if (!sendSms || !smsBlock) return;
        smsBlock.style.display = sendSms.checked ? 'block' : 'none';
        if (sendSms.checked) fillTemplates();
    }

    if (sendSms) sendSms.addEventListener('change', toggleSms);
    if (smsMessage) smsMessage.addEventListener('input', updateCounter);

    fillTemplates();
    toggleSms();
    updateCounter();
})();
</script>
@endpush
