<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * One HR account may hold more than one establishment.
 *
 * PESO IT, 2026-08-26: the same HR officer can be the authorised contact for
 * two companies. They asked for one e-mail to cover both. The e-mail cannot be
 * duplicated — it is how a person signs in, and two rows sharing it makes the
 * login a coin toss — so the account stays one, and the companies under it
 * become many.
 *
 * user_id was unique, which is what pinned an account to a single company.
 * A plain index replaces it: still fast to look up, no longer a limit of one.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Ang plain nga index kinahanglan mo-una: ang foreign key sa user_id
        // nagsalig sa unique nga index, ug dili siya matangtang samtang wala
        // pay lain nga index nga makasapnay niini.
        DB::statement('CREATE INDEX employer_nsrp_registrations_user_id_index ON employer_nsrp_registrations (user_id)');
        DB::statement('ALTER TABLE employer_nsrp_registrations DROP INDEX employer_nsrp_registrations_user_id_unique');
    }

    public function down(): void
    {
        DB::statement('CREATE UNIQUE INDEX employer_nsrp_registrations_user_id_unique ON employer_nsrp_registrations (user_id)');
        DB::statement('ALTER TABLE employer_nsrp_registrations DROP INDEX employer_nsrp_registrations_user_id_index');
    }
};
