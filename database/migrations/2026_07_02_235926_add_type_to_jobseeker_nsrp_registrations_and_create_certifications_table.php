<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Add 'type' column sa NSRP registrations ──
        Schema::table('jobseeker_nsrp_registrations', function (Blueprint $table) {
            $table->enum('type', ['local', 'overseas', 'both'])->default('local')->after('employment_type');
        });

        // ── Bag-ong Certifications table ──
        Schema::create('jobseeker_certifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jobseeker_nsrp_registration_id', 'jc_nsrp_fk')
                  ->constrained('jobseeker_nsrp_registrations')
                  ->onDelete('cascade');
            $table->enum('category', ['eligibility', 'license']);
            $table->string('name');
            $table->date('date_taken')->nullable();
            $table->date('valid_until')->nullable();
            $table->string('certificate_file')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jobseeker_certifications');
        Schema::table('jobseeker_nsrp_registrations', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};