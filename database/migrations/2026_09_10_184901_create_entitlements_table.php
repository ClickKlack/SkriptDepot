<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Freischaltungen: eine Zeile pro Nutzer × Skript. Der Token ist das Credential in der URL.
     */
    public function up(): void
    {
        Schema::create('entitlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('script_id')->constrained()->restrictOnDelete();
            $table->string('token', 40)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'script_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entitlements');
    }
};
