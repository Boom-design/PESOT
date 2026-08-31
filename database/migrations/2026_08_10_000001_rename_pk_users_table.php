<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Renames the users table primary key from the generic "id" to the
 * descriptive "users_id" (table name + _id).
 *
 * MySQL cannot rename a column that is referenced by a foreign key, so the
 * three inbound constraints are dropped first and recreated afterwards.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jobseeker_registrations', function (Blueprint $table) {
            $table->dropForeign('jobseeker_registrations_user_id_foreign');
        });
        Schema::table('employer_nsrp_registrations', function (Blueprint $table) {
            $table->dropForeign('employer_nsrp_registrations_user_id_foreign');
        });
        Schema::table('staff', function (Blueprint $table) {
            $table->dropForeign('staff_user_id_foreign');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('id', 'users_id');
        });

        Schema::table('jobseeker_registrations', function (Blueprint $table) {
            $table->foreign('user_id')->references('users_id')->on('users')->onDelete('cascade');
        });
        Schema::table('employer_nsrp_registrations', function (Blueprint $table) {
            $table->foreign('user_id')->references('users_id')->on('users')->onDelete('cascade');
        });
        Schema::table('staff', function (Blueprint $table) {
            $table->foreign('user_id')->references('users_id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('jobseeker_registrations', function (Blueprint $table) {
            $table->dropForeign('jobseeker_registrations_user_id_foreign');
        });
        Schema::table('employer_nsrp_registrations', function (Blueprint $table) {
            $table->dropForeign('employer_nsrp_registrations_user_id_foreign');
        });
        Schema::table('staff', function (Blueprint $table) {
            $table->dropForeign('staff_user_id_foreign');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('users_id', 'id');
        });

        Schema::table('jobseeker_registrations', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
        Schema::table('employer_nsrp_registrations', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
        Schema::table('staff', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
