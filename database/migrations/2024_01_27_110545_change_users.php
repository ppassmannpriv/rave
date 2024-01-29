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
        Schema::table('users', function (Blueprint $table) {
            $table->string('street', 512);
            $table->string('postcode');
            $table->string('city');
            $table->enum('country', [
                'AT',
                'BE',
                'HR',
                'CY',
                'EE',
                'FI',
                'FR',
                'DE',
                'GR',
                'IE',
                'IT',
                'LV',
                'LT',
                'LU',
                'MT',
                'NL',
                'PT',
                'SK',
                'SI',
                'ES',
            ])->default('DE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropColumns('users', [
            'street',
            'postcode',
            'city',
            'country'
        ]);
    }
};
