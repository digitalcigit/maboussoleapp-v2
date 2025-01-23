<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('prospects', function (Blueprint $table) {
            // Sauvegarde temporaire des anciennes données
            $table->json('old_documents')->nullable()->after('documents');
            
            // Réinitialisation de la colonne documents
            $table->json('documents')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('prospects', function (Blueprint $table) {
            $table->dropColumn('old_documents');
            $table->json('documents')->nullable()->change();
        });
    }
};
