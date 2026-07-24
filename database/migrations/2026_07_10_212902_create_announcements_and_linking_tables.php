<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('title');
            $table->text('message');
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('sms_status')->default('not_applicable');
            $table->timestamp('sms_sent_at')->nullable();
            $table->timestamps();
        });

        Schema::create('announcement_jobseeker', function (Blueprint $table) {
            $table->id();
            $table->foreignId('announcement_id')->constrained('announcements')->onDelete('cascade');
            $table->foreignId('jobseeker_id')->constrained('jobseeker_registrations')->onDelete('cascade');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });

        Schema::create('announcement_employer', function (Blueprint $table) {
            $table->id();
            $table->foreignId('announcement_id')->constrained('announcements')->onDelete('cascade');
            $table->foreignId('employer_id')->constrained('employer_nsrp_registrations')->onDelete('cascade');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });

        Schema::create('announcement_staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('announcement_id')->constrained('announcements')->onDelete('cascade');
            $table->foreignId('staff_id')->constrained('staff')->onDelete('cascade');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcement_staff');
        Schema::dropIfExists('announcement_employer');
        Schema::dropIfExists('announcement_jobseeker');
        Schema::dropIfExists('announcements');
    }
};