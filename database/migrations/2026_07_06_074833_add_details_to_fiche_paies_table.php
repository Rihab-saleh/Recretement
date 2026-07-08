<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fiche_paies', function (Blueprint $table) {
            $table->unsignedTinyInteger('mois')->nullable()->after('personne_id');
            $table->unsignedSmallInteger('annee')->nullable()->after('mois');

            $table->unsignedSmallInteger('joursOuvres')->default(0)->after('joursConge');
            $table->unsignedSmallInteger('joursTravailles')->default(0)->after('joursOuvres');
            $table->unsignedSmallInteger('joursAbsence')->default(0)->after('joursTravailles');
            $table->unsignedSmallInteger('joursRetard')->default(0)->after('joursAbsence');
            $table->unsignedInteger('totalRetardMinutes')->default(0)->after('joursRetard');

            $table->double('deductionAbsence')->default(0)->after('salaireNet');
            $table->double('deductionConge')->default(0)->after('deductionAbsence');
            $table->double('deductionRetard')->default(0)->after('deductionConge');

            $table->double('pourcentageAbsence')->default(100)->after('deductionRetard');
            $table->double('pourcentageConge')->default(0)->after('pourcentageAbsence');
            $table->double('pourcentageRetard')->default(100)->after('pourcentageConge');
        });
    }

    public function down(): void
    {
        Schema::table('fiche_paies', function (Blueprint $table) {
            $table->dropColumn([
                'mois', 'annee', 'joursOuvres', 'joursTravailles', 'joursAbsence',
                'joursRetard', 'totalRetardMinutes', 'deductionAbsence', 'deductionConge',
                'deductionRetard', 'pourcentageAbsence', 'pourcentageConge', 'pourcentageRetard',
            ]);
        });
    }
};