<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidatures', function (Blueprint $table) {
            $table->id();
            $table->string('statut')->default('en_attente');
            $table->date('datePostulation')->nullable();
            $table->foreignId('personne_id')->constrained('personnes')->onDelete('cascade');
            $table->foreignId('offre_id')->constrained('offres')->onDelete('cascade');
            $table->string('telephone')->nullable();
            $table->string('cv')->nullable();
            $table->text('lettre_motivation')->nullable();
            $table->integer('experience')->nullable();
            $table->string('diplome')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('candidatures', function (Blueprint $table) {
            $table->dropColumn(['telephone', 'cv', 'lettre_motivation', 'experience', 'diplome']);
        });
    }
};