<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Why a text message did not arrive.
 *
 * `sms_status` already records that a send failed; without the reason beside it
 * staff would have to read storage/logs/laravel.log to find out whether the
 * problem was the number, the credits, or the gateway.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->string('sms_error')->nullable()->after('sms_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropColumn('sms_error');
        });
    }
};
