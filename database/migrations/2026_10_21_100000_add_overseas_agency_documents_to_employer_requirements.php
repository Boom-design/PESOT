<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The papers an overseas recruitment agency brings to PESO.
 *
 * PESO SRA, 2026-10-21: an agency is not accredited on the same papers as a
 * local establishment, and the office hands them their own slip —
 * "REQUIREMENTS FOR ACCREDITATION/PARTNERSHIP WITH PESO". Until now every
 * employer was asked for the local five, so an agency uploaded a SEC/DTI and
 * a Vacancy Posting that nobody at the SRA desk ever wanted to read, and was
 * never asked for the licence renewal or the DMW job-order balance that
 * actually decide whether they may operate.
 *
 * The slip lists eight. Two are not stored here: PhilJobNet registration, which
 * the office dropped, and the filled-up NSRP form, which this system already
 * collects at registration. The remaining six are the agency's folder, and
 * the business permit is the one paper both markets bring, so it is reused.
 *
 * Only three carry an expiry, because only three are issued for a period:
 * the business permit (already here), the certificate of renewal of licence,
 * and the authority to operate a branch office. An agency profile, a DMW job
 * order balance and a branch organisational chart have no date printed on
 * them, and asking for one would make the employer invent it.
 *
 * Nothing is removed and no existing column is touched. A local employer
 * leaves these null, the same way an agency leaves the local ones null.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employer_requirements', function (Blueprint $table) {
            $table->string('agency_profile')->nullable()->after('vacancy_posting_expires_at');

            $table->string('cert_of_renewal_license')->nullable()->after('agency_profile');
            $table->date('cert_of_renewal_license_expires_at')->nullable()->after('cert_of_renewal_license');

            $table->string('jo_balance_dmw')->nullable()->after('cert_of_renewal_license_expires_at');
            $table->string('branch_org_chart')->nullable()->after('jo_balance_dmw');

            $table->string('authority_to_operate')->nullable()->after('branch_org_chart');
            $table->date('authority_to_operate_expires_at')->nullable()->after('authority_to_operate');

            $table->timestamp('cert_of_renewal_license_expiry_notified_at')->nullable()
                ->after('vacancy_posting_expiry_notified_at');
            $table->timestamp('authority_to_operate_expiry_notified_at')->nullable()
                ->after('cert_of_renewal_license_expiry_notified_at');
        });
    }

    public function down(): void
    {
        Schema::table('employer_requirements', function (Blueprint $table) {
            $table->dropColumn([
                'agency_profile',
                'cert_of_renewal_license',
                'cert_of_renewal_license_expires_at',
                'jo_balance_dmw',
                'branch_org_chart',
                'authority_to_operate',
                'authority_to_operate_expires_at',
                'cert_of_renewal_license_expiry_notified_at',
                'authority_to_operate_expiry_notified_at',
            ]);
        });
    }
};
