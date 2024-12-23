<?php

namespace App\Mail;

use App\Models\PaymentMethod\PayPalExpress;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Order;

class OrderCreated extends Mailable
{
    use Queueable, SerializesModels;

    public Order $order;
    public ?string $paymentMethodAlias;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Order $order, ?string $paymentMethodAlias = null)
    {
        $this->order = $order;
        $this->paymentMethodAlias = $paymentMethodAlias;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $view = 'mail.order-created';
        if ($this->paymentMethodAlias === PayPalExpress::ALIAS) {
            $view = 'mail.paypal-order-created';
        }
        return $this->subject('Order #' . $this->order->id)->view($view);
    }
}
