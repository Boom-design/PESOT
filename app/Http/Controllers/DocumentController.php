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
    /**
     * The document columns on employer_requirements, as an allow-list.
     *
     * Both markets together: the papers of a local establishment and those of
     * an overseas agency live on the same row, and the desk reading an
     * agency's folder asks for them by the same route.
     */
    private const REQUIREMENT_FIELDS = [
        // Neither is a requirement document, but both are stored on the same
        // row and read back the same way, so they go through the same
        // allow-list.
        'company_logo',
        'nsrp_establishment_form',

        ...\App\Models\EmployerRequirement::REVIEWED_DOCUMENTS,
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

        $requirement = EmployerRequirement::with('employer')->findOrFail($id);
        $user        = Auth::user();

        abort_unless($this->maySeeRequirement($user, $requirement), 403);

        $path = $requirement->{$field};

        // ?download=1 asks for the file itself rather than a look at it. Same
        // permission either way — the question is what the browser does with
        // it, not who may have it.
        return request()->boolean('download') && $path
            ? $this->stream($path, $this->downloadName($requirement, $field, $path))
            : $this->stream($path);
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
    private function stream(?string $path, ?string $downloadAs = null): StreamedResponse
    {
        abort_if(!$path || str_contains($path, '..'), 404);
        abort_unless(Storage::disk('local')->exists($path), 404);

        // ── Tan-awon, o i-download.
        // ──
        // ── Ang file gipangalan ug hash sa pag-upload, mao nga ang usa ka
        // ── download nga nagdala sa pangalan sa disk mohatag sa desk ug
        // ── "JfJOAHoz….png" sa ilang folder. Ang ngalan sa kompanya ug ang
        // ── papel mao ang gipangita nila pag-usab unya. ──
        $disposition = $downloadAs !== null
            ? 'attachment; filename="' . $downloadAs . '"'
            : 'inline; filename="' . basename($path) . '"';

        return Storage::disk('local')->response($path, null, [
            'Content-Disposition'    => $disposition,
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /** A filename a person can find again: company, paper, extension. */
    private function downloadName(EmployerRequirement $requirement, string $field, string $path): string
    {
        $company = $requirement->employer->company_name ?? 'Employer';
        $label   = $field === 'company_logo'
            ? 'Company Logo'
            : EmployerRequirement::documentLabel($field);

        $name = preg_replace('/[^A-Za-z0-9 \-_.]/', '', $company . ' - ' . $label);

        return trim($name) . '.' . strtolower(pathinfo($path, PATHINFO_EXTENSION));
    }
}
