<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('announcement_jobseeker');
        Schema::dropIfExists('announcement_employer');
        Schema::dropIfExists('announcement_staff');

        Schema::table('announcements', function (Blueprint $table) {
            $table->foreignId('jobseeker_id')->nullable()->after('id')->constrained('jobseeker_registrations')->onDelete('cascade');
            $table->foreignId('employer_id')->nullable()->after('jobseeker_id')->constrained('employer_nsrp_registrations')->onDelete('cascade');
            $table->foreignId('staff_id')->nullable()->after('employer_id')->constrained('staff')->onDelete('cascade');
            $table->boolean('is_read')->default(false)->after('message');
        });
    }

    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropForeign(['jobseeker_id']);
            $table->dropForeign(['employer_id']);
            $table->dropForeign(['staff_id']);
            $table->dropColumn(['jobseeker_id', 'employer_id', 'staff_id', 'is_read']);
        });
    }
};