{{-- The two halves of one job: encode a walk-in who has no account, and look
     through the people already registered. The active tab is read from the
     route, so a page only has to include this. --}}
@php $onWalkin = request()->routeIs('staff.nsrp*'); @endphp
<div class="d-flex gap-2 mb-4" style="overflow-x:auto;flex-wrap:nowrap;-webkit-overflow-scrolling:touch;padding-bottom:4px;">
    <a href="{{ route('staff.nsrp') }}"
       class="btn btn-sm fw-semibold"
       style="{{ $onWalkin
                    ? 'background:var(--g-600);color:#fff;border:none;'
                    : 'border:1px solid var(--n-200);color:var(--g-700);background:#fff;' }}border-radius:8px;font-size:12px;padding:5px 16px;white-space:nowrap;flex-shrink:0;">
        <i class="ph-fill ph-file-text me-1"></i> Walk-in NSRP
    </a>
    <a href="{{ route('staff.registrations') }}"
       class="btn btn-sm fw-semibold"
       style="{{ $onWalkin
                    ? 'border:1px solid var(--n-200);color:var(--g-700);background:#fff;'
                    : 'background:var(--g-600);color:#fff;border:none;' }}border-radius:8px;font-size:12px;padding:5px 16px;white-space:nowrap;flex-shrink:0;">
        <i class="ph-fill ph-user-list me-1"></i> Registrations
    </a>
</div>
