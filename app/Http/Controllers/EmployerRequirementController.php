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

        $requirement = EmployerRequirement::where('user_id', $company->employerNsrp->id)->first();

        return view('company.requirements.index', compact('company', 'requirement'));
    }

    // ── SUBMIT / RESUBMIT REQUIREMENTS ──
    public function store(Request $request)
    {
        $company = $this->authCompany();
        if (!$company) return redirect()->route('login');

        $existing = EmployerRequirement::where('user_id', $company->employerNsrp->id)->first();

        $fields = [
            'business_permit',
            'sec_dti',
            'company_profile',
            'no_pending_case_certificate',
            'vacancy_posting',
        ];

        // ── Kung "rejected" na sa una, ug naay specific rejected_fields, ── 
        // ── i-require lang nga i-resubmit kadtong mga gi-flag; ang uban optional ──
        $isPartialResubmit = $existing
            && $existing->status === 'rejected'
            && !empty($existing->rejected_fields);

        $fieldsToRequire = $isPartialResubmit ? $existing->rejected_fields : $fields;

        $rules = [];
        foreach ($fields as $field) {
            $rules[$field] = (in_array($field, $fieldsToRequire) ? 'required' : 'nullable') . '|file|mimes:jpg,jpeg,png,pdf|max:5120';
        }

        $request->validate($rules, [
            'required' => 'This document is required.',
            'mimes'    => 'Only JPG, PNG, or PDF files are allowed.',
            'max'      => 'File size must not exceed 5MB.',
        ]);

        // Store files
        $data = [
            'user_id'         => $company->employerNsrp->id,
            'status'          => 'pending',
            'remarks'         => null,
            'rejected_fields' => null, // i-clear pag mag-resubmit
        ];

        foreach ($fields as $field) {
            if ($request->hasFile($field)) {
                // Delete old file if exists
                if ($existing && $existing->$field) {
                    Storage::disk('public')->delete($existing->$field);
                }
                $path = $request->file($field)->store('employer_requirements/' . $company->id, 'public');
                $data[$field] = $path;
            }
            // Kung walay bag-ong file gi-upload, ang naa nang value magpabilin (dili ma-overwrite sa null)
        }

        if ($existing) {
            $existing->update($data);
        } else {
            EmployerRequirement::create($data);
        }

        $isOverseas = $company->employerNsrp && $company->employerNsrp->is_overseas;

// Notify Vacancy Staff (local company)
if (!$isOverseas) {
            $staffIds = \App\Models\Staff::where('staff_role', 'job_vacancy')->pluck('id');

            \App\Models\Announcement::sendToStaff([
                'type'           => 'requirements_submitted',
                'title'          => 'New Requirements Submitted 📋',
                'message'        => $company->employerNsrp->company_name . ' has submitted their requirements for review.',
                'reference_type' => 'employer_requirement',
                'reference_id'   => $existing ? $existing->id : \App\Models\EmployerRequirement::where('user_id', $company->employerNsrp->id)->first()->id,
            ], $staffIds);
        }

        // Notify SRA Staff (overseas company)
if ($isOverseas) {
            $staffIds = \App\Models\Staff::where('staff_role', 'sra')->pluck('id');

            \App\Models\Announcement::sendToStaff([
                'type'           => 'requirements_submitted',
                'title'          => 'New Requirements Submitted 📋',
                'message'        => $company->employerNsrp->company_name . ' has submitted their requirements for review.',
                'reference_type' => 'employer_requirement',
                'reference_id'   => $existing ? $existing->id : \App\Models\EmployerRequirement::where('user_id', $company->employerNsrp->id)->first()->id,
            ], $staffIds);
        }

        return redirect()->route('company.requirements')
            ->with('success', 'Requirements submitted successfully! PESO staff will review your documents.');
    }

    // ── API: GET STATUS (for Flutter) ──
    public function apiStatus(Request $request)
    {
        $user = $request->user();
        if (!$user || $user->role !== 'company') {
            return response()->json(['status' => 'none']);
        }

        $requirement = EmployerRequirement::where('user_id', $user->id)->first();

        if (!$requirement) {
            return response()->json(['status' => 'none']);
        }

        return response()->json([
            'status'  => $requirement->status,
            'remarks' => $requirement->remarks,
        ]);
    }

    // ── API: SUBMIT REQUIREMENTS (for Flutter) ──
    public function apiStore(Request $request)
    {
        $user = $request->user();
        if (!$user || $user->role !== 'company') {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'business_permit'             => 'required|file|max:5120',
            'sec_dti'                     => 'required|file|max:5120',
            'company_profile'             => 'required|file|max:5120',
            'nsrp_establishment_form'     => 'required|file|max:5120',
            'no_pending_case_certificate' => 'required|file|max:5120',
            'vacancy_posting'             => 'required|file|max:5120',
        ]);

        $existing = EmployerRequirement::where('user_id', $user->id)->first();

        $data = [
            'user_id' => $user->id,
            'status'  => 'pending',
            'remarks' => null,
        ];

        $fields = [
            'business_permit',
            'sec_dti',
            'company_profile',
            'no_pending_case_certificate',
            'vacancy_posting',
        ];

        foreach ($fields as $field) {
            if ($request->hasFile($field)) {
                if ($existing && $existing->$field) {
                    Storage::disk('public')->delete($existing->$field);
                }
                $path = $request->file($field)->store('employer_requirements/' . $user->id, 'public');
                $data[$field] = $path;
            }
        }

        if ($existing) {
            $existing->update($data);
            $requirement = $existing;
        } else {
            $requirement = EmployerRequirement::create($data);
        }

        return response()->json([
            'message'     => 'Requirements submitted successfully.',
            'requirement' => $requirement,
        ]);
    }
}