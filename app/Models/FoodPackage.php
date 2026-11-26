<?php

namespace App\Models;

use App\Models\Concerns\HasImageUrl;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FoodPackage extends Model
{
    use HasImageUrl;

    protected $table = 'food_packages';

    protected $primaryKey = 'package_id';

    public $timestamps = false;

    protected $fillable = [
        'package_group',
        'package_name',
        'pricing_type',
        'description',
        'price',
        'price_with_water',
        'price_with_softdrinks',
        'freebie',
        'min_pax',
        'max_pax',
        'event_type',
        'image_path',
        'is_available',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'price_with_water' => 'decimal:2',
            'price_with_softdrinks' => 'decimal:2',
            'is_available' => 'boolean',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(PackageItem::class, 'package_id', 'package_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(CateringBooking::class, 'package_id', 'package_id');
    }

    public function computePricePerPax(?string $drinkOption = 'Solo'): float
    {
        if ($this->pricing_type !== 'Per Pax') {
            return (float) $this->price;
        }

        return match ($drinkOption) {
            'Bottled Water' => (float) ($this->price_with_water ?? $this->price),
            'Softdrinks' => (float) ($this->price_with_softdrinks ?? $this->price),
            default => (float) $this->price,
        };
    }

    public function computeTotal(int $guests, ?string $drinkOption = 'Solo'): float
    {
        if ($this->pricing_type === 'Per Pax') {
            return round($this->computePricePerPax($drinkOption) * $guests, 2);
        }

        return (float) $this->price;
    }

    public function isPerPax(): bool
    {
        return $this->pricing_type === 'Per Pax';
    }
}
