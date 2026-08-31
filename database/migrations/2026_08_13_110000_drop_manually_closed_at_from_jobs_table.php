<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ── Gitangtang ang manually_closed_at. Gidugang kini kaganina para sa bulag
// ── nga "Mark this position as filled" nga buton, apan wala na kadto: ang
// ── posting mo-sira na lang mag-isa pag-abot sa slots, ug ang pag-record sa
// ── hire mao na mismo ang pag-deklara sa employer nga puno na. Duha ka click
// ── para sa usa ka butang — mao nga usa ra ang gibilin.
// ──
// ── Walay datos nga mawala: wala pa gyud kini nasulatan sa produksyon. ──
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_qualifications', function (Blueprint $table) {
            $table->dropColumn('manually_closed_at');
        });
    }

    public function down(): void
    {
        Schema::table('job_qualifications', function (Blueprint $table) {
            $table->timestamp('manually_closed_at')->nullable()->after('status');
        });
    }
};
