{{-- A device that has been signed out should not have to click to find out.

     The session ends on the server the moment the same account signs in
     somewhere else, but nothing reaches this browser: the page sits there
     looking live, and the person only learns what happened when they next
     touch something.

     So the page asks for itself, every few seconds, and says so the moment
     the answer changes.

     Pass `accent` to match the role's own button colour. --}}
@php($accent = $accent ?? '#28812F')
<script>
(function () {
    const STATE_URL = @json(route('session.state'));
    const LOGIN_URL = @json(route('login'));
    const EVERY_MS  = 10000;

    let timer  = null;
    let told   = false;

    async function check() {
        if (told) return;

        let data;
        try {
            const res = await fetch(STATE_URL, {
                headers: { 'Accept': 'application/json' },
                cache: 'no-store',
            });
            if (!res.ok) return;
            data = await res.json();
        } catch (e) {
            return;   // offline, or the laptop just woke up — ask again later
        }

        // A plain signed-out state needs no dialog: a normal logout has
        // already taken the person to the login page itself.
        if (data.state !== 'taken_over') return;

        told = true;
        if (timer) clearInterval(timer);

        Swal.fire({
            title: 'Signed out',
            text: data.message,
            icon: 'info',
            confirmButtonColor: @json($accent),
            confirmButtonText: 'OK',
            allowOutsideClick: false,
            allowEscapeKey: false,
        }).then(function () {
            window.location.replace(LOGIN_URL);
        });
    }

    timer = setInterval(check, EVERY_MS);

    // Coming back to the tab is the moment the answer matters most, and the
    // interval may have been throttled to nothing while it was hidden.
    document.addEventListener('visibilitychange', function () {
        if (!document.hidden) check();
    });
})();
</script>
