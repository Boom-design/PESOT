{{--
    "You're employed through PESO" — and the way back to applying.

    PESO CDO client, 2026-09-14: a jobseeker hired through PESO does not apply
    for other vacancies while they hold the job. This card says why the Apply
    button is gone and gives them the one thing to press when the job ends.
    The dashboard shows it in full; the job page shows it where Apply would be.

    Expects: $employment (JobseekerWorkExperience from PesoEmployment::current).
--}}
@php
    $compact = $compact ?? false;
    $since   = \App\Support\PesoEmployment::startMonth($employment);
@endphp

<div class="p-3 mb-3 rounded-3 fade-in" style="background:var(--g-50);border:1px solid var(--g-500);">
    <div class="d-flex gap-3 {{ $compact ? 'flex-column align-items-center text-center' : 'flex-wrap align-items-center justify-content-between' }}">
        <div class="d-flex gap-3 {{ $compact ? 'flex-column align-items-center' : 'align-items-start' }}">
            <i class="ph-fill ph-briefcase" style="font-size:28px;color:var(--g-700);"></i>
            <div>
                <div style="font-size:14px;font-weight:700;color:var(--g-700);">You're employed through PESO</div>
                <div style="font-size:12.5px;color:var(--n-700);">
                    <strong>{{ $employment->position }}</strong> at {{ $employment->company_name }}@if($since) since {{ $since->format('F Y') }}@endif
                </div>
                <div style="font-size:11.5px;color:var(--n-500);margin-top:2px;">
                    Applying is paused while you hold this job. When it ends, let us know and you can apply again.
                </div>
            </div>
        </div>
        <button type="button" class="btn btn-peso btn-sm px-3 flex-shrink-0 {{ $compact ? 'w-100' : '' }}"
                data-bs-toggle="modal" data-bs-target="#lookingForWorkModal">
            <i class="ph ph-magnifying-glass me-1"></i> I'm looking for work again
        </button>
    </div>
</div>

@once
@push('scripts')
<div class="modal fade" id="lookingForWorkModal" tabindex="-1" aria-labelledby="lookingForWorkTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-3">
            <form method="POST" action="{{ route('jobseeker.employment.end') }}">
                @csrf
                <div class="modal-header border-0" style="background:var(--g-600);">
                    <h6 class="modal-title fw-bold text-white" id="lookingForWorkTitle">
                        <i class="ph ph-magnifying-glass me-2"></i>Looking for work again
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p style="font-size:13px;color:var(--n-700);">
                        This closes <strong>{{ $employment->position }}</strong> at {{ $employment->company_name }}
                        on your NSRP work experience, and you can apply for jobs again.
                    </p>

                    <label class="peso-label" for="endReason">Why did the job end?</label>
                    <select name="reason" id="endReason" class="form-select peso-input mb-3" required
                            onchange="document.getElementById('endReasonOtherWrap').hidden = this.value !== 'others'">
                        <option value="">Select</option>
                        @foreach(\App\Support\PesoEmployment::END_REASONS as $value => $label)
                        <option value="{{ $value }}" {{ old('reason') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>

                    <div id="endReasonOtherWrap" class="mb-3" {{ old('reason') === 'others' ? '' : 'hidden' }}>
                        <label class="peso-label" for="endReasonOther">Please specify</label>
                        <input type="text" name="unemployed_other" id="endReasonOther" class="form-control peso-input"
                               maxlength="255" value="{{ old('unemployed_other') }}">
                    </div>

                    <label class="peso-label" for="endMonth">Month of your last day</label>
                    <input type="month" name="last_month" id="endMonth" class="form-control peso-input" required
                           value="{{ old('last_month', now()->format('Y-m')) }}"
                           @if($since) min="{{ $since->format('Y-m') }}" @endif
                           max="{{ now()->format('Y-m') }}">
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-sm" data-bs-dismiss="modal"
                            style="border:1px solid var(--n-200);color:var(--g-700);background:#fff;border-radius:8px;">Cancel</button>
                    <button type="submit" class="btn btn-peso btn-sm px-3">Confirm</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endpush
@endonce
