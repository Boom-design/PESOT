{{--
    Ask for the start date the moment an applicant is marked Hired.

    PESO CDO client, 2026-09-13: a hire with no start date told the jobseeker
    nothing about when to report, and gave the office no way to tell a hire
    that began from one that was only promised. Every Hired button in the
    employer portal — company interview, in-house and job fair — goes through
    this one dialog, so the question cannot be skipped on one channel.

    Each Hired form carries a hidden `start_date` input and a button with the
    class `confirm-hired`. One delegated listener serves every such button on
    the page, however many rows there are. The server still requires the date
    (CompanyWebController::updateApplicantStatus); this only asks for it.

    Include it anywhere a Hired form is rendered. @once keeps it to one copy.
--}}
@once
@push('scripts')
<script>
    document.addEventListener('click', function (event) {
        const button = event.target.closest('.confirm-hired');
        if (!button) return;

        event.preventDefault();

        const form  = button.closest('form');
        const field = form ? form.querySelector('input[name="start_date"]') : null;
        if (!form || !field) return;

        Swal.fire({
            title: 'Mark as Hired?',
            html: 'The applicant will be notified. You can change this later if they do not report for work.'
                + '<br><br><strong>When does this applicant start work?</strong>',
            icon: 'question',
            input: 'date',
            inputValue: field.value || button.dataset.today || '',
            showCancelButton: true,
            confirmButtonColor: '#28812F',
            cancelButtonColor: '#9aa5b1',
            confirmButtonText: 'Yes, mark as hired',
            inputValidator: (value) => value ? undefined : 'Enter the date this applicant starts work.',
        }).then((result) => {
            if (!result.isConfirmed) return;
            field.value = result.value;
            form.submit();
        });
    });
</script>
@endpush
@endonce
