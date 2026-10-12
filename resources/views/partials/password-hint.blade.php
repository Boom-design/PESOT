{{--
    Password requirement checklist. Shown next to every field that sets a
    password so the rule is visible before submitting rather than only after a
    rejection.

    PESO, 2026-10-13: this used to be one sentence — "Must be at least 8
    characters and include an uppercase letter, a lowercase letter, a number,
    and a special character." Nobody read to the end of it. People typed
    something, were rejected, and had no way of telling which of the four
    things they had missed. Each rule is now its own line and ticks as it is
    met, so the one still outstanding is the one still showing grey.

    The tests mirror Illuminate\Validation\Rules\Password exactly, including the
    unicode property escapes — a checklist that ticks on a password the server
    then rejects would be worse than the sentence it replaced. `mixedCase` is
    one rule there and two lines here: it passes precisely when the password
    holds at least one lowercase letter and at least one uppercase letter, so
    splitting it loses nothing and says twice as much.

    Pass $onDark = true when the field sits on a green/photo background (the
    public auth pages) so the text stays readable.

    Pass $for = 'the-input-id' when the field this belongs to is not the nearest
    password input before this block. Nothing needs it today; the block is
    included directly under its own field everywhere.
--}}
@php
    $onDark = $onDark ?? false;
    $for    = $for ?? null;
    $uid    = 'pwhint' . \Illuminate\Support\Str::random(6);

    $textColor = $onDark ? 'rgba(255,255,255,0.78)' : 'var(--n-500)';
    $metColor  = $onDark ? '#86efac'                : 'var(--g-600)';
@endphp

<div id="{{ $uid }}" class="pw-hint" data-for="{{ $for }}"
     style="font-size:11px;line-height:1.7;margin-top:5px;color:{{ $textColor }};">
    <div class="pw-hint-rule" data-rule="length"><i class="ph ph-circle"></i> At least 8 characters</div>
    <div class="pw-hint-rule" data-rule="upper"><i class="ph ph-circle"></i> One uppercase letter</div>
    <div class="pw-hint-rule" data-rule="lower"><i class="ph ph-circle"></i> One lowercase letter</div>
    <div class="pw-hint-rule" data-rule="number"><i class="ph ph-circle"></i> One number</div>
    <div class="pw-hint-rule" data-rule="symbol"><i class="ph ph-circle"></i> One special character</div>
</div>

<style>
    .pw-hint .pw-hint-rule { display:flex; align-items:center; gap:6px; transition:color 0.15s; }
    .pw-hint .pw-hint-rule i { font-size:13px; flex:0 0 auto; }
</style>

<script>
(function () {
    const hint = document.getElementById('{{ $uid }}');
    if (!hint || hint.dataset.bound) return;
    hint.dataset.bound = '1';

    const met = '{{ $metColor }}';

    // Which field this belongs to. Named explicitly when $for was passed;
    // otherwise the nearest password field before this block, which is where
    // the include always sits. The input may be wrapped — the show/hide toggle
    // puts it inside a .input-wrap or .position-relative — so each previous
    // sibling is searched as well as tested.
    function findField() {
        const named = hint.dataset.for;
        if (named) return document.getElementById(named);

        const selector = 'input[type="password"], input[type="text"][name*="password"]';

        for (let node = hint.previousElementSibling; node; node = node.previousElementSibling) {
            if (node.matches(selector)) return node;
            const inner = node.querySelector(selector);
            if (inner) return inner;
        }

        return hint.closest('form')?.querySelector(selector) ?? null;
    }

    const field = findField();
    if (!field) return;

    // Mirrors Illuminate\Validation\Rules\Password. Do not "simplify" these to
    // [A-Z] and friends: the server tests unicode categories, so a password of
    // accented or non-Latin letters would tick differently here than there.
    const tests = {
        length: v => [...v].length >= 8,
        upper:  v => /\p{Lu}/u.test(v),
        lower:  v => /\p{Ll}/u.test(v),
        number: v => /\pN/u.test(v),
        symbol: v => /\p{Z}|\p{S}|\p{P}/u.test(v),
    };

    const rules = hint.querySelectorAll('.pw-hint-rule');

    function paint() {
        const value = field.value;

        rules.forEach(rule => {
            const passed = tests[rule.dataset.rule](value);
            const icon   = rule.querySelector('i');

            // Grey and an empty circle until it is met, so an untouched field
            // reads as a list of requirements rather than a list of failures.
            // Nothing turns red: the person is still typing.
            icon.className   = passed ? 'ph-fill ph-check-circle' : 'ph ph-circle';
            rule.style.color = passed ? met : '';
        });
    }

    field.addEventListener('input', paint);
    paint();
})();
</script>
