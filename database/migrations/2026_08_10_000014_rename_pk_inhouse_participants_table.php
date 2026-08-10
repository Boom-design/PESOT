<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Renames the inhouse_participants table primary key from the generic "id" to
 * the descriptive "inhouse_participants_id". No inbound foreign keys.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inhouse_participants', function (Blueprint $table) {
            $table->renameColumn('id', 'inhouse_participants_id');
        });
    }

    public function down(): void
    {
        Schema::table('inhouse_participants', function (Blueprint $table) {
            $table->renameColumn('inhouse_participants_id', 'id');
        });
    }
};
