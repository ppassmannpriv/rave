<?php

use App\Models\PaymentMethod;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        PaymentMethod::create([
            'alias' => 'paypal_ff',
            'name' => 'PayPal Friends&Family',
            'active' => true,
            'FQN' => 'PayPalFriendsFamily',
            'description' => 'PayPal Friends & Family - We will generate a code for you, that you will have to use as a subject after ordering. Make sure to do this within the following 24h after you ordered otherwise we will cancel your order!',
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        PaymentMethod::where('alias', '=', 'paypal_ff')->first()->delete();
    }
};
