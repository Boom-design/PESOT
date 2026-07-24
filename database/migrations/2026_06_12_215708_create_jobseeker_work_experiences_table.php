<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jobseeker_work_experiences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jobseeker_registration_id')
                  ->constrained('jobseeker_registrations')
                  ->onDelete('cascade');
            $table->string('company_name');
            $table->string('position');
            $table->string('industry')->nullable();
            $table->string('date_from')->nullable(); // mm/yyyy format
            $table->string('date_to')->nullable();   // mm/yyyy or 'present'
            $table->boolean('is_current')->default(false);
            $table->string('employment_status')->nullable(); // regular, contractual, etc.
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jobseeker_work_experiences');
    }
};