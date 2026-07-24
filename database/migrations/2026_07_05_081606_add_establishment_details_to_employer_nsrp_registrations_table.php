<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employer_nsrp_registrations', function (Blueprint $table) {
            // Section I — Establishment Details
            $table->string('trade_name')->nullable()->after('employer_type');
            $table->string('tin')->nullable()->after('trade_name');
            $table->enum('tin_type', ['main', 'branch'])->nullable()->after('tin');
            $table->enum('total_workforce', ['micro', 'small', 'medium', 'large'])->nullable()->after('tin_type');
            $table->string('line_of_business')->nullable()->after('total_workforce');
            $table->string('est_barangay')->nullable()->after('line_of_business');
            $table->string('est_city_municipality')->nullable()->after('est_barangay');
            $table->string('est_province')->nullable()->after('est_city_municipality');

            // Section II — Establishment Contact Details (extra fields)
            $table->string('contact_title')->nullable()->after('est_province'); // Mr/Ms/Miss/Others
            $table->string('telephone_no')->nullable()->after('contact_title');
            $table->string('fax_no')->nullable()->after('telephone_no');

            // Certification/Authorization
            $table->boolean('certification_agreed')->default(false)->after('fax_no');
            $table->date('certification_date')->nullable()->after('certification_agreed');
        });
    }

    public function down(): void
    {
        Schema::table('employer_nsrp_registrations', function (Blueprint $table) {
            $table->dropColumn([
                'trade_name', 'tin', 'tin_type', 'total_workforce', 'line_of_business',
                'est_barangay', 'est_city_municipality', 'est_province',
                'contact_title', 'telephone_no', 'fax_no',
                'certification_agreed', 'certification_date',
            ]);
        });
    }
};