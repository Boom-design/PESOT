<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE applications MODIFY status ENUM('pending', 'reviewed', 'qualified', 'waiting', 'hired', 'rejected') DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE applications MODIFY status ENUM('pending', 'accepted', 'rejected') DEFAULT 'pending'");
    }
};