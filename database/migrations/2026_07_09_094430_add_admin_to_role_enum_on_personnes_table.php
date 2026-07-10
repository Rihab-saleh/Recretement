<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // La colonne `role` était un ENUM créé sans 'admin'. On la modifie directement en SQL
        // (MySQL ne permet pas de modifier un ENUM avec Schema::table()/->change() facilement
        // sans le package doctrine/dbal, donc on passe par une requête SQL brute).
        DB::statement("ALTER TABLE personnes MODIFY COLUMN role ENUM('manager', 'candidat', 'rh', 'admin') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE personnes MODIFY COLUMN role ENUM('manager', 'candidat', 'rh') NOT NULL");
    }
};