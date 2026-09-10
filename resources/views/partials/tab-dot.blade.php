{{--
    Red count on a tab, the same red the sidebar uses.

    The number on a sidebar item has to be traceable: press it, and the tabs
    inside say which of them the number came from. Without this the desk is
    told there are two things waiting and then has to open every tab to find
    them.

    .nav-dot is styled inside the sidebar only, so the tab carries its own copy
    of the same look rather than borrowing a class that is not there.

    Expects: $count. Optionally $on (bool) — true when this tab is the lit one,
    which inverts the colours so the number stays readable on the dark button.
--}}
@php $tabDotCount = (int) ($count ?? 0); @endphp

@if($tabDotCount > 0)
    <span title="{{ $tabDotCount }} item(s) need your attention"
          style="display:inline-flex;align-items:center;justify-content:center;min-width:17px;
                 height:17px;padding:0 5px;margin-left:6px;border-radius:999px;
                 background:{{ ($on ?? false) ? '#fff' : 'var(--danger)' }};
                 color:{{ ($on ?? false) ? 'var(--danger)' : '#fff' }};
                 font-size:10px;font-weight:700;line-height:1;vertical-align:middle;">{{ $tabDotCount > 9 ? '9+' : $tabDotCount }}</span>
@endif
