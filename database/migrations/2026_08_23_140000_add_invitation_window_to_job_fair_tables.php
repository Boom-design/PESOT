<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// ── Ang orasan sa imbitasyon, ug ang adlaw nga ipasa sa DOLE.
// ──
// ── PESO Job Fair staff ug project manager, 2026-08-23: usa ka semana ang
// ── employer para motubag sa imbitasyon; kung wala, mangita ang staff ug lain.
// ── Napulo ka adlaw sa dili pa ang fair, ang na-confirm nga roster mao ang
// ── ipasa sa DOLE.
// ──
// ── Ang invited_at kinahanglan nga kaugalingon nga kolum, dili ang created_at:
// ── kung mag-invite ang staff ug employer human sa una nga hugpong, bag-o ang
// ── iyang pito ka adlaw, ug dili na mapakita sa created_at. ──
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_fair_participants', function (Blueprint $table) {
            $table->dateTime('invited_at')->nullable()->after('confirmation_status');
            $table->dateTime('responded_at')->nullable()->after('invited_at');
        });

        // Ang naa nang laray: ang created_at mao ang adlaw nga na-invite sila,
        // ug ang updated_at mao ang adlaw nga mitubag — kung mitubag sila.
        DB::table('job_fair_participants')->update([
            'invited_at' => DB::raw('created_at'),
        ]);
        DB::table('job_fair_participants')
            ->where('confirmation_status', '!=', 'pending')
            ->update(['responded_at' => DB::raw('updated_at')]);

        DB::statement("ALTER TABLE job_fair_participants
            MODIFY confirmation_status
            ENUM('pending','confirmed','declined','expired') NOT NULL DEFAULT 'pending'");

        Schema::table('job_fair_events', function (Blueprint $table) {
            $table->date('dole_cutoff_at')->nullable()->after('overseas_capacity');
        });
    }

    public function down(): void
    {
        // Ibalik sa pending una tangtangon ang enum value, kay kung dili, ang
        // 'expired' nga laray mahimong blangko nga string sa MySQL.
        DB::table('job_fair_participants')
            ->where('confirmation_status', 'expired')
            ->update(['confirmation_status' => 'pending']);

        DB::statement("ALTER TABLE job_fair_participants
            MODIFY confirmation_status
            ENUM('pending','confirmed','declined') NOT NULL DEFAULT 'pending'");

        Schema::table('job_fair_participants', function (Blueprint $table) {
            $table->dropColumn(['invited_at', 'responded_at']);
        });

        Schema::table('job_fair_events', function (Blueprint $table) {
            $table->dropColumn('dole_cutoff_at');
        });
    }
};
