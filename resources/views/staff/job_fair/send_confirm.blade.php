@extends('staff.layouts.app')

@section('content')

@php
    use App\Support\PhoneNumber;

    $sendable  = $breakdown['sendable'];
    $noNumber  = $breakdown['skipped_no_number'];
    $invalid   = $breakdown['skipped_invalid'];
    $optedOut  = $breakdown['skipped_opted_out'];
    $skipped   = count($noNumber) + count($invalid) + count($optedOut);
    $totalSms  = count($sendable) * $segments;
@endphp

<div class="mb-4">
    <h5 class="fw-bold mb-1" style="color:var(--g-700);">
        <i class="ph-fill ph-seal-check me-2" style="color:var(--g-600);"></i>Review before sending
    </h5>
    <p class="mb-0" style="font-size:13px;color:var(--n-500);">
        Nothing has been sent yet. Check the recipients and the message, then confirm.
    </p>
</div>

<div class="row g-4">

    <div class="col-lg-7">

        {{-- ── WHAT WILL BE SENT ── --}}
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white border-0 pt-3 pb-0 px-4">
                <h6 class="fw-bold mb-0" style="color:var(--g-700);">
                    <i class="ph-fill ph-note me-2" style="color:var(--g-600);"></i>Message
                </h6>
            </div>
            <div class="card-body p-4">
                <div class="mb-3">
                    <div style="font-size:11px;font-weight:700;letter-spacing:.04em;text-transform:uppercase;color:var(--n-500);">Event</div>
                    <div style="font-size:13px;color:var(--n-900);">
                        {{ $event->title }} — {{ $event->event_date?->format('M d, Y') ?? 'None' }}
                        @if($event->venue) · {{ $event->venue }} @endif
                    </div>
                </div>

                <div class="mb-3">
                    <div style="font-size:11px;font-weight:700;letter-spacing:.04em;text-transform:uppercase;color:var(--n-500);">In-app notification</div>
                    <div class="fw-semibold" style="font-size:13px;color:var(--n-900);">{{ $payload['title'] }}</div>
                    <div style="font-size:13px;color:var(--n-700);white-space:pre-wrap;">{{ $payload['message'] }}</div>
                </div>

                @if($sendSms)
                    <div>
                        <div style="font-size:11px;font-weight:700;letter-spacing:.04em;text-transform:uppercase;color:var(--n-500);">
                            Text message — exactly as it will arrive
                        </div>
                        <div class="mt-1 p-3 rounded-3"
                            style="background:var(--g-50);border:1px solid var(--g-200);
                                   font-size:13px;color:var(--n-900);white-space:pre-wrap;">{{ $smsText }}</div>
                        <div class="mt-1" style="font-size:12px;color:var(--n-500);">
                            {{ mb_strlen($smsText) }} characters · {{ $segments }} message part(s) per recipient ·
                            sender shown as <strong>{{ config('services.philsms.sender_id') }}</strong>
                        </div>
                    </div>
                @else
                    <div class="p-2 rounded-3"
                        style="background:var(--n-50);border:1px solid var(--n-200);font-size:12px;color:var(--n-500);">
                        <i class="ph ph-bell me-1"></i>In-app notification only — no text message will be sent.
                    </div>
                @endif
            </div>
        </div>

        {{-- ── WHO IS SKIPPED ── --}}
        @if($sendSms && $skipped > 0)
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white border-0 pt-3 pb-0 px-4">
                    <h6 class="fw-bold mb-0" style="color:var(--warn);">
                        <i class="ph-fill ph-warning me-2"></i>{{ $skipped }} recipient(s) will not get a text
                    </h6>
                </div>
                <div class="card-body p-4">
                    <p style="font-size:12px;color:var(--n-500);">
                        They still receive the in-app notification.
                    </p>

                    @foreach([
                        ['rows' => $noNumber, 'reason' => 'No mobile number on their account'],
                        ['rows' => $invalid,  'reason' => 'The saved number is not a valid mobile number'],
                        ['rows' => $optedOut, 'reason' => 'Turned off SMS in their profile'],
                    ] as $group)
                        @if(count($group['rows']) > 0)
                            <div class="mb-3">
                                <div class="fw-semibold mb-1" style="font-size:12px;color:var(--n-700);">
                                    {{ $group['reason'] }} ({{ count($group['rows']) }})
                                </div>
                                <ul class="mb-0 ps-3" style="font-size:12px;color:var(--n-500);">
                                    @foreach(array_slice($group['rows'], 0, 10) as $row)
                                        <li>
                                            {{ $row['name'] }}
                                            @if($row['raw_number'])
                                                — <span style="font-family:monospace;">{{ $row['raw_number'] }}</span>
                                            @endif
                                        </li>
                                    @endforeach
                                    @if(count($group['rows']) > 10)
                                        <li>and {{ count($group['rows']) - 10 }} more</li>
                                    @endif
                                </ul>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ── CONFIRM ── --}}
        <form method="POST" action="{{ route('staff.jobfair.send.post') }}" id="confirmForm">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <div class="d-flex gap-2">
                <a href="{{ route('staff.jobfair.send', ['event_id' => $event->job_fair_events_id]) }}"
                    class="btn fw-semibold"
                    style="background:var(--n-0);color:var(--n-700);border:1px solid var(--n-200);
                           border-radius:10px;padding:11px 20px;font-size:14px;">
                    <i class="ph ph-arrow-left me-1"></i>Go back and edit
                </a>
                <button type="submit" id="confirmBtn" class="btn flex-grow-1 fw-semibold"
                    style="background:var(--g-600);color:#fff;border:none;border-radius:10px;
                           padding:11px;font-size:14px;">
                    <i class="ph-fill ph-paper-plane-tilt me-2"></i>
                    Confirm and send to {{ $breakdown['total'] }} recipient(s)
                </button>
            </div>
        </form>

    </div>

    {{-- ── RIGHT: THE NUMBERS ── --}}
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white border-0 pt-3 pb-0 px-4">
                <h6 class="fw-bold mb-0" style="color:var(--g-700);">
                    <i class="ph-fill ph-users-three me-2" style="color:var(--g-600);"></i>Recipients
                </h6>
            </div>
            <div class="card-body p-4">
                <div class="mb-3" style="font-size:13px;color:var(--n-700);">
                    {{ $audienceLabel }}
                </div>

                <div class="d-flex justify-content-between py-2"
                    style="border-bottom:1px solid var(--n-100);font-size:13px;">
                    <span style="color:var(--n-500);">In-app notification</span>
                    <strong style="color:var(--n-900);">{{ $breakdown['total'] }}</strong>
                </div>

                @if($sendSms)
                    <div class="d-flex justify-content-between py-2"
                        style="border-bottom:1px solid var(--n-100);font-size:13px;">
                        <span style="color:var(--n-500);">Will receive a text</span>
                        <strong style="color:var(--g-700);">{{ count($sendable) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between py-2"
                        style="border-bottom:1px solid var(--n-100);font-size:13px;">
                        <span style="color:var(--n-500);">Skipped</span>
                        <strong style="color:{{ $skipped ? 'var(--warn)' : 'var(--n-900)' }};">{{ $skipped }}</strong>
                    </div>
                    <div class="d-flex justify-content-between py-2"
                        style="border-bottom:1px solid var(--n-100);font-size:13px;">
                        <span style="color:var(--n-500);">Message parts each</span>
                        <strong style="color:var(--n-900);">{{ $segments }}</strong>
                    </div>
                    <div class="d-flex justify-content-between pt-3 mt-1"
                        style="border-top:2px solid var(--g-200);font-size:14px;">
                        <span class="fw-semibold" style="color:var(--g-700);">Total text messages</span>
                        <strong style="color:var(--g-700);font-size:18px;">{{ $totalSms }}</strong>
                    </div>

                    @unless($smsLive)
                        <div class="mt-3 p-2 rounded-3"
                            style="background:var(--warn-bg);border:1px solid var(--warn-br);
                                   font-size:12px;color:var(--warn);">
                            <i class="ph-fill ph-flask me-1"></i>
                            Test mode — confirming will record everything as if it were sent,
                            but no text leaves the system and nothing is charged.
                        </div>
                    @endunless
                @endif
            </div>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
// One press only. The server also refuses a replayed confirmation, but stopping
// the second click keeps staff from wondering whether it went twice.
(function () {
    const form = document.getElementById('confirmForm');
    const btn  = document.getElementById('confirmBtn');
    if (!form || !btn) return;

    form.addEventListener('submit', function () {
        btn.disabled = true;
        btn.innerHTML = '<i class="ph ph-spinner me-2"></i>Sending…';
    });
})();
</script>
@endpush
