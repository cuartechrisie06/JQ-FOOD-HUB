<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->increments('category_id');
            $table->string('category_name', 80);
            $table->string('description', 200)->nullable();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->increments('product_id');
            $table->unsignedInteger('category_id');
            $table->string('product_name', 100);
            $table->text('description')->nullable();
            $table->string('unit', 20);
            $table->decimal('unit_price', 10, 2);
            $table->integer('stock_qty');
            $table->integer('lead_time_days');
            $table->string('image_path', 255)->nullable();
            $table->boolean('is_available')->default(true);
            $table->dateTime('created_at');
            $table->foreign('category_id')->references('category_id')->on('categories')->restrictOnDelete();
        });

        Schema::create('food_packages', function (Blueprint $table) {
            $table->increments('package_id');
            $table->string('package_name', 100);
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->integer('min_pax');
            $table->integer('max_pax');
            $table->string('event_type', 50);
            $table->string('image_path', 255)->nullable();
            $table->boolean('is_available')->default(true);
        });

        Schema::create('package_items', function (Blueprint $table) {
            $table->increments('package_item_id');
            $table->unsignedInteger('package_id');
            $table->string('item_description', 150);
            $table->string('quantity', 50);
            $table->foreign('package_id')->references('package_id')->on('food_packages')->cascadeOnDelete();
        });

        Schema::create('availability_calendar', function (Blueprint $table) {
            $table->increments('calendar_id');
            $table->date('slot_date')->unique();
            $table->integer('max_bookings')->default(0);
            $table->boolean('is_blocked')->default(false);
            $table->string('remarks', 150)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('package_items');
        Schema::dropIfExists('availability_calendar');
        Schema::dropIfExists('food_packages');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
    }
};
