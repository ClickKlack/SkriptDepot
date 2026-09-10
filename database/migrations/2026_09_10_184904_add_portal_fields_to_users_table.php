<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Portal-Felder am Nutzer: geheimer Seed für das Wasserzeichen, Admin-Kennzeichen, UI-Sprache.
     *
     * Der Seed wird erst nullable angelegt, für bestehende Zeilen befüllt und danach auf NOT NULL gezogen,
     * damit die Migration auch auf einer bereits befüllten Tabelle sauber durchläuft.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('seed', 64)->nullable()->after('password');
            $table->boolean('is_admin')->default(false)->after('seed');
            $table->string('locale', 5)->default('de')->after('is_admin');
        });

        DB::table('users')->whereNull('seed')->pluck('id')->each(function (int $userId): void {
            DB::table('users')->where('id', $userId)->update(['seed' => bin2hex(random_bytes(32))]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('seed', 64)->nullable(false)->change();
            $table->unique('seed');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['seed']);
            $table->dropColumn(['seed', 'is_admin', 'locale']);
        });
    }
};
