<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE personnes MODIFY COLUMN role ENUM('manager', 'candidat', 'rh', 'admin', 'super_admin') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE personnes MODIFY COLUMN role ENUM('manager', 'candidat', 'rh', 'admin') NOT NULL");
    }
};