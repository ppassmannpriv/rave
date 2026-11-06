<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->boolean('admin_only')->default(false)->after('active');
        });
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('external_id')->nullable()->after('reference');
        });
        Schema::table('transaction_partials', function (Blueprint $table) {
            $table->string('external_id')->nullable()->after('transaction_id');
            $table->unique(['transaction_id', 'external_id']);
        });
        Schema::create('paypal_webhook_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_id')->unique();
            $table->string('event_type');
            $table->json('payload');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });

        $now = now();
        DB::table('payment_methods')->insertOrIgnore([
            'alias' => 'paypal',
            'name' => 'PayPal',
            'FQN' => 'PayPal',
            'description' => 'Pay securely with PayPal.',
            'active' => false,
            'admin_only' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function down()
    {
        DB::table('payment_methods')->where('alias', 'paypal')->delete();
        Schema::dropIfExists('paypal_webhook_events');
        Schema::table('transaction_partials', function (Blueprint $table) {
            $table->dropUnique(['transaction_id', 'external_id']);
            $table->dropColumn('external_id');
        });
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('external_id');
        });
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->dropColumn('admin_only');
        });
    }
};
