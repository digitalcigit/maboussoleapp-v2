<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('activities', function (Blueprint $table) {
            if (! Schema::hasColumn('activities', 'subject_id')) {
                $table->unsignedBigInteger('subject_id')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('activities', function (Blueprint $table) {
            if (Schema::hasColumn('activities', 'subject_id')) {
                $table->dropColumn('subject_id');
            }
        });
    }
};
