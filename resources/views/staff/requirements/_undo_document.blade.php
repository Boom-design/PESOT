{{-- The way back from a decision on one document.

     Both an approval and a rejection are undone the same way: the paper drops
     out of both lists and goes back to unreviewed. Nothing has left the office
     yet at this point — the employer is told only when the folder is declined
     — so an undo costs nothing and needs no reason. --}}
<form action="{{ route('staff.requirements.documents.undo', [$requirement->employer_requirements_id, $field]) }}"
      method="POST" class="d-inline">
    @csrf
    <button type="submit" class="btn btn-sm p-0 border-0"
        style="background:none;color:var(--n-500);font-size:11px;text-decoration:underline;">
        Undo
    </button>
</form>
