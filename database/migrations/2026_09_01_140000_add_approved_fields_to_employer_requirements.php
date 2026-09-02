<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Which documents the desk has actually read.
 *
 * PESO Job Vacancy staff, 2026-09-01: the desk opens a company, reads the
 * business permit, and accepts it — and the company moved to Approved
 * Employers with four documents still unread. One button approved the whole
 * folder, and there was no way to say "this one is fine, I am still reading
 * the rest".
 *
 * So the folder is now accepted a paper at a time, and the final Approve is
 * refused until all five are in this column. The approval itself stays one
 * deliberate press: it announces to the employer, reopens a restricted
 * account and fires job fair invitations, and none of that should happen as a
 * side effect of ticking the fifth checkbox.
 *
 * Mirrors rejected_fields, which has held the other half of this answer since
 * 2026-07-05 — the documents sent back. Now the system holds both halves.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employer_requirements', function (Blueprint $table) {
            $table->json('approved_fields')->nullable()->after('rejected_fields');
        });

        // ── Ang na-approve na kaniadto: ang tibuok folder gidawat sa usa ka
        // ── pindot, mao nga ang tanang papel nga naa didto gidawat. Kung dili
        // ── ni butangan, ang daan nga laray magpakita nga walay usa nga nabasa
        // ── — nga bakak — ug ang bisan unsang ihap nga mag-agad niini masayop.
        $approved = DB::table('employer_requirements')
            ->where('status', 'approved')
            ->get(['employer_requirements_id', 'business_permit', 'sec_dti',
                   'company_profile', 'no_pending_case_certificate', 'vacancy_posting']);

        foreach ($approved as $row) {
            $present = collect([
                'business_permit', 'sec_dti', 'company_profile',
                'no_pending_case_certificate', 'vacancy_posting',
            ])->filter(fn($field) => !empty($row->$field))->values()->all();

            DB::table('employer_requirements')
                ->where('employer_requirements_id', $row->employer_requirements_id)
                ->update(['approved_fields' => json_encode($present)]);
        }
    }

    public function down(): void
    {
        Schema::table('employer_requirements', function (Blueprint $table) {
            $table->dropColumn('approved_fields');
        });
    }
};
