<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Days the PESO office itself is occupied — staff meetings, trainings,
     * office activities, closures.
     *
     * Only the admin writes here. Every staff calendar reads it, and an
     * in-house interview or job fair cannot be booked on a day that appears
     * in this table: the staff who would run it are in the meeting.
     */
    public function up(): void
    {
        Schema::create('office_calendar_events', function (Blueprint $table) {
            $table->id('office_calendar_events_id');
            $table->string('title');

            // meeting | training | activity | closure | other — label and icon
            // only. Every type blocks scheduling; the office is occupied
            // whatever the reason.
            $table->string('type')->default('meeting');

            // A one-day entry leaves end_date null. A multi-day training fills
            // it, and every day from start to end inclusive is blocked.
            $table->date('start_date');
            $table->date('end_date')->nullable();

            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();

            $table->string('location')->nullable();
            $table->text('notes')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index('start_date');
            $table->foreign('created_by')->references('users_id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('office_calendar_events');
    }
};
