<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Renames the announcements table primary key from the generic "id" to the
 * descriptive "announcements_id". No inbound foreign keys.
 *
 * Note: announcements.reference_id is a loose polymorphic pointer with no
 * foreign key constraint, so it is unaffected.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->renameColumn('id', 'announcements_id');
        });
    }

    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->renameColumn('announcements_id', 'id');
        });
    }
};
