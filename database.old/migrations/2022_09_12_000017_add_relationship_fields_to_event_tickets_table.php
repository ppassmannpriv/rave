<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRelationshipFieldsToEventTicketsTable extends Migration
{
    public function up()
    {
        if (Schema::hasColumns('event_tickets', ['event_id']) === false) {
            Schema::table('event_tickets', function (Blueprint $table) {
                $table->unsignedBigInteger('event_id')->nullable();
                $table->foreign('event_id', 'event_fk_7304879')->references('id')->on('events');
            });
        }
    }
}
