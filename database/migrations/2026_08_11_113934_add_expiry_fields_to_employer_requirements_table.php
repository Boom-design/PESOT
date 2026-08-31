<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employer_requirements', function (Blueprint $table) {
            $table->date('business_permit_expires_at')->nullable()->after('business_permit');
            $table->date('sec_dti_expires_at')->nullable()->after('sec_dti');
            $table->date('company_profile_expires_at')->nullable()->after('company_profile');
            $table->date('no_pending_case_certificate_expires_at')->nullable()->after('no_pending_case_certificate');
            $table->date('vacancy_posting_expires_at')->nullable()->after('vacancy_posting');

            $table->timestamp('business_permit_expiry_notified_at')->nullable()->after('rejected_fields');
            $table->timestamp('sec_dti_expiry_notified_at')->nullable()->after('business_permit_expiry_notified_at');
            $table->timestamp('company_profile_expiry_notified_at')->nullable()->after('sec_dti_expiry_notified_at');
            $table->timestamp('no_pending_case_certificate_expiry_notified_at')->nullable()->after('company_profile_expiry_notified_at');
            $table->timestamp('vacancy_posting_expiry_notified_at')->nullable()->after('no_pending_case_certificate_expiry_notified_at');
        });
    }

    public function down(): void
    {
        Schema::table('employer_requirements', function (Blueprint $table) {
            $table->dropColumn([
                'business_permit_expires_at',
                'sec_dti_expires_at',
                'company_profile_expires_at',
                'no_pending_case_certificate_expires_at',
                'vacancy_posting_expires_at',
                'business_permit_expiry_notified_at',
                'sec_dti_expiry_notified_at',
                'company_profile_expiry_notified_at',
                'no_pending_case_certificate_expiry_notified_at',
                'vacancy_posting_expiry_notified_at',
            ]);
        });
    }
};
