<?php

namespace App\Console\Commands;

use App\Models\Announcement;
use App\Models\EmployerRequirement;
use Illuminate\Console\Command;

class SendEmployerRequirementExpiryWarnings extends Command
{
    protected $signature = 'employer-requirements:send-expiry-warnings';
    protected $description = 'Notify employers 7 days before a document expires, and while a business permit renewal is outstanding';

    public function handle()
    {
        $totalSent = 0;
        $fields = array_keys(EmployerRequirement::DOCUMENT_LABELS);

        // ── Per field, kay 5 ka parallel expiry column ang naa (dili row-per-doc) ──
        $duePerRequirement = []; // employer_requirements_id => ['field' => Carbon date, ...]

        foreach ($fields as $field) {
            $due = EmployerRequirement::where('status', 'approved')
                ->whereNotNull("{$field}_expires_at")
                ->whereDate("{$field}_expires_at", '>=', today())
                ->whereDate("{$field}_expires_at", '<=', today()->addDays(7))
                ->whereNull("{$field}_expiry_notified_at")
                ->get(['employer_requirements_id', 'user_id', "{$field}_expires_at"]);

            foreach ($due as $requirement) {
                $duePerRequirement[$requirement->employer_requirements_id]['requirement'] = $requirement;
                $duePerRequirement[$requirement->employer_requirements_id]['fields'][$field] = $requirement->{"{$field}_expires_at"};
            }
        }

        foreach ($duePerRequirement as $employerRequirementsId => $entry) {
            $requirement = EmployerRequirement::find($employerRequirementsId);

            $labels = collect($entry['fields'])->map(function ($date, $field) {
                return EmployerRequirement::DOCUMENT_LABELS[$field] . ' (expires ' . $date->format('M d, Y') . ')';
            })->implode(', ');

            // ── Business Permit ra ang basihan sa pag-restrict — ang uban 4 kay reminder ra, dili mag-cause og access loss ──
            $includesBusinessPermit = array_key_exists('business_permit', $entry['fields']);
            $message = $includesBusinessPermit
                ? 'The following document(s) will expire soon: ' . $labels . '. Your Business Permit is the basis for account access — please resubmit before it expires to avoid losing access to PESO services.'
                : 'The following document(s) will expire soon: ' . $labels . '. Please resubmit updated copies when convenient — this does not affect your ability to post jobs as long as your Business Permit remains valid.';

            Announcement::sendToEmployers([
                'type'           => 'employer_requirement_expiring',
                'title'          => 'Document Expiring Soon ⏳',
                'message'        => $message,
                'reference_type' => 'employer_requirement',
                'reference_id'   => $requirement->employer_requirements_id,
            ], $requirement->user_id);

            $update = [];
            foreach (array_keys($entry['fields']) as $field) {
                $update["{$field}_expiry_notified_at"] = now();
            }
            $requirement->update($update);

            $totalSent++;
        }

        $totalSent += $this->remindDuringPermitGrace();

        $this->info("Sent {$totalSent} employer requirement expiry warning(s).");
        return 0;
    }

    /**
     * The reminder that goes out while a business permit renewal is outstanding.
     *
     * The warning above fires in the last week of December, against the permit's
     * own 31 December expiry. That is the wrong moment to be the only warning:
     * the city is not issuing the new permit yet, and by the time the employer
     * can act the message is months old. This one goes out once the new year has
     * started and the account is running on grace — it names the day the grace
     * runs out, which is the date that actually costs them access.
     */
    private function remindDuringPermitGrace(): int
    {
        $sent = 0;

        $inGrace = EmployerRequirement::where('status', 'approved')
            ->whereNotNull('business_permit_year')
            ->where('business_permit_year', '<', now()->year)
            ->whereNull('business_permit_grace_notified_at')
            ->get();

        foreach ($inGrace as $requirement) {
            if (!$requirement->isBusinessPermitInGrace()) {
                continue;   // already past the grace — the expire command handles it
            }

            $deadline = $requirement->businessPermitGraceEndsAt();
            $dueYear  = $requirement->business_permit_year + 1;

            Announcement::sendToEmployers([
                'type'           => 'employer_requirement_expiring',
                'title'          => 'Business Permit Renewal Due ⏳',
                'message'        => 'Your ' . $requirement->businessPermitLabel() . ' has reached the end of the year it covers. '
                    . 'Upload your ' . $dueYear . ' business permit on the Requirements page by '
                    . $deadline->format('F j, Y') . '. After that date, job posting and job fair invitations are paused '
                    . 'until the new permit is approved.',
                'reference_type' => 'employer_requirement',
                'reference_id'   => $requirement->employer_requirements_id,
            ], $requirement->user_id);

            $requirement->update(['business_permit_grace_notified_at' => now()]);
            $sent++;
        }

        return $sent;
    }
}
