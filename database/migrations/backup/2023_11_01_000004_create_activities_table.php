<?php

use App\Models\Activity;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', Activity::getValidTypes());
            $table->enum('status', Activity::getValidStatuses())->default(Activity::STATUS_PENDING);
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('created_by')->constrained('users');
            $table->morphs('subject');
            $table->foreignId('prospect_id')->nullable()->constrained('prospects')->onDelete('cascade');
            $table->foreignId('client_id')->nullable()->constrained('clients')->onDelete('cascade');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->json('result')->nullable();
            $table->json('attachments')->nullable();
            $table->timestamps();

            // Les index morphs() ont déjà créé ces index
            $table->index('status');
            $table->index('type');
            $table->index('scheduled_at');
            $table->index('completed_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('activities');
    }
};
