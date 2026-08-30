<?php

namespace App\Http\Controllers;

use App\Models\EmployerRequirement;
use App\Models\JobseekerNsrpRegistration;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * The one door to every uploaded document that is not meant for the public.
 *
 * Business permits, TINs and training certificates used to sit on the `public`
 * disk and be linked directly as `/storage/employer_requirements/<hash>.jpg`.
 * The filenames are random, so nobody could browse them — but the URL carried
 * no identity at all. Anyone holding one, forever, could open the file:
 * copied into an email, left in the history of a shared computer, forwarded
 * once by mistake. For a system that holds NSRP data, that is not good enough.
 *
 * Now the files live on the `local` disk, which is rooted at
 * `storage/app/private` and is not served by the web server at all. Every read
 * comes through here, and every read is checked against who is asking.
 *
 * Deliberately NOT behind this controller: profile photos, job posters and job
 * images. Those are meant to be seen — the poster is on the public job list
 * that guests browse without an account.
 */
class DocumentController extends Controller
{
    /** The six document columns on employer_requirements, as an allow-list. */
    private const REQUIREMENT_FIELDS = [
        // Not a requirement document, but it is stored on the same row and read
        // back the same way, so it goes through the same allow-list.
        'company_logo',
        'business_permit',
        'sec_dti',
        'company_profile',
        'nsrp_establishment_form',
        'no_pending_case_certificate',
        'vacancy_posting',
    ];

    /**
     * One of an employer's six requirement documents.
     *
     * Readable by PESO staff and the admin, who review them, and by the
     * employer the document belongs to. Anybody else gets a 403, including
     * another employer holding the URL.
     */
    public function requirement(int $id, string $field): StreamedResponse
    {
        // The field comes from the URL, so it is checked against the allow-list
        // before it is ever used as a column name.
        abort_unless(in_array($field, self::REQUIREMENT_FIELDS, true), 404);

        $requirement = EmployerRequirement::findOrFail($id);
        $user        = Auth::user();

        abort_unless($this->maySeeRequirement($user, $requirement), 403);

        return $this->stream($requirement->{$field});
    }

    /**
     * One training certificate attached to a jobseeker's NSRP form.
     *
     * `training_certificates` is a JSON array of paths, so the URL carries the
     * position in that array rather than a filename — a filename in the URL
     * would let anyone request any file on the disk.
     */
    public function certificate(int $id, int $index): StreamedResponse
    {
        $nsrp = JobseekerNsrpRegistration::with('registration')->findOrFail($id);
        $user = Auth::user();

        abort_unless($this->maySeeCertificate($user, $nsrp), 403);

        $paths = is_array($nsrp->training_certificates)
            ? $nsrp->training_certificates
            : json_decode($nsrp->training_certificates ?? '[]', true);

        return $this->stream($paths[$index] ?? null);
    }

    /** Staff and admin review these; the employer owns them. */
    private function maySeeRequirement($user, EmployerRequirement $requirement): bool
    {
        if (!$user) return false;
        if (in_array($user->role, ['staff', 'admin'], true)) return true;

        // Despite its name, employer_requirements.user_id holds the
        // employer_nsrp_registrations_id — the same value the employer's own
        // requirements page looks itself up by.
        return $user->role === 'company'
            // Bisan asa sa iyang mga kompanya, dili ang aktibo ra: ang papel
            // iya gihapon bisan lain ang kompanya nga iyang gitan-aw karon.
            && $user->employerCompanies()
                ->where('employer_nsrp_registrations_id', $requirement->user_id)
                ->exists();
    }

    /** Staff and admin process these; the jobseeker owns them. */
    private function maySeeCertificate($user, JobseekerNsrpRegistration $nsrp): bool
    {
        if (!$user) return false;
        if (in_array($user->role, ['staff', 'admin'], true)) return true;

        return $user->role === 'jobseeker'
            && $nsrp->registration
            && (int) $nsrp->registration->user_id === (int) $user->users_id;
    }

    /**
     * Send the file inline so the browser previews it in the existing modal
     * rather than downloading it.
     *
     * A path that is empty, or that climbs out of the disk root, is a 404 — the
     * caller does not learn which of the two it was.
     */
    private function stream(?string $path): StreamedResponse
    {
        abort_if(!$path || str_contains($path, '..'), 404);
        abort_unless(Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path, null, [
            'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
