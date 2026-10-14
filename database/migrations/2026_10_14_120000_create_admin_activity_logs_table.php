<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * What each admin did, so the other two can see it.
 *
 * PESO, 2026-10-14: there are three administrators in the office and they have
 * been sharing one login. Everything any of them did was recorded as the work
 * of "PESO Admin", so a staff account switched off on Tuesday could not be
 * traced to the person who switched it off — not to blame anyone, but because
 * the other two need to know it happened and who to ask about it.
 *
 * Three accounts now, and this is the page they share instead.
 *
 * The actor's NAME is copied in beside their id. An admin may rename
 * themselves, and a log that reads back the current name would rewrite its own
 * history the moment they did; the name here is the one they were using when
 * they acted. The id stays for linking, and survives as a dangling reference
 * if an account is ever removed — the record of what happened must outlive the
 * account that did it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_activity_logs', function (Blueprint $table) {
            $table->id('admin_activity_logs_id');

            $table->unsignedBigInteger('actor_user_id')->nullable();
            $table->string('actor_name');

            // A short machine key — 'staff_status_changed', 'calendar_added' —
            // so the page can group and icon them without matching on prose.
            $table->string('action', 64);

            // The sentence the other admins read. Written when it happens,
            // never recomputed: it describes a moment, and the rows it names
            // can change or disappear afterwards.
            $table->string('summary', 500);

            // What it was done to, for a link back. Nullable because some
            // actions are about the admin's own account and have no subject.
            $table->string('subject_type', 40)->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();

            $table->timestamps();

            $table->index('created_at');
            $table->index(['subject_type', 'subject_id']);

            $table->foreign('actor_user_id')
                  ->references('users_id')->on('users')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_activity_logs');
    }
};
