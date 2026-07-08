<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('candidats', function (Blueprint $table) {
            $table->decimal('salaire_propose', 10, 2)->nullable()->after('affectation');
            $table->string('responsable_nom')->nullable()->after('salaire_propose');
            $table->date('date_affectation')->nullable()->after('responsable_nom');
        });
    }

    public function down(): void
    {
        Schema::table('candidats', function (Blueprint $table) {
            $table->dropColumn(['salaire_propose', 'responsable_nom', 'date_affectation']);
        });
    }
};