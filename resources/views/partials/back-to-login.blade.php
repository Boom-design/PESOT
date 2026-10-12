{{--
    A way out of a registration form.

    PESO, 2026-10-13: both registration pages offered "Already have an account?
    Sign in" at the very bottom, under every field. Somebody who opened the
    wrong form, or who changed their mind on the first question, had to scroll
    past the whole thing to leave it — or use the browser's back button, which
    on a bounced form re-posts or shows a stale copy.

    The colour is written for the dark glass background. Both pages carry a
    light-mode block that rewrites `color:rgba(255,255,255...` to var(--n-500),
    so the one spelling reads correctly in both.
--}}
<a href="{{ route('login') }}"
   style="display:inline-flex;align-items:center;gap:6px;text-decoration:none;
          font-size:12.5px;font-weight:600;color:rgba(255,255,255,0.85);margin-bottom:10px;">
    <i class="ph-bold ph-arrow-left"></i> Back to login
</a>
