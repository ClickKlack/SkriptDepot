<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Skriptversionen: jede Zeile hält den Master-Quelltext einer Version mit Platzhaltern.
     */
    public function up(): void
    {
        Schema::create('script_versions', function (Blueprint $table) {
            $table->id();
            // Versionen bleiben forensisch relevant, deshalb darf ein Skript mit Versionen nicht gelöscht werden.
            $table->foreignId('script_id')->constrained()->restrictOnDelete();
            $table->string('version', 32);
            $table->longText('source');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['script_id', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('script_versions');
    }
};
