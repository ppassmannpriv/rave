<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRelationshipFieldsToOrdersTable extends Migration
{
    public function up()
    {
        if (Schema::hasColumns('orders', ['user_id', 'payment_id', 'event_ticket_code_id']) === false) {
            Schema::table('orders', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable();
                $table->foreign('user_id', 'user_fk_7304958')->references('id')->on('users');
                $table->unsignedBigInteger('payment_id')->nullable();
                $table->foreign('payment_id', 'payment_fk_7304991')->references('id')->on('payments');
                $table->unsignedBigInteger('event_ticket_code_id')->nullable();
                $table->foreign('event_ticket_code_id', 'event_ticket_code_fk_7304992')->references('id')->on('event_ticket_codes');
            });
        }
    }
}
