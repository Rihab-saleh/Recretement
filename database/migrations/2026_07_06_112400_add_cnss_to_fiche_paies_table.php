<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fiche_paies', function (Blueprint $table) {
            // Retenue de l'État (CNSS) : calculée sur le salaire de base, avant d'obtenir le salaire net.
            $table->double('deductionCnss')->default(0)->after('deductionRetard');
            $table->double('pourcentageCnss')->default(9.18)->after('pourcentageRetard');
        });
    }

    public function down(): void
    {
        Schema::table('fiche_paies', function (Blueprint $table) {
            $table->dropColumn(['deductionCnss', 'pourcentageCnss']);
        });
    }
};