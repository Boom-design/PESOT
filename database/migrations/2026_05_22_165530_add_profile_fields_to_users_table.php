<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Jobseeker fields
            $table->string('last_name')->nullable()->after('phone');
            $table->string('first_name')->nullable()->after('last_name');
            $table->string('middle_name')->nullable()->after('first_name');
            $table->date('date_of_birth')->nullable()->after('middle_name');
            $table->string('gender')->nullable()->after('date_of_birth');

            // Company fields
            $table->string('company_name')->nullable()->after('gender');
            $table->string('contact_person')->nullable()->after('company_name');
            $table->string('position_title')->nullable()->after('contact_person');
            $table->string('mobile_number')->nullable()->after('position_title');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'last_name', 'first_name', 'middle_name',
                'date_of_birth', 'gender',
                'company_name', 'contact_person',
                'position_title', 'mobile_number',
            ]);
        });
    }
};