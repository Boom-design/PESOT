{{--
    Ang pansamantalang password, gipakita KAUSA ra.

    Naa siya sa flash session, mao nga mawala pag-refresh — kana ang tumong.
    Wala kini gitipigan nga plaintext ug wala gi-email; kung mawala sa dili pa
    kini mabasa sa telepono, buhat ug bag-o.
--}}
@if(session('recovery_temp_password'))
<div class="alert d-flex align-items-start gap-3 rounded-3 border-0 mb-3"
     style="background:#fff5e0;color:#6b4500;border:1px solid #e0b64d !important;">
    <i class="ph-fill ph-key" style="font-size:22px;flex-shrink:0;margin-top:2px;"></i>
    <div class="flex-grow-1">
        <div class="fw-bold" style="font-size:13.5px;">
            Temporary password for {{ session('recovery_contact') }}
        </div>
        <div style="font-family:ui-monospace, Menlo, Consolas, monospace;
                    font-size:22px;font-weight:800;letter-spacing:2px;
                    background:#fff;border:1px dashed #c99a2e;border-radius:8px;
                    padding:10px 14px;margin:8px 0;display:inline-block;
                    user-select:all;">{{ session('recovery_temp_password') }}</div>
        <div style="font-size:11.5px;line-height:1.6;">
            Read this to them now over the phone. <strong>It will not be shown again</strong> —
            leaving or refreshing this page loses it, and you would have to issue a new one.
            They will be forced to change it the moment they log in, so nobody here keeps it.
        </div>
    </div>
</div>
@endif
