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
        Schema::table('order_items', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('event_ticket_codes', function (Blueprint $table) {
            $table->unsignedBigInteger('order_item_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('event_ticket_codes', function (Blueprint $table) {
            $table->dropColumn('order_item_id');
        });
    }
};
