<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CateringBooking extends Model
{
    protected $table = 'catering_bookings';

    protected $primaryKey = 'booking_id';

    public $timestamps = false;

    protected $fillable = [
        'user_id', 'package_id', 'calendar_id', 'order_id', 'event_type', 'event_time',
        'event_location', 'guest_count', 'special_requests', 'status', 'created_at',
    ];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function foodPackage(): BelongsTo
    {
        return $this->belongsTo(FoodPackage::class, 'package_id', 'package_id');
    }

    public function calendar(): BelongsTo
    {
        return $this->belongsTo(AvailabilityCalendar::class, 'calendar_id', 'calendar_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'order_id');
    }
}
