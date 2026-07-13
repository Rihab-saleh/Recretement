<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personnes', function (Blueprint $table) {
            $table->foreignId('entreprise_id')
                ->nullable()
                ->after('departement')
                ->constrained('entreprises')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('personnes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('entreprise_id');
        });
    }
};