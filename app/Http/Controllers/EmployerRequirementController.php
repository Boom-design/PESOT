<?php

namespace App\Http\Controllers;

use App\Models\EmployerRequirement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class EmployerRequirementController extends Controller
{
    // ── Helper ──
    private function authCompany()
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'company') {
            return null;
        }
        return $user;
    }

    // ── VIEW REQUIREMENTS PAGE ──
    public function index()
    {
        $company = $this->authCompany();
        if (!$company) return redirect()->route('login');

        $requirement = EmployerRequirement::where('user_id', $company->activeCompany()->employer_nsrp_registrations_id)->first();

        return view('company.requirements.index', compact('company', 'requirement'));
    }

    // ── SUBMIT / RESUBMIT REQUIREMENTS ──
    public function store(Request $request)
    {
        $company = $this->authCompany();
        if (!$company) return redirect()->route('login');

        $existing = EmployerRequirement::where('user_id', $company->activeCompany()->employer_nsrp_registrations_id)->first();

        $fields = [
            'business_permit',
            'sec_dti',
            'company_profile',
            'no_pending_case_certificate',
            'vacancy_posting',
        ];

        // ── Kung "rejected" o "expired" na sa una, ug naay specific rejected_fields, ──
        // ── i-require lang nga i-resubmit kadtong mga gi-flag; ang uban optional ──
        $isPartialResubmit = $existing
            && in_array($existing->status, ['rejected', 'expired'])
            && !empty($existing->rejected_fields);

        $fieldsToRequire = $isPartialResubmit ? $existing->rejected_fields : [];

        $rules = [];
        foreach ($fields as $field) {
            $rules[$field] = (in_array($field, $fieldsToRequire) ? 'required' : 'nullable') . '|file|mimes:jpg,jpeg,png,pdf|max:5120';

            // The business permit is dated by the year it covers, not by an
            // expiry the employer has to read off the paper. See
            // EmployerRequirement::businessPermitGraceEndsAt().
            if ($field !== 'business_permit') {
                $rules["{$field}_expires_at"] = 'nullable|required_with:' . $field . '|date|after:today';
            }
        }

        $rules['business_permit_year'] = 'nullable|required_with:business_permit|integer|min:'
            . (now()->year - 2) . '|max:' . (now()->year + 1);

        // The logo is a picture, never a PDF, and it has no expiry.
        $rules['company_logo'] = 'nullable|image|mimes:jpg,jpeg,png|max:2048';

        $request->validate($rules, [
            'required' => 'This document is required.',
            'mimes'    => 'Only JPG, PNG, or PDF files are allowed.',
            'max'      => 'File size must not exceed 5MB.',
            'business_permit_year.required_with' => 'Say which year this business permit covers.',
            'company_logo.image' => 'The logo must be a JPG or PNG image.',
            'company_logo.max'   => 'The logo must be 2MB or smaller.',
        ]);

        // ── ANG LOGO WALAY REVIEW ──
        //
        // Ang logo dili dokumento — usa siya ka hulagway aron mailhan sa desk
        // kung kinsa ang ilang gitan-aw. Apan usa ra ka porma ang tanan, mao
        // nga ang employer nga nag-ilis ug logo nagpadala sa TIBUOK porma, ug
        // ang status nabalik sa 'pending'. Ang lima ka aprubado nga dokumento
        // niya nahimong "under review" pag-usab, ug ang desk gipadad-an ug
        // notipikasyon nga walay bag-o nga basahon.
        //
        // Kung ang logo RA ang gipadala, ang status, ang remarks ug ang
        // rejected_fields dili tandogon, ug walay notipikasyon.
        $submittedDocuments = collect($fields)->contains(fn($f) => $request->hasFile($f));

        $data = ['user_id' => $company->activeCompany()->employer_nsrp_registrations_id];

        if ($submittedDocuments) {
            $data['status']          = 'pending';
            $data['remarks']         = null;
            $data['rejected_fields'] = null; // i-clear pag mag-resubmit
        }

        foreach ($fields as $field) {
            if ($request->hasFile($field)) {
                // Delete old file if exists
                if ($existing && $existing->$field) {
                    Storage::disk('local')->delete($existing->$field);
                }
                $path = $request->file($field)->store('employer_requirements', 'local');
                $data[$field] = $path;

                if ($field === 'business_permit') {
                    // A permit runs to the last day of the year it covers. The
                    // date is written from the year rather than typed, so the
                    // two can never disagree.
                    $year = (int) $request->input('business_permit_year');
                    $data['business_permit_year']       = $year;
                    $data['business_permit_expires_at'] = \Carbon\Carbon::create($year, 12, 31)->toDateString();
                    // A fresh permit opens a fresh cycle: next year's grace
                    // reminder has to be allowed to fire again.
                    $data['business_permit_grace_notified_at'] = null;
                } else {
                    $data["{$field}_expires_at"] = $request->input("{$field}_expires_at");
                }

                $data["{$field}_expiry_notified_at"] = null; // reset the 1-week-before warning gate for the new cycle
            }
            // Kung walay bag-ong file gi-upload, ang naa nang value magpabilin (dili ma-overwrite sa null)
        }

        // ── LOGO ── Kept out of the loop above: it is not reviewed, not
        // rejected, and never expires, so none of that machinery applies to it.
        if ($request->hasFile('company_logo')) {
            if ($existing && $existing->company_logo) {
                Storage::disk('local')->delete($existing->company_logo);
            }
            $data['company_logo'] = $request->file('company_logo')->store('employer_requirements', 'local');
        }

        if ($existing) {
            $existing->update($data);
        } else {
            EmployerRequirement::create($data);
        }

        // Ang logo ra: wala nay tan-awon sa desk, mao nga wala nay ipadala.
        if (!$submittedDocuments) {
            return redirect()->route('company.requirements')
                ->with('success', 'Company logo updated. It is not reviewed, so your documents are unchanged.');
        }

        $isOverseas = $company->activeCompany() && $company->activeCompany()->is_overseas;

// Notify Vacancy Staff (local company)
if (!$isOverseas) {
            $staffIds = \App\Models\Staff::where('staff_role', 'job_vacancy')->pluck('staff_id');

            \App\Models\Announcement::sendToStaff([
                'type'           => 'requirements_submitted',
                'title'          => 'New Requirements Submitted 📋',
                'message'        => $company->activeCompany()->company_name . ' has submitted their requirements for review.',
                'reference_type' => 'employer_requirement',
                'reference_id'   => $existing ? $existing->employer_requirements_id : \App\Models\EmployerRequirement::where('user_id', $company->activeCompany()->employer_nsrp_registrations_id)->first()->employer_requirements_id,
            ], $staffIds);
        }

        // Notify SRA Staff (overseas company)
if ($isOverseas) {
            $staffIds = \App\Models\Staff::where('staff_role', 'sra')->pluck('staff_id');

            \App\Models\Announcement::sendToStaff([
                'type'           => 'requirements_submitted',
                'title'          => 'New Requirements Submitted 📋',
                'message'        => $company->activeCompany()->company_name . ' has submitted their requirements for review.',
                'reference_type' => 'employer_requirement',
                'reference_id'   => $existing ? $existing->employer_requirements_id : \App\Models\EmployerRequirement::where('user_id', $company->activeCompany()->employer_nsrp_registrations_id)->first()->employer_requirements_id,
            ], $staffIds);
        }

        return redirect()->route('company.requirements')
            ->with('success', 'Requirements submitted successfully! PESO staff will review your documents.');
    }

}