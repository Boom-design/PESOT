<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ── Duha ka butang nga wala pa makontrol sa employer (PESO interview
// ── 2026-08-13, ug pilot testing sa parehas nga adlaw).
// ──
// ── external_hires — ang gi-hire nga wala miagi sa PESO. Pananglit, nag-post
// ── ug 5 ka Welder ang employer, dayon gi-hire niya ang iyang ig-agaw nga wala
// ── gyud mi-apply sa system. Upat na lang ang tinuod nga bakante, apan ang
// ── tanan nga slot count gikan sa `job_matching` nga rows — mao nga walay
// ── paagi nga mahibaw-an sa system. Ang numero ra ang gitipigan, walay
// ── pangalan: desisyon sa opisina, kay self-reported man kini.
// ──
// ── DILI ni maihap nga PESO placement. Ang report nga isumite sa Mayor's
// ── Office ug DOLE nag-ihap ug `Application` rows ra — kung apilon kini,
// ── mataas ang numero sa PESO nga dili tinuod. Bulag siya nga linya.
// ──
// ── manually_closed_at — "Kung ideklara sa kompanya nga filled na ang
// ── position, dili na usab kini i-post." Timestamp, dili boolean: ang
// ── `status = 'closed'` naa nay laing kahulogan (Job Fair nga posting nga
// ── naghulat ug event), mao nga kinahanglan ug bulag nga column aron
// ── dili magsagol ang duha — ug matipigan pud kanus-a kini nahitabo. ──
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_qualifications', function (Blueprint $table) {
            $table->unsignedInteger('external_hires')->default(0)->after('slots');
            $table->timestamp('manually_closed_at')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('job_qualifications', function (Blueprint $table) {
            $table->dropColumn(['external_hires', 'manually_closed_at']);
        });
    }
};
