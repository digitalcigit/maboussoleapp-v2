<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Activity;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', [
                Activity::TYPE_CALL,
                Activity::TYPE_EMAIL,
                Activity::TYPE_MEETING,
                Activity::TYPE_NOTE,
                Activity::TYPE_DOCUMENT,
                Activity::TYPE_PAYMENT,
                Activity::TYPE_CONVERSION,
                Activity::TYPE_OTHER
            ])->default(Activity::TYPE_OTHER);
            $table->enum('status', [
                Activity::STATUS_PENDING,
                Activity::STATUS_IN_PROGRESS,
                Activity::STATUS_COMPLETED,
                Activity::STATUS_CANCELLED
            ])->default(Activity::STATUS_PENDING);
            $table->datetime('start_date')->nullable();
            $table->datetime('end_date')->nullable();
            $table->unsignedBigInteger('client_id')->nullable()->index();
            $table->unsignedBigInteger('prospect_id')->nullable()->index();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
