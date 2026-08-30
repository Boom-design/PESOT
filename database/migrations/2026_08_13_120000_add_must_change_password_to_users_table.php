<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ── Gi-set kini kung ang PESO staff o ang Admin nagbutang ug temporary
// ── password para sa usa ka employer nga nawad-an ug access (ni-hawa ang HR,
// ── personal nga Gmail ang gigamit sa pag-register).
// ──
// ── Ang tumong: mubo ra kaayo ang panahon nga kahibalo ang opisina sa
// ── password. Pag-login sa employer, walay laing page nga maabot gawas sa
// ── pag-ilis sa password. ──
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('must_change_password')->default(false)->after('password');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('must_change_password');
        });
    }
};
