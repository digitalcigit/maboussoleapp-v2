<?php

use App\Models\Client;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        // D'abord, convertir les colonnes enum en string pour pouvoir les modifier
        Schema::table('clients', function (Blueprint $table) {
            $table->string('status')->change();
            $table->string('payment_status')->change();
        });

        // Ensuite, reconvertir en enum avec les bonnes valeurs
        Schema::table('clients', function (Blueprint $table) {
            $table->enum('status', Client::getValidStatuses())->default(Client::STATUS_ACTIVE)->change();
            $table->enum('payment_status', Client::getValidPaymentStatuses())->default(Client::PAYMENT_STATUS_PENDING)->change();
        });
    }

    public function down()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->enum('status', ['actif', 'inactif', 'en_attente', 'archive'])->default('actif')->change();
            $table->enum('payment_status', ['en_attente', 'partiel', 'complete', 'rembourse', 'annule'])->default('en_attente')->change();
        });
    }
};
