{{--
    The Change Password form, kept shut until it is asked for.

    PESO, 2026-08-26: left open, the three password boxes sit under the profile
    form and read as more of it. People fill them in on their way down the page
    and change a password they never meant to touch — and then cannot get back
    in. One button in front of them settles it.

    Include this immediately before the password <form>, and give that form
    `id="passwordForm" style="display:none;"`. The same markup and the same
    script serve every account type, so the four profile pages cannot drift
    into four different behaviours.
--}}
<div id="passwordIntro">
    <p class="mb-3" style="font-size:13px;color:var(--n-500);">
        Your password is not shown here. Open this only when you actually want to change it.
    </p>
    <button type="button" class="btn btn-peso px-4" onclick="togglePasswordForm(true)">
        <i class="ph ph-lock-simple me-1"></i>Change Password
    </button>
</div>

<script>
    function togglePasswordForm(open) {
        const intro = document.getElementById('passwordIntro');
        const form  = document.getElementById('passwordForm');
        if (!intro || !form) return;

        intro.style.display = open ? 'none' : '';
        form.style.display  = open ? '' : 'none';

        // Limpyohan pag-sirado: ang natago nga kahon nga naay sulod mopadala
        // gihapon niini sa sunod nga pag-save sa porma.
        if (!open) form.reset();
    }

    // A failed change bounces back with errors — reopen so the person can see
    // them, instead of landing on a shut panel with a red message above it.
    @if($errors->hasAny(['current_password', 'new_password', 'new_password_confirmation', 'password']))
        document.addEventListener('DOMContentLoaded', () => togglePasswordForm(true));
    @endif
</script>
