<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pointages', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->time('heureEntree');
            $table->integer('retardMinutes')->default(0);
            $table->double('nbHeures')->nullable();
            $table->string('statut');
            $table->foreignId('personne_id')->constrained('personnes')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pointages');
    }
};