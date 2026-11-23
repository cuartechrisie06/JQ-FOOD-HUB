<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->increments('payment_id');
            $table->unsignedInteger('order_id');
            $table->unsignedInteger('verified_by')->nullable();
            $table->enum('payment_method', ['Cash', 'E-Wallet', 'Bank Transfer']);
            $table->enum('payment_type', ['Down Payment', 'Balance', 'Full Payment']);
            $table->decimal('amount', 10, 2);
            $table->string('reference_no', 50)->nullable();
            $table->string('proof_image', 255)->nullable();
            $table->enum('status', ['Pending', 'Verified', 'Rejected'])->default('Pending');
            $table->dateTime('submitted_at');
            $table->dateTime('verified_at')->nullable();
            $table->foreign('order_id')->references('order_id')->on('orders')->cascadeOnDelete();
            $table->foreign('verified_by')->references('user_id')->on('users')->nullOnDelete();
        });

        Schema::create('catering_bookings', function (Blueprint $table) {
            $table->increments('booking_id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('package_id');
            $table->unsignedInteger('calendar_id');
            $table->unsignedInteger('order_id')->nullable();
            $table->string('event_type', 50);
            $table->time('event_time');
            $table->string('event_location', 255);
            $table->integer('guest_count');
            $table->text('special_requests')->nullable();
            $table->enum('status', ['Pending', 'Confirmed', 'Rejected', 'Cancelled'])->default('Pending');
            $table->dateTime('created_at');
            $table->foreign('user_id')->references('user_id')->on('users')->restrictOnDelete();
            $table->foreign('package_id')->references('package_id')->on('food_packages')->restrictOnDelete();
            $table->foreign('calendar_id')->references('calendar_id')->on('availability_calendar')->restrictOnDelete();
            $table->foreign('order_id')->references('order_id')->on('orders')->nullOnDelete();
        });

        Schema::create('reviews', function (Blueprint $table) {
            $table->increments('review_id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('order_id');
            $table->unsignedTinyInteger('rating');
            $table->text('comment')->nullable();
            $table->enum('status', ['Pending', 'Approved', 'Hidden'])->default('Pending');
            $table->dateTime('created_at');
            $table->unique('order_id');
            $table->foreign('user_id')->references('user_id')->on('users')->cascadeOnDelete();
            $table->foreign('order_id')->references('order_id')->on('orders')->cascadeOnDelete();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->increments('notification_id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('order_id')->nullable();
            $table->string('type', 40);
            $table->string('message', 255);
            $table->boolean('is_read')->default(false);
            $table->boolean('email_sent')->default(false);
            $table->dateTime('created_at');
            $table->foreign('user_id')->references('user_id')->on('users')->cascadeOnDelete();
            $table->foreign('order_id')->references('order_id')->on('orders')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('catering_bookings');
        Schema::dropIfExists('payments');
    }
};
