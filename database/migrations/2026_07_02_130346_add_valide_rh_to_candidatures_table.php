<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('candidatures', function (Blueprint $table) {
            $table->boolean('valide_rh')->default(false)->after('statut');
            $table->timestamp('date_validation_rh')->nullable()->after('valide_rh');
        });
    }

    public function down(): void
    {
        Schema::table('candidatures', function (Blueprint $table) {
            $table->dropColumn(['valide_rh', 'date_validation_rh']);
        });
    }
};