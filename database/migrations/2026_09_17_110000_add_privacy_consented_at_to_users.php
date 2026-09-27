<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * When the person agreed to the Data Privacy Notice (Republic Act No. 10173)
 * at registration. PESO CDO, 2026-09-17: registration now asks for consent
 * first, the same as PESO's own applicant form.
 *
 * Accounts made before this, and accounts staff make for walk-ins, stay null.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('privacy_consented_at')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('privacy_consented_at');
        });
    }
};
