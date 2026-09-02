{{-- The five papers and the decision on each one.

     The desk reads a folder a paper at a time, so each paper carries its own
     Approve and Reject. Neither button moves the company and neither sends the
     employer anything — those two things happen once, on the buttons below
     this list, after all five have been decided.

     Shared by the requirements page and the review modal on the Employers page:
     the desk found the modal first, and a decision made in one place has to
     mean the same thing in the other.

     Expects: $requirement, $perDoc (may this desk decide, one paper at a time). --}}
@php
    $decisionDocs  = \App\Models\EmployerRequirement::DOCUMENT_LABELS;
    $decidedCount  = $requirement->decidedDocumentCount();
    $stillToReview = $requirement->documentsNotYetDecided();
    $hasRejected   = $requirement->hasRejectedDocuments();
    $reviewId      = $requirement->employer_requirements_id;
@endphp

<div class="p-2 rounded-3 mb-2"
     style="background:{{ $hasRejected ? 'var(--danger-bg)' : ($stillToReview ? 'var(--warn-bg)' : 'var(--n-50)') }};
            border:1px solid {{ $hasRejected ? 'var(--danger-br)' : ($stillToReview ? 'var(--warn-br)' : 'var(--n-200)') }};">
    <div class="fw-semibold mb-1"
         style="font-size:11.5px;color:{{ $hasRejected ? 'var(--danger)' : ($stillToReview ? 'var(--warn)' : 'var(--g-700)') }};">
        {{ $decidedCount }} of {{ count($decisionDocs) }} documents reviewed
    </div>

    @foreach($decisionDocs as $field => $label)
    <div class="py-1" style="border-top:1px solid rgba(0,0,0,0.05);">
        <div class="d-flex justify-content-between align-items-center gap-2">
            <span style="font-size:11.5px;color:var(--g-700);line-height:1.3;">{{ $label }}</span>

            @if(!$requirement->$field)
                <span style="font-size:10.5px;color:var(--n-400);white-space:nowrap;">Not uploaded</span>

            @elseif($requirement->isFieldAccepted($field))
                <span class="d-flex align-items-center gap-1" style="white-space:nowrap;">
                    <span class="fw-semibold" style="color:var(--g-600);font-size:10.5px;">
                        <i class="ph-fill ph-check-circle"></i> Approved
                    </span>
                    @if($perDoc)
                        @include('staff.requirements._undo_document', ['field' => $field])
                    @endif
                </span>

            @elseif($requirement->isFieldRejected($field))
                <span class="d-flex align-items-center gap-1" style="white-space:nowrap;">
                    <span class="fw-semibold" style="color:var(--danger);font-size:10.5px;">
                        <i class="ph-fill ph-x-circle"></i> Rejected
                    </span>
                    @if($perDoc)
                        @include('staff.requirements._undo_document', ['field' => $field])
                    @endif
                </span>

            @elseif($perDoc)
                <span class="d-flex align-items-center gap-1" style="white-space:nowrap;">
                    <form action="{{ route('staff.requirements.documents.accept', [$reviewId, $field]) }}"
                          method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm fw-semibold px-2 py-0"
                            style="border:1px solid var(--g-600);color:var(--g-600);background:#fff;
                                   border-radius:6px;font-size:10.5px;">
                            <i class="ph ph-check"></i> Approve
                        </button>
                    </form>
                    <button type="button" class="btn btn-sm fw-semibold px-2 py-0"
                        style="border:1px solid var(--danger-br);color:var(--danger);background:#fff;
                               border-radius:6px;font-size:10.5px;"
                        data-bs-toggle="collapse" data-bs-target="#decideRej{{ $reviewId }}_{{ $field }}">
                        <i class="ph ph-x"></i> Reject
                    </button>
                </span>

            @else
                <span style="font-size:10.5px;color:var(--n-400);white-space:nowrap;">Not reviewed</span>
            @endif
        </div>

        @if($requirement->fieldRejectionNote($field))
            <div style="font-size:10px;color:var(--danger);margin-top:2px;line-height:1.35;">
                {{ $requirement->fieldRejectionNote($field) }}
            </div>
        @endif

        {{-- Ang hinungdan gipangayo dayon. Ang papel nga gibalibaran nga walay
             hinungdan mobalik sa employer nga wala mahibalo unsay ayohon. --}}
        @if($perDoc && $requirement->$field && !$requirement->isFieldDecided($field))
            <div class="collapse" id="decideRej{{ $reviewId }}_{{ $field }}">
                <form action="{{ route('staff.requirements.documents.reject', [$reviewId, $field]) }}"
                      method="POST" class="d-flex gap-1 mt-1">
                    @csrf
                    <input type="text" name="reason" maxlength="255" required
                        class="form-control form-control-sm py-0"
                        style="border:1px solid var(--danger-br);border-radius:6px;font-size:10.5px;"
                        placeholder="What is wrong with it?">
                    <button type="submit" class="btn btn-sm btn-danger fw-semibold px-2 py-0"
                        style="border-radius:6px;font-size:10.5px;">
                        Reject
                    </button>
                </form>
            </div>
        @endif
    </div>
    @endforeach

    @if($hasRejected)
        <div style="font-size:10.5px;color:var(--danger);margin-top:6px;line-height:1.4;">
            Send the folder back with <strong>Decline Requirements</strong> — the employer gets one
            message listing every rejected document.
        </div>
    @endif
</div>
