<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            // Chemin (sur le disque "public") d'un fichier joint à la notification,
            // par exemple un bulletin de paie PDF.
            $table->string('fichier')->nullable()->after('lu');
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn('fichier');
        });
    }
};