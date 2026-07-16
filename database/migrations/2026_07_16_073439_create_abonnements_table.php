<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Table des abonnements : un candidat "suit" une entreprise pour être
     * notifié dès qu'elle publie une nouvelle offre d'emploi.
     */
    public function up(): void
    {
        Schema::create('abonnements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personne_id')->constrained('personnes')->cascadeOnDelete();
            $table->foreignId('entreprise_id')->constrained('entreprises')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['personne_id', 'entreprise_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('abonnements');
    }
};