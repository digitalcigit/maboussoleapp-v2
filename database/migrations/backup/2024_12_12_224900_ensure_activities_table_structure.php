<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Remove the problematic migration from the migrations table
        DB::table('migrations')
            ->where('migration', '2024_12_12_224500_add_subject_fields_to_activities_table')
            ->delete();

        if (Schema::hasTable('activities')) {
            Schema::table('activities', function (Blueprint $table) {
                // Check and add each column only if it doesn't exist
                if (!Schema::hasColumn('activities', 'title')) {
                    $table->string('title')->after('id')->nullable();
                }

                if (!Schema::hasColumn('activities', 'status')) {
                    $table->enum('status', ['planifié', 'en_cours', 'terminé', 'annulé'])->after('type')->default('planifié');
                }

                // Add relationships if they don't exist
                if (!Schema::hasColumn('activities', 'client_id')) {
                    $table->unsignedBigInteger('client_id')->nullable();
                    $table->foreign('client_id')
                          ->references('id')
                          ->on('clients')
                          ->onDelete('cascade');
                }

                if (!Schema::hasColumn('activities', 'prospect_id')) {
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
        if (Schema::hasTable('activities')) {
            Schema::table('activities', function (Blueprint $table) {
                // Only drop columns if they exist
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
    }
};
