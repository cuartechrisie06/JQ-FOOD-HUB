<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('food_packages', function (Blueprint $table) {
            $table->string('package_group', 100)->nullable()->after('package_id');
            $table->enum('pricing_type', ['Fixed', 'Per Pax'])->default('Fixed')->after('package_name');
            $table->decimal('price_with_water', 10, 2)->nullable()->after('price');
            $table->decimal('price_with_softdrinks', 10, 2)->nullable()->after('price_with_water');
            $table->string('freebie', 100)->nullable()->after('price_with_softdrinks');
            $table->integer('min_pax')->nullable()->change();
            $table->integer('max_pax')->nullable()->change();
        });

        Schema::table('catering_bookings', function (Blueprint $table) {
            $table->enum('drink_option', ['Solo', 'Bottled Water', 'Softdrinks'])->nullable()->after('guest_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('catering_bookings', function (Blueprint $table) {
            $table->dropColumn('drink_option');
        });

        Schema::table('food_packages', function (Blueprint $table) {
            $table->dropColumn([
                'package_group',
                'pricing_type',
                'price_with_water',
                'price_with_softdrinks',
                'freebie',
            ]);
            $table->integer('min_pax')->nullable(false)->change();
            $table->integer('max_pax')->nullable(false)->change();
        });
    }
};
