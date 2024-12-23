<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('token')->nullable();
            $table->string('payer_id')->nullable();
            $table->string('payer_email')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumns('transactions', ['token', 'payer_id', 'payer_email'])) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropColumn('token');
                $table->dropColumn('payer_id');
                $table->dropColumn('payer_email');
            });
        }
    }
};
