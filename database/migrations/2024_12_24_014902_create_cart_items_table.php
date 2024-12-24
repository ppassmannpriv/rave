<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Product;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->integer('qty')->default(1);
            $table->integer('single_price')->default(0);
            $table->integer('total_price')->default(0);
            $table->enum('type', Product::PRODUCT_TYPES)->default(Product::DEFAULT_PRODUCT_TYPE);
            $table->foreignId('product_id')->nullable()->constrained('products');
            $table->foreignId('cart_id')->constrained('carts');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
