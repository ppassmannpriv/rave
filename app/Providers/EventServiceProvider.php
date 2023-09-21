<?php

namespace App\Providers;

use App\Listeners\OrderEventTicketGenerationListener;
use App\Events\OrderEventTicketGenerationEvent;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Events\EventTicketCreatedEvent;
use App\Events\EventTicketUpdatedEvent;
use App\Events\TransactionSavedEvent;
use App\Events\OrderSavedEvent;
use App\Listeners\EventTicketCreatedListener;
use App\Listeners\EventTicketUpdatedListener;
use App\Listeners\TransactionPaidListener;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        EventTicketCreatedEvent::class => [
            EventTicketCreatedListener::class,
        ],
        EventTicketUpdatedEvent::class => [
            EventTicketUpdatedListener::class,
        ],
        OrderEventTicketGenerationEvent::class => [
            OrderEventTicketGenerationListener::class
        ],
        TransactionSavedEvent::class => [
            TransactionPaidListener::class
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }
}
