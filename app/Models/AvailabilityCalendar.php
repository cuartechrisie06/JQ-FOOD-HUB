<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\UniqueConstraintViolationException;

class AvailabilityCalendar extends Model
{
    protected $table = 'availability_calendar';

    protected $primaryKey = 'calendar_id';

    public $timestamps = false;

    protected $fillable = ['slot_date', 'max_bookings', 'is_blocked', 'remarks'];

    protected function casts(): array
    {
        return ['slot_date' => 'date', 'is_blocked' => 'boolean'];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'calendar_id', 'calendar_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(CateringBooking::class, 'calendar_id', 'calendar_id');
    }

    public static function getOrCreateForDate(string $date): self
    {
        $calendar = static::whereDate('slot_date', $date)->first();
        if ($calendar) {
            return $calendar;
        }

        try {
            return static::create([
                'slot_date' => $date,
                'max_bookings' => config('jqfood.default_max_bookings', 2),
                'is_blocked' => false,
                'remarks' => null,
            ]);
        } catch (UniqueConstraintViolationException $exception) {
            return static::whereDate('slot_date', $date)->firstOrFail();
        }
    }

    public function confirmedBookingCount(): int
    {
        return $this->bookings()->where('status', 'Confirmed')->count();
    }

    public function availabilityStatus(): string
    {
        if ($this->is_blocked) {
            return 'Not Available';
        }

        return $this->confirmedBookingCount() >= $this->max_bookings
            ? 'Fully Booked'
            : 'Available';
    }
}
