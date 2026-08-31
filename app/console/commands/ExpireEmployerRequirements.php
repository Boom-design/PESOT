<?php

namespace App\Console\Commands;

use App\Models\Announcement;
use App\Models\EmployerRequirement;
use Illuminate\Console\Command;

class ExpireEmployerRequirements extends Command
{
    protected $signature = 'employer-requirements:expire';
    protected $description = 'Restrict an employer once the renewal grace on their Business Permit has run out';

    // ── Per PESO advisor: Business Permit ra ang basihan sa pag-deactivate/restrict.
    // ── Ang uban 4 (SEC/DTI, Company Profile, No Pending Case, Vacancy Posting) pwede
    // ── ma-expire nga independent (SEC/DTI kay 3-5 years ra ang bili pananglit) apan
    // ── ang employer maka-post gihapon og job basta valid pa ang Business Permit. ──
    //
    // ── 'restricted' ang gigamit, dili 'deactivated': ang 'deactivated' mo-block sa
    // ── login mismo, mao nga dili na makasulod ang employer sa Requirements page —
    // ── didto ra man niya ma-upload ang bag-o niyang permit. Ang 'restricted' kay
    // ── makasulod gihapon, dili lang maka-post ug dili ma-imbitar sa job fair. ──
    public function handle()
    {
        $totalExpired = 0;
        $totalRestricted = 0;

        // ── Ang permit tinuig, ug ang tuig mismo dili pa igo nga hinungdan sa
        // ── pag-restrict. Ang siyudad dili mohatag sa bag-ong permit sa Enero 1;
        // ── ang renewal season moabot sa unang quarter. Mao nga ang gisukod dinhi
        // ── mao ang katapusan sa palugit — tan-awa ang
        // ── EmployerRequirement::businessPermitGraceEndsAt(). ──
        $months   = (int) config('peso.employer.business_permit_grace_months', 3);
        $cutoff   = today()->subMonths($months)->year;   // ang tuig nga nahurot na ang palugit

        $expired = EmployerRequirement::with('employer')
            ->where('status', 'approved')
            ->whereNotNull('business_permit_year')
            ->where('business_permit_year', '<', $cutoff)
            ->get()
            ->filter(fn($requirement) => $requirement->isBusinessPermitOverdue());

        foreach ($expired as $requirement) {
            $label    = $requirement->businessPermitLabel();
            $dueYear  = $requirement->business_permit_year + 1;
            $deadline = $requirement->businessPermitGraceEndsAt()->format('F j, Y');

            $requirement->update([
                'status'          => 'expired',
                'rejected_fields' => ['business_permit'],
                'remarks'         => "Your {$label} covers a year that has passed, and the renewal window closed on {$deadline}. Please upload your {$dueYear} business permit.",
            ]);

            // ── Auto-block. Ang 'deactivated' walay labot dinhi — kana kay manual
            // ── nga desisyon sa Admin para sa kompanya nga nagsara na. ──
            $user = $requirement->employer?->employer;
            if ($user && $user->status === 'approved') {
                $user->update(['status' => 'restricted']);
                $totalRestricted++;
            }

            Announcement::sendToEmployers([
                'type'           => 'employer_requirement_expired',
                'title'          => 'Business Permit Expired ⏳',
                'message'        => "Your {$label} covers a year that has passed and the renewal window closed on {$deadline}, so job posting and job fair invitations are paused for your account. You can still log in — upload your {$dueYear} business permit on the Requirements page and access resumes as soon as PESO staff approve it.",
                'reference_type' => 'employer_requirement',
                'reference_id'   => $requirement->employer_requirements_id,
            ], $requirement->user_id);

            $totalExpired++;
        }

        $this->info("Expired {$totalExpired} employer requirement(s) (Business Permit renewal overdue); restricted {$totalRestricted} account(s).");
        return 0;
    }
}
