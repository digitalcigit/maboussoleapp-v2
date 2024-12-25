<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Client;

require_once __DIR__ . '/../../app/Models/Client.php';

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prospect_id')->constrained()->onDelete('cascade');
            $table->string('client_number')->unique();
            $table->string('passport_number')->nullable();
            $table->date('passport_expiry')->nullable();
            $table->enum('visa_status', [
                Client::VISA_STATUS_NOT_STARTED,
                Client::VISA_STATUS_IN_PROGRESS,
                Client::VISA_STATUS_OBTAINED,
                Client::VISA_STATUS_REJECTED
            ])->default(Client::VISA_STATUS_NOT_STARTED);
            $table->json('travel_preferences')->nullable();
            $table->enum('payment_status', [
                Client::PAYMENT_STATUS_PENDING,
                Client::PAYMENT_STATUS_PARTIAL,
                Client::PAYMENT_STATUS_COMPLETED
            ])->default(Client::PAYMENT_STATUS_PENDING);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->enum('status', [
                Client::STATUS_ACTIVE,
                Client::STATUS_INACTIVE,
                Client::STATUS_PENDING,
                Client::STATUS_ARCHIVED
            ])->default(Client::STATUS_ACTIVE);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
