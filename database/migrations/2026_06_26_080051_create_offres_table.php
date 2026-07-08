<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offres', function (Blueprint $table) {
            $table->id();
            $table->string('intitule');
            $table->string('departement')->nullable();
            $table->text('description');
            $table->double('salaire');
            $table->string('statut');
            $table->date('datePublication')->nullable();
            $table->date('date_fin')->nullable();
            $table->foreignId('personne_id')->constrained('personnes')->onDelete('cascade');
            $table->timestamps();
        });
    }

     public function down(): void
    {
        Schema::table('offres', function (Blueprint $table) {
            $table->dropColumn('date_fin');
        });
    }
};