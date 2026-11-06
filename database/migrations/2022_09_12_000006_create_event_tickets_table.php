<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventTicketsTable extends Migration
{
    public function up()
    {
        Schema::create('event_tickets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('ticket_type');
            $table->decimal('price', 15, 2);
            $table->datetime('from')->nullable();
            $table->datetime('to')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
}