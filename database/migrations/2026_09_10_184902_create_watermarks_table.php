<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Wasserzeichen: hält fest, welcher Build-Hash an welche Freischaltung für welche Version ging.
     */
    public function up(): void
    {
        Schema::create('watermarks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entitlement_id')->constrained()->restrictOnDelete();
            $table->foreignId('script_version_id')->constrained()->restrictOnDelete();
            // 8 Hex-Zeichen, deterministisch aus Seed, Skript und Version berechnet.
            $table->string('build_hash', 8)->index();
            $table->timestamp('first_delivered_at');

            $table->unique(['entitlement_id', 'script_version_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('watermarks');
    }
};
