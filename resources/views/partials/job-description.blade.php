{{--
    A job description, laid out so it can be read.

    It is prose, not a checklist, so it is set as prose: the first line of each
    paragraph is indented and every line after it runs flush to the left margin,
    the way a printed document sets one.

    That only works if the text is allowed to fill the width. A single newline
    is therefore soft — the sentences on either side of it join into one flowing
    paragraph — and only a blank line starts a new one. Breaking at every
    newline gave a column of short stubs with the whole right side empty, and
    nothing to indent against.

    A bullet the employer typed themselves is dropped from the front of a line:
    it would end up stranded in the middle of a joined sentence.

    Expects: $description. Optional $descriptionSize (default 13px).
--}}
@php
    $descSize = $descriptionSize ?? '13px';

    $descParagraphs = collect(preg_split('/(\r?\n)\s*(\r?\n)/', (string) $description))
        ->map(function ($block) {
            return collect(preg_split('/\r\n|\r|\n/', $block))
                ->map(fn($line) => trim(preg_replace('/^([-*\x{2022}\x{00B7}]|\d+[.)])\s*/u', '', trim($line))))
                ->filter()
                ->implode(' ');
        })
        ->filter()
        ->values();
@endphp

@if($descParagraphs->isEmpty())
    <div style="font-size:{{ $descSize }};color:var(--n-500);">No description provided.</div>
@else
    @foreach($descParagraphs as $paragraph)
        <p style="font-size:{{ $descSize }};color:var(--n-700);line-height:1.8;
                  text-indent:2em;text-align:justify;margin:0 0 6px;">{{ $paragraph }}</p>
    @endforeach
@endif
