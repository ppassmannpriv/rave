<?php

namespace App\Services;
use Twilio\Rest\Client;

class TwilioService
{
    protected Client $client;

    public function __construct()
    {
        $this->client = new Client(env('TWILIO_SID'), env('TWILIO_AUTH_TOKEN'));
    }

    public function sendWhatsApp(string $to, string $message)
    {
        return $this->client->messages->create('whatsapp:' . $to, [
            'from' => 'whatsapp:' . env('TWILIO_PHONE_NUMBER'),
            'body' => $message
        ]);
    }

    public function sendSMS($to, $message)
    {
        return $this->client->messages->create($to, [
            'from' => 'Schleuse 1',
            'body' => $message,
        ]);
    }
}
