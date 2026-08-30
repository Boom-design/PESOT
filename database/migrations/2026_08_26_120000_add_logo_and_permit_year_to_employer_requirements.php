<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employer_requirements', function (Blueprint $table) {
            // The company logo. It sits with the requirements because that is
            // the one page where an employer hands PESO a file, but it is not a
            // document that lapses — a logo has no expiry date and never asks
            // to be renewed.
            $table->string('company_logo')->nullable()->after('reviewed_by');

            // Which calendar year the CDO business permit covers.
            //
            // The permit is issued per year, so its own expiry date is always
            // the 31st of December. What decides access is not that date but
            // the grace the office allows into the following year, and that
            // can only be worked out from the year the permit is for.
            $table->unsignedSmallInteger('business_permit_year')->nullable()->after('business_permit');
        });
    }

    public function down(): void
    {
        Schema::table('employer_requirements', function (Blueprint $table) {
            $table->dropColumn(['company_logo', 'business_permit_year']);
        });
    }
};
