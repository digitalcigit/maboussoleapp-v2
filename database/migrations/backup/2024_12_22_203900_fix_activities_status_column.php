<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('activities', function (Blueprint $table) {
            // Modifier la colonne status pour accepter les valeurs correctes
            $table->string('status')->change();
        });
    }

    public function down()
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->string('status')->change();
        });
    }
};
