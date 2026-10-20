{{--
    Pulling out of something already joined.

    PESO, 2026-10-20: "basin naay emergency or something". Until now the person
    who had joined and could not go had nothing to press, and the employer kept
    waiting for them.

    It cannot be undone, so it asks first, and the question names what is being
    left and on what day. The modal is pushed to the end of the page rather
    than left inside the table cell: a modal inside a <td> inherits the table's
    stacking context and opens behind the backdrop.

    Expects:
      $action  — the route to post to
      $key     — something unique per row, for the modal id
      $what    — "the in-house interview for Cashier", read inside a sentence
      $when    — the day, or null when there is none yet
      $note    — the one consequence worth spelling out
--}}
@php
    $modalId = 'cancelJoin' . preg_replace('/[^A-Za-z0-9]/', '', (string) $key);
@endphp

<button type="button" class="btn btn-sm fw-semibold"
    data-bs-toggle="modal" data-bs-target="#{{ $modalId }}"
    style="background:#fff;color:var(--danger);border:1px solid var(--danger);
           border-radius:8px;font-size:11px;padding:4px 12px;white-space:nowrap;">
    <i class="ph ph-x-circle me-1"></i>Cancel
</button>

@push('scripts')
<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-3">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold" style="color:var(--g-700);">
                    <i class="ph-fill ph-warning-circle me-1" style="color:var(--danger);"></i>
                    Cancel your place?
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="font-size:13px;color:var(--n-700);">
                <p class="mb-2">
                    You are about to withdraw from {{ $what }}@if(!empty($when)) on
                    <span class="fw-semibold">{{ $when }}</span>@endif.
                </p>
                <p class="mb-0" style="color:var(--danger);font-size:12px;">
                    <i class="ph-fill ph-info me-1"></i>{{ $note }}
                </p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-sm fw-semibold" data-bs-dismiss="modal"
                    style="border:1px solid var(--n-200);color:var(--g-700);background:#fff;
                           border-radius:8px;font-size:12px;padding:5px 16px;">
                    Keep my place
                </button>
                <form action="{{ $action }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm fw-semibold"
                        style="background:var(--danger);color:#fff;border:none;
                               border-radius:8px;font-size:12px;padding:5px 16px;">
                        Yes, cancel
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endpush
