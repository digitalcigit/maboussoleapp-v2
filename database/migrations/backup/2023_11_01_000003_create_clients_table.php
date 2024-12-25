<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('client_number')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('profession')->nullable();
            $table->string('education_level')->nullable();
            $table->string('current_location')->nullable();
            $table->string('current_field')->nullable();
            $table->string('desired_field')->nullable();
            $table->string('desired_destination')->nullable();
            $table->json('emergency_contact')->nullable();
            $table->enum('status', [
                'actif',
                'inactif',
                'en_pause',
                'termine',
                'annule',
            ])->default('actif');
            $table->enum('payment_status', [
                'en_attente',
                'partiel',
                'complet',
                'rembourse',
            ])->default('en_attente');
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('assigned_to')->nullable()->constrained('users');
            $table->foreignId('prospect_id')->nullable()->constrained('prospects');
            $table->string('commercial_code')->nullable();
            $table->foreignId('partner_id')->nullable()->constrained('users');
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->json('payment_schedule')->nullable();
            $table->json('services')->nullable();
            $table->json('documents')->nullable();
            $table->timestamp('last_payment_at')->nullable();
            $table->timestamp('next_payment_at')->nullable();
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('payment_status');
            $table->index('last_payment_at');
            $table->index('next_payment_at');
            $table->index('start_date');
            $table->index('end_date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('clients');
    }
};
