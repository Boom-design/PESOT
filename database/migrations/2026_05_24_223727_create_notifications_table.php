<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('type');                          // registration_submitted, user_registered, etc.
            $table->string('title');                         // e.g. "New Registration Form"
            $table->text('message');                         // e.g. "Santizo, Reach submitted a registration form"
            $table->boolean('is_read')->default(false);      // unread by default
            $table->foreignId('user_id')                     // who triggered it
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('cascade');
            $table->string('reference_type')->nullable();    // e.g. "registration", "user"
            $table->unsignedBigInteger('reference_id')->nullable(); // e.g. registration id
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};