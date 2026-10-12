{{--
    Keep a text input to digits as it is typed.

    `maxlength` and `pattern` between them cap the length and refuse a bad value
    on submit, but neither stops a letter being typed in the first place — the
    box fills with text and complains later. Stripping as it is typed means the
    wrong character never appears, which is how the field reads to the person
    filling it in.

    Two forms ask for the same TIN — the public employer registration and the
    staff walk-in — so this is written once. A second copy is a second chance
    for one of them to drift.

    Pass $for = 'the-input-id'.

    The caret is put back where it was, minus whatever was removed ahead of it.
    Without that, correcting a digit in the middle throws the cursor to the end
    on every keystroke.
--}}
@php
    $digitsOnlyFor = $for ?? null;
@endphp

@if($digitsOnlyFor)
<script>
(function () {
    const field = document.getElementById(@json($digitsOnlyFor));
    if (!field || field.dataset.digitsOnly) return;
    field.dataset.digitsOnly = '1';

    field.addEventListener('input', function () {
        const before = field.value;
        const clean  = before.replace(/\D/g, '');

        if (clean === before) return;

        const caret = field.selectionStart ?? before.length;
        const ahead = before.slice(0, caret);
        const removedBeforeCaret = ahead.length - ahead.replace(/\D/g, '').length;

        field.value = clean;

        const at = Math.max(0, caret - removedBeforeCaret);
        field.setSelectionRange(at, at);
    });

    // A pasted "123-456-789" should become the number, not be refused.
    field.addEventListener('paste', function () {
        setTimeout(() => field.dispatchEvent(new Event('input')), 0);
    });
})();
</script>
@endif
