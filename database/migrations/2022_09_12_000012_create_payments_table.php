<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('provider');
            $table->decimal('amount', 15, 2);
            $table->string('state');
            $table->string('reference')->unique();
            $table->timestamps();
            $table->softDeletes();
        });
    }
}