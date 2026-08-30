{{--
    Password requirement hint. Shown next to every field that sets a password so
    the rule is visible before submitting rather than only after a rejection.

    Pass $onDark = true when the field sits on a green/photo background (the
    public auth pages) so the text stays readable.
--}}
@php
    $onDark = $onDark ?? false;
@endphp
<div style="font-size:11px;line-height:1.5;margin-top:5px;color:{{ $onDark ? 'rgba(255,255,255,0.78)' : 'var(--n-500)' }};">
    <i class="ph ph-info me-1"></i>{{ \App\Rules\PasswordPolicy::hint() }}
</div>
