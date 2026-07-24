<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->enum('posting_status', ['pending', 'approved', 'rejected'])
                ->default('pending')->after('status');
            $table->string('posting_type', 50)
                ->default('direct')->after('posting_status'); // direct or inhouse
            $table->text('remarks')->nullable()->after('posting_type');
            $table->string('salary')->nullable()->after('remarks');
        });
    }

    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropColumn(['posting_status', 'posting_type', 'remarks', 'salary']);
        });
    }
};