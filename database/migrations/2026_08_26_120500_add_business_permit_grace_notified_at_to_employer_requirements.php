<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employer_requirements', function (Blueprint $table) {
            // The gate for the renewal reminder sent during the grace months.
            //
            // It cannot share business_permit_expiry_notified_at: that one is
            // spent on the warning sent in the last week of December, and the
            // grace reminder goes out months later. One column per reminder, or
            // the second one never fires.
            $table->timestamp('business_permit_grace_notified_at')
                ->nullable()
                ->after('business_permit_expiry_notified_at');
        });
    }

    public function down(): void
    {
        Schema::table('employer_requirements', function (Blueprint $table) {
            $table->dropColumn('business_permit_grace_notified_at');
        });
    }
};
