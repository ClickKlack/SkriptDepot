<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Auslieferungsprotokoll: jeder Abruf von .meta.js oder .user.js wird hier festgehalten.
     */
    public function up(): void
    {
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entitlement_id')->constrained()->restrictOnDelete();
            $table->foreignId('script_version_id')->constrained()->restrictOnDelete();
            $table->string('type', 8);
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};
