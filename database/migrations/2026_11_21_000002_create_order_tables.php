<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->increments('order_id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('calendar_id');
            $table->enum('order_type', ['Regular', 'Bulk', 'Packed Lunch', 'Catering']);
            $table->enum('fulfillment_method', ['Pickup', 'Delivery']);
            $table->time('fulfillment_time');
            $table->string('delivery_address', 255)->nullable();
            $table->decimal('total_amount', 10, 2);
            $table->decimal('downpayment_amount', 10, 2)->default(0);
            $table->enum('status', ['Pending', 'Confirmed', 'Preparing', 'Ready', 'Out for Delivery', 'Completed', 'Cancelled'])->default('Pending');
            $table->text('notes')->nullable();
            $table->dateTime('created_at');
            $table->foreign('user_id')->references('user_id')->on('users')->restrictOnDelete();
            $table->foreign('calendar_id')->references('calendar_id')->on('availability_calendar')->restrictOnDelete();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->increments('order_item_id');
            $table->unsignedInteger('order_id');
            $table->unsignedInteger('product_id')->nullable();
            $table->unsignedInteger('package_id')->nullable();
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('subtotal', 10, 2);
            $table->foreign('order_id')->references('order_id')->on('orders')->cascadeOnDelete();
            $table->foreign('product_id')->references('product_id')->on('products')->restrictOnDelete();
            $table->foreign('package_id')->references('package_id')->on('food_packages')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
