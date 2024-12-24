<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->timestamp('contract_start_date')->nullable()->after('commercial_code');
            $table->timestamp('contract_end_date')->nullable()->after('contract_start_date');
        });
    }

    public function down()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['contract_start_date', 'contract_end_date']);
        });
    }
};
