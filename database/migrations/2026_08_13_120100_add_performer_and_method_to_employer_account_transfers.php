<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ── Ang `performed_by` nga naa na kay FK sa `staff.staff_id`. Ang Admin walay
// ── staff row, mao nga dili siya matipigan didto — ug ang Admin makahimo na
// ── karon ug recovery isip backup kung wala ang LRA/SRA.
// ──
// ── `performed_by_user_id` — kinsa gyud, bisan staff o admin.
// ── `method` — gi-emailan ba ug code, o gibutangan ug temporary password.
// ──      Importante ni sa audit: ang temp_password nagpasabot nga naay tawo sa
// ──      opisina nga nakahibalo sa password, bisan mubo ra.
// ──
// ── Ang daan nga `performed_by` wala gitangtang: naay mga row nga nagsalig pa
// ── niini, ug ang audit dili angay usbon sa likod. ──
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employer_account_transfers', function (Blueprint $table) {
            $table->foreignId('performed_by_user_id')
                ->nullable()
                ->after('performed_by')
                ->constrained('users', 'users_id')
                ->nullOnDelete();

            $table->string('method')->default('reset_code')->after('performed_by_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('employer_account_transfers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('performed_by_user_id');
            $table->dropColumn('method');
        });
    }
};
