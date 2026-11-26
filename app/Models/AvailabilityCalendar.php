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

    /** Order types that use up a day's capacity. Regular orders still show on the calendar. */
    public const CAPACITY_ORDER_TYPES = ['Catering', 'Bulk', 'Packed Lunch'];

    public function scopeWithBookedCounts($query)
    {
        return $query->withCount([
            'orders as event_orders_count' => fn ($orders) => $orders
                ->whereIn('order_type', self::CAPACITY_ORDER_TYPES)
                ->where('status', '!=', 'Cancelled'),
            'bookings as unlinked_confirmed_count' => fn ($bookings) => $bookings
                ->where('status', 'Confirmed')
                ->whereNull('order_id'),
        ]);
    }

    /**
     * Catering, bulk and packed-lunch orders that are still active on this date.
     * Cancelling an order frees its slot.
     */
    public function bookedCount(): int
    {
        if (array_key_exists('event_orders_count', $this->attributes)) {
            return (int) $this->attributes['event_orders_count']
                + (int) ($this->attributes['unlinked_confirmed_count'] ?? 0);
        }

        return $this->orders()
            ->whereIn('order_type', self::CAPACITY_ORDER_TYPES)
            ->where('status', '!=', 'Cancelled')
            ->count()
            + $this->bookings()->where('status', 'Confirmed')->whereNull('order_id')->count();
    }

    public function confirmedBookingCount(): int
    {
        return $this->bookedCount();
    }

    public function availabilityStatus(): string
    {
        if ($this->is_blocked) {
            return 'Not Available';
        }

        return $this->bookedCount() >= $this->max_bookings
            ? 'Fully Booked'
            : 'Available';
    }
}
