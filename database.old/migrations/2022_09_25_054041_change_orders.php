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
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('price', 15, 2);
            $table->unsignedBigInteger('transaction_id')->nullable();

            $table->dropForeign('event_ticket_code_fk_7304992');
            $table->dropColumn('event_ticket_code_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('price');
            $table->dropColumn('transaction_id');

            $table->unsignedBigInteger('event_ticket_code_id')->nullable();
            $table->foreign('event_ticket_code_id', 'event_ticket_code_fk_7304992')->references('id')->on('event_ticket_codes');
        });
    }
};
