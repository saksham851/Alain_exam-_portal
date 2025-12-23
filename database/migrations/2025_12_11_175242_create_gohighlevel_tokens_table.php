<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('gohighlevel_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('location_id')->nullable(); // GHL Location ID
            $table->text('access_token');
            $table->text('refresh_token');
            $table->string('token_type')->nullable(); // e.g., Bearer
            $table->integer('expires_in')->nullable(); // Seconds until expiration
            $table->string('user_type')->nullable(); // Location or Company
            $table->text('scope')->nullable(); // Changed to text to hold long permission strings
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gohighlevel_tokens');
    }
};
