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
        Schema::create('time_schedule_shifts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('time_schedule_id');
            $table->string('name');
            $table->tinyText('description')->nullable();
            $table->dateTime('start');
            $table->dateTime('end');
            $table->boolean('crew_only');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('time_schedule_shifts');
    }
};
