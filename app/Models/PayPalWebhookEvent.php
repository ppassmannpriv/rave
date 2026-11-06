<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayPalWebhookEvent extends Model
{
    protected $table = 'paypal_webhook_events';

    protected $fillable = ['event_id', 'event_type', 'payload', 'processed_at'];

    protected $casts = [
        'payload' => 'array',
        'processed_at' => 'datetime',
    ];
}
