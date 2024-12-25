<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Mise à jour de la table prospects
        Schema::table('prospects', function (Blueprint $table) {
            if (! Schema::hasColumn('prospects', 'user_id')) {
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            }
        });

        // Mise à jour de la table clients
        Schema::table('clients', function (Blueprint $table) {
            if (! Schema::hasColumn('clients', 'user_id')) {
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            }
        });

        // Mise à jour de la table activities
        Schema::table('activities', function (Blueprint $table) {
            if (! Schema::hasColumn('activities', 'user_id')) {
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            }
        });

        // Mise à jour des données existantes avec des valeurs valides
        DB::statement("UPDATE prospects SET status = 'nouveau' WHERE status NOT IN ('nouveau', 'en_cours', 'qualifie', 'converti', 'annule')");

        // Modification de la colonne status dans la table clients
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->enum('status', ['en_attente', 'actif', 'inactif', 'suspendu'])->default('en_attente')->after('emergency_contact');
        });

        // Mise à jour de payment_status
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('payment_status');
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->enum('payment_status', ['en_attente', 'paye', 'retard', 'annule'])->default('en_attente')->after('total_amount');
        });

        DB::statement("UPDATE activities SET status = 'planifie' WHERE status NOT IN ('planifie', 'en_cours', 'termine', 'annule')");

        DB::statement("UPDATE activities SET type = CASE 
            WHEN type NOT IN ('appel', 'reunion', 'email', 'autre') THEN 'autre'
            ELSE type 
        END");

        // Maintenant, on peut changer les colonnes en ENUM
        Schema::table('prospects', function (Blueprint $table) {
            $table->enum('status', [
                'nouveau',
                'en_cours',
                'qualifie',
                'converti',
                'annule',
            ])->default('nouveau')->change();
        });

        Schema::table('activities', function (Blueprint $table) {
            $table->enum('status', [
                'planifie',
                'en_cours',
                'termine',
                'annule',
            ])->default('planifie')->change();

            $table->enum('type', [
                'appel',
                'reunion',
                'email',
                'autre',
            ])->default('autre')->change();
        });
    }

    public function down()
    {
        Schema::table('prospects', function (Blueprint $table) {
            if (Schema::hasColumn('prospects', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
            $table->string('status')->change();
        });

        Schema::table('clients', function (Blueprint $table) {
            if (Schema::hasColumn('clients', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
            $table->string('status')->change();
            $table->string('payment_status')->change();
        });

        Schema::table('activities', function (Blueprint $table) {
            if (Schema::hasColumn('activities', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
            $table->string('status')->change();
            $table->string('type')->change();
        });
    }
};
