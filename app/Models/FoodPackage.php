<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FoodPackage extends Model
{
    protected $table = 'food_packages';

    protected $primaryKey = 'package_id';

    public $timestamps = false;

    protected $fillable = [
        'package_name', 'description', 'price', 'min_pax', 'max_pax',
        'event_type', 'image_path', 'is_available',
    ];

    protected function casts(): array
    {
        return ['price' => 'decimal:2', 'is_available' => 'boolean'];
    }

    public function items(): HasMany
    {
        return $this->hasMany(PackageItem::class, 'package_id', 'package_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(CateringBooking::class, 'package_id', 'package_id');
    }
}
