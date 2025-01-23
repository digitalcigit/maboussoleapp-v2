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
        // Les permissions seront gérées par le seeder DossierPermissionsSeeder
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rien à faire ici car les permissions sont gérées par le seeder
    }
};
