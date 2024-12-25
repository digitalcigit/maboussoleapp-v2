<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('activities')) {
            // First, check if we need to modify the type column
            if (Schema::hasColumn('activities', 'type')) {
                Schema::table('activities', function (Blueprint $table) {
                    $table->string('type')->change();
                });
            }

            // Then add any missing columns
            Schema::table('activities', function (Blueprint $table) {
                if (! Schema::hasColumn('activities', 'title')) {
                    $table->string('title')->nullable()->after('id');
                }

                if (! Schema::hasColumn('activities', 'status')) {
                    $table->enum('status', ['planned', 'in_progress', 'completed', 'cancelled'])
                          ->default('planned')
                          ->after('type');
                }

                // Add polymorphic relationship if it doesn't exist
                if (! Schema::hasColumn('activities', 'subject_type')) {
                    $table->morphs('subject');
                }

                // Add relationships if they don't exist
                if (! Schema::hasColumn('activities', 'client_id')) {
                    $table->unsignedBigInteger('client_id')->nullable();
                    $table->foreign('client_id')
                          ->references('id')
                          ->on('clients')
                          ->onDelete('cascade');
                }

                if (! Schema::hasColumn('activities', 'prospect_id')) {
                    $table->unsignedBigInteger('prospect_id')->nullable();
                    $table->foreign('prospect_id')
                          ->references('id')
                          ->on('prospects')
                          ->onDelete('cascade');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            // Only drop columns that we added
            $columns = ['title', 'status', 'client_id', 'prospect_id'];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('activities', $column)) {
                    if (in_array($column, ['client_id', 'prospect_id'])) {
                        $table->dropForeign([$column]);
                    }
                    $table->dropColumn($column);
                }
            }
        });
    }
};
