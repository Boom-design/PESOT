<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_fair_participants', function (Blueprint $table) {
            // Who put this employer on the list, when the list was not built by
            // the system on its own.
            //
            // Overseas employers are no longer invited automatically: SRA picks
            // them, after asking the PESO head. That permission is given in the
            // office, not in here, so the only honest thing the system can hold
            // is who acted on it and what they were told — which is what these
            // two columns are for. Null means the invitation went out on the
            // event's own rules, with nobody choosing.
            $table->unsignedBigInteger('invited_by')->nullable()->after('invited_at');
            $table->string('permission_note', 255)->nullable()->after('invited_by');
        });
    }

    public function down(): void
    {
        Schema::table('job_fair_participants', function (Blueprint $table) {
            $table->dropColumn(['invited_by', 'permission_note']);
        });
    }
};
