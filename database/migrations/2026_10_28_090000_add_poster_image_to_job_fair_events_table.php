<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ── Ang poster sa job fair.
// ──
// ── PESO CDO, 2026-10-28: ang kada job fair naay poster nga gihimo sa
// ── opisina ug gipagawas sa Facebook. Sa wala pa kini, ang landing page
// ── walay ikapakita sa job fair gawas sa mga bakante nga nagkatag-katag, ug
// ── ang bisita wala masayod nga usa ra diay ka adlaw ang tanan.
// ──
// ── Ang hulagway ra ang gi-upload. Ang petsa, oras ug address wala didto:
// ── gihimo kini sa system gikan sa event mismo, kay ang hulagway dili
// ── mausab kung mabalhin ang petsa, ug ang nahisulat sa hulagway nga sayop
// ── magpabilin nga sayop.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_fair_events', function (Blueprint $table) {
            $table->string('poster_image')->nullable()->after('venue_address');
        });
    }

    public function down(): void
    {
        Schema::table('job_fair_events', function (Blueprint $table) {
            $table->dropColumn('poster_image');
        });
    }
};
