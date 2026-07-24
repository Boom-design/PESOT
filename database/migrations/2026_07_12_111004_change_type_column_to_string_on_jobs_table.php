<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE job_qualifications MODIFY type VARCHAR(50) NOT NULL DEFAULT 'full_time'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE job_qualifications MODIFY type ENUM('full_time','part_time','contractual') NOT NULL DEFAULT 'full_time'");
    }
};