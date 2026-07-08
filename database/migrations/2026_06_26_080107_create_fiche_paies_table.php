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
    Schema::create('fiche_paies', function (Blueprint $table) {
        $table->id();
        $table->double('salaireBase');
        $table->integer('joursConge');
        $table->double('salaireNet');
        $table->string('fichierPDF')->nullable();
        $table->foreignId('personne_id')->constrained('personnes')->onDelete('cascade');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fiche_paies');
    }
};
