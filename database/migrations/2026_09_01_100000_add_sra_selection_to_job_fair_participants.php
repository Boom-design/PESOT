<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * SRA picks who actually goes to the fair, after the agency says yes.
 *
 * PESO SRA, 2026-09-01: inviting an overseas agency and letting it into the
 * fair are two decisions, not one. SRA sends the invitation after asking the
 * PESO head; the agency answers; and then SRA looks at who answered and picks
 * the ones the fair can hold. Before this, a "yes" from the agency put it in
 * the fair on its own, and the desk that did the choosing had no say in the
 * result of its own choice.
 *
 * The word "pending" was already taken — it means "the employer has not
 * answered yet", and the lapse sweep, the reminder command and every count in
 * the office read it that way. So the waiting room gets its own word:
 *
 *   pending      the employer has not answered
 *   accepted     the employer said yes; SRA has not decided        ← new
 *   confirmed    in the fair — this is what the roster and the jobseeker
 *                list have always meant, and it does not change
 *   not_selected the employer said yes, the office did not take it   ← new
 *   declined     the employer itself said no
 *   expired      the week ran out with no answer
 *
 * not_selected is kept apart from declined on purpose. Reading "declined" for
 * an agency that actually asked to come would put the refusal on the wrong
 * side of the table, and the DOLE report is built out of these words.
 *
 * Local employers are untouched: nobody chooses them, they are invited by the
 * event's own rules, so their yes stays a yes.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE job_fair_participants
            MODIFY confirmation_status
            ENUM('pending','accepted','confirmed','not_selected','declined','expired')
            NOT NULL DEFAULT 'pending'");

        Schema::table('job_fair_participants', function (Blueprint $table) {
            // Who let this agency in, or turned it away, and on what reasoning.
            // Null for every local employer and for every row written before
            // the selection step existed — nobody decided, so nobody is named.
            $table->unsignedBigInteger('sra_decided_by')->nullable()->after('permission_note');
            $table->dateTime('sra_decided_at')->nullable()->after('sra_decided_by');
            $table->string('sra_decision_note', 255)->nullable()->after('sra_decided_at');
        });
    }

    public function down(): void
    {
        // Ibalik sa daan nga pulong una tangtangon ang enum value, kay kung
        // dili, ang laray mahimong blangko nga string sa MySQL.
        DB::table('job_fair_participants')
            ->where('confirmation_status', 'accepted')
            ->update(['confirmation_status' => 'confirmed']);

        DB::table('job_fair_participants')
            ->where('confirmation_status', 'not_selected')
            ->update(['confirmation_status' => 'declined']);

        DB::statement("ALTER TABLE job_fair_participants
            MODIFY confirmation_status
            ENUM('pending','confirmed','declined','expired')
            NOT NULL DEFAULT 'pending'");

        Schema::table('job_fair_participants', function (Blueprint $table) {
            $table->dropColumn(['sra_decided_by', 'sra_decided_at', 'sra_decision_note']);
        });
    }
};
