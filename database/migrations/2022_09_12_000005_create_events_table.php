<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('events') === false) {
            Schema::create('events', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name');
                $table->datetime('start');
                $table->datetime('end');
                $table->string('location');
                $table->longText('description')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }
}
