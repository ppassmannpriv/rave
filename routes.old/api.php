<?php

Route::group(['prefix' => 'v1', 'as' => 'api.', 'namespace' => 'Api\V1\Admin', 'middleware' => ['auth:sanctum']], function () {
    // Event
    Route::post('events/media', 'EventApiController@storeMedia')->name('events.storeMedia');
    Route::apiResource('events', 'EventApiController');

    // Event Tickets
    Route::apiResource('event-tickets', 'EventTicketsApiController');

    // Event Ticket Code
    Route::apiResource('event-ticket-codes', 'EventTicketCodeApiController');

    // Order
    Route::apiResource('orders', 'OrderApiController');

    // Payment
    Route::apiResource('payments', 'PaymentApiController');
});