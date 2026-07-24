<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employer_nsrp_registrations', function (Blueprint $table) {
            $table->string('company_name')->nullable()->after('user_id');
            $table->string('contact_person')->nullable()->after('company_name');
            $table->string('position_title')->nullable()->after('contact_person');
            $table->string('mobile_number')->nullable()->after('position_title');
        });
    }

    public function down(): void
    {
        Schema::table('employer_nsrp_registrations', function (Blueprint $table) {
            $table->dropColumn(['company_name', 'contact_person', 'position_title', 'mobile_number']);
        });
    }
};