<?php

use App\Models\Event\EventTicket;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('event_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events');
            $table->integer('price');
            $table->enum('ticket_type', EventTicket::EVENT_TICKET_TYPES)->default(EventTicket::DEFAULT_EVENT_TICKET_TYPE);
            $table->dateTime('from');
            $table->dateTime('to');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_tickets');
    }
};
