<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jobseeker_registrations', function (Blueprint $table) {
            $table->string('perm_house_street')->nullable()->after('province');
            $table->string('perm_barangay')->nullable()->after('perm_house_street');
            $table->string('perm_municipality_city')->nullable()->after('perm_barangay');
            $table->string('perm_province')->nullable()->after('perm_municipality_city');
            $table->boolean('same_as_permanent')->default(false)->after('perm_province');
        });
    }

    public function down(): void
    {
        Schema::table('jobseeker_registrations', function (Blueprint $table) {
            $table->dropColumn([
                'perm_house_street',
                'perm_barangay',
                'perm_municipality_city',
                'perm_province',
                'same_as_permanent',
            ]);
        });
    }
};