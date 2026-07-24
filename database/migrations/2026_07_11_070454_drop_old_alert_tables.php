<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('jobseeker_alerts');
        Schema::dropIfExists('employer_alerts');
        Schema::dropIfExists('staff_alerts');
    }

    public function down(): void
    {
        Schema::create('jobseeker_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jobseeker_id')->constrained('jobseeker_registrations')->onDelete('cascade');
            $table->string('type');
            $table->string('title');
            $table->text('message');
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->boolean('is_read')->default(false);
            $table->string('sms_status')->default('not_applicable');
            $table->timestamp('sms_sent_at')->nullable();
            $table->timestamps();
        });

        Schema::create('employer_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employer_id')->constrained('employer_nsrp_registrations')->onDelete('cascade');
            $table->string('type');
            $table->string('title');
            $table->text('message');
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->boolean('is_read')->default(false);
            $table->string('sms_status')->default('not_applicable');
            $table->timestamp('sms_sent_at')->nullable();
            $table->timestamps();
        });

        Schema::create('staff_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('staff')->onDelete('cascade');
            $table->string('type');
            $table->string('title');
            $table->text('message');
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->boolean('is_read')->default(false);
            $table->string('sms_status')->default('not_applicable');
            $table->timestamp('sms_sent_at')->nullable();
            $table->timestamps();
        });
    }
};