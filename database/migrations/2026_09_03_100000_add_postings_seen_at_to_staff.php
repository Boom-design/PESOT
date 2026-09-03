<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * When the Job Fair desk last opened Job Fair Vacancies.
 *
 * PESO Job Fair staff, 2026-09-03: the red count on that sidebar item stayed
 * at 1 after the page had been read, because it counted approved-but-closed
 * fair postings and only "Post All Job Vacancies" cleared them. Opening the
 * page is what the number is asking for, so opening the page is what should
 * clear it — and the number comes back on its own when a newer posting arrives.
 *
 * Nullable: a desk that has never opened the page has seen nothing, which is
 * exactly what the count should say.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->timestamp('postings_seen_at')->nullable()->after('phone');
        });
    }

    public function down(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->dropColumn('postings_seen_at');
        });
    }
};
