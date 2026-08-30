<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ── PESO interview 2026-08-13: "Kung ang HR sa usa ka employer mo-resign...
// ── kinahanglan adunay paagi aron ma-reset ang password ug user account sa
// ── bag-ong authorized HR."
// ──
// ── Ang pag-ilis sa HR nagpasabot nga naay laing tawo nga makakita sa mga
// ── applicant sa maong kompanya. Personal nga datos kana (block 9: "dili pud
// ── nimo basta-basta ma-disclose ang ilang information"), mao nga kada usa ka
// ── handover naay permanenteng rekord: kinsa nga staff, kanus-a, ug kinsa ang
// ── giilisan. Walay password nga gitipigan dinhi — ang staff wala gyud
// ── makakita niini; ang employer ra mismo ang mo-set pinaagi sa reset code. ──
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employer_account_transfers', function (Blueprint $table) {
            $table->id('employer_account_transfers_id');

            $table->foreignId('employer_id')
                ->constrained('employer_nsrp_registrations', 'employer_nsrp_registrations_id')
                ->cascadeOnDelete();

            // Ang staff nga nag-buhat. Kung ma-delete ang staff record, ang
            // rekord magpabilin — mao ra gyud ni ang pulos sa audit row.
            $table->foreignId('performed_by')
                ->nullable()
                ->constrained('staff', 'staff_id')
                ->nullOnDelete();

            $table->string('previous_contact_person')->nullable();
            $table->string('previous_email')->nullable();
            $table->string('new_contact_person');
            $table->string('new_email');
            $table->text('reason')->nullable();

            $table->timestamps();

            $table->index(['employer_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employer_account_transfers');
    }
};
