<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Renames the inhouse_schedules table primary key from the generic "id" to the
 * descriptive "inhouse_schedules_id".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inhouse_participants', function (Blueprint $table) {
            $table->dropForeign('inhouse_participants_inhouse_schedule_id_foreign');
        });

        Schema::table('inhouse_schedules', function (Blueprint $table) {
            $table->renameColumn('id', 'inhouse_schedules_id');
        });

        Schema::table('inhouse_participants', function (Blueprint $table) {
            $table->foreign('inhouse_schedule_id')->references('inhouse_schedules_id')->on('inhouse_schedules')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('inhouse_participants', function (Blueprint $table) {
            $table->dropForeign('inhouse_participants_inhouse_schedule_id_foreign');
        });

        Schema::table('inhouse_schedules', function (Blueprint $table) {
            $table->renameColumn('inhouse_schedules_id', 'id');
        });

        Schema::table('inhouse_participants', function (Blueprint $table) {
            $table->foreign('inhouse_schedule_id')->references('id')->on('inhouse_schedules')->onDelete('cascade');
        });
    }
};
