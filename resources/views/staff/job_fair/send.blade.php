@extends('staff.layouts.app')

@section('content')

<div class="mb-4">
    <h5 class="fw-bold mb-1" style="color:#2d7a5f;">
        <i class="bi bi-megaphone-fill me-2" style="color:#4dd9c0;"></i>Send Job Fair Notification
    </h5>
    <p class="mb-0" style="font-size:13px;color:#888;">
        Notify all active jobseekers and employers about upcoming job fair events.
    </p>
</div>

<div class="row g-4">

    {{-- ── SEND FORM ── --}}
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white border-0 pt-3 pb-0 px-4">
                <h6 class="fw-bold mb-0" style="color:#2d7a5f;">
                    <i class="bi bi-send-fill me-2" style="color:#4dd9c0;"></i>Compose Notification
                </h6>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('staff.jobfair.send.post') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label fw-semibold small" style="color:#2d7a5f;">
                            Notification Title <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="title" class="form-control"
                            style="border:1.5px solid #a8e6cf;border-radius:10px;font-size:13px;"
                            placeholder="e.g. Job Fair 2026 — SM CDO"
                            value="{{ old('title') }}" required>
                        @error('title')
                            <div style="color:#c62828;font-size:12px;margin-top:4px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small" style="color:#2d7a5f;">
                            Message <span class="text-danger">*</span>
                        </label>
                        <textarea name="message" class="form-control" rows="5"
                            style="border:1.5px solid #a8e6cf;border-radius:10px;
                                   font-size:13px;resize:none;"
                            placeholder="e.g. You are invited to attend the Job Fair on May 30, 2026 at SM City CDO. Bring your resume and valid ID."
                            required>{{ old('message') }}</textarea>
                        @error('message')
                            <div style="color:#c62828;font-size:12px;margin-top:4px;">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Recipients Info --}}
                    <div class="mb-4 p-3 rounded-3"
                        style="background:#f0f9f6;border:1.5px solid #a8e6cf;">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-people-fill" style="color:#4dd9c0;font-size:18px;"></i>
                            <div>
                                <div class="fw-semibold" style="font-size:13px;color:#2d7a5f;">
                                    Recipients
                                </div>
                                <div style="font-size:12px;color:#888;">
                                    All active <strong>Jobseekers</strong> + <strong>Employers</strong>
                                    will receive this notification.
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn w-100 fw-semibold"
                        style="background:linear-gradient(90deg,#90d870,#4dd9c0);
                               color:#fff;border:none;border-radius:10px;
                               padding:11px;font-size:14px;">
                        <i class="bi bi-send-fill me-2"></i>Send Notification
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- ── RIGHT: TIPS ── --}}
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white border-0 pt-3 pb-0 px-4">
                <h6 class="fw-bold mb-0" style="color:#2d7a5f;">
                    <i class="bi bi-lightbulb-fill me-2" style="color:#4dd9c0;"></i>Tips
                </h6>
            </div>
            <div class="card-body p-4">
                <ul class="list-unstyled mb-0" style="font-size:13px;color:#555;">
                    <li class="mb-3 d-flex gap-2">
                        <i class="bi bi-check-circle-fill mt-1" style="color:#4dd9c0;flex-shrink:0;"></i>
                        <span>Include the <strong>date, time, and venue</strong> of the job fair.</span>
                    </li>
                    <li class="mb-3 d-flex gap-2">
                        <i class="bi bi-check-circle-fill mt-1" style="color:#4dd9c0;flex-shrink:0;"></i>
                        <span>Mention what to <strong>bring</strong> (resume, valid ID, certificates).</span>
                    </li>
                    <li class="mb-3 d-flex gap-2">
                        <i class="bi bi-check-circle-fill mt-1" style="color:#4dd9c0;flex-shrink:0;"></i>
                        <span>Keep the title <strong>short and clear</strong>.</span>
                    </li>
                    <li class="d-flex gap-2">
                        <i class="bi bi-exclamation-circle-fill mt-1" style="color:#e6a817;flex-shrink:0;"></i>
                        <span>This will be sent to <strong>all active users</strong> — double check before sending!</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

</div>

@endsection