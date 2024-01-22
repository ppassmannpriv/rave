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
        if (Schema::hasColumn('event_ticket_codes', 'event_ticket_id') === false) {
            Schema::table('event_ticket_codes', function (Blueprint $table) {
                $table->unsignedBigInteger('event_ticket_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('event_ticket_codes', function (Blueprint $table) {
            $table->dropColumn('event_ticket_id');
        });
    }
};
