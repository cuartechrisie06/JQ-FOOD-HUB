<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $primaryKey = 'order_id';

    public $timestamps = false;

    protected $fillable = [
        'user_id', 'calendar_id', 'order_type', 'fulfillment_method', 'fulfillment_time',
        'delivery_address', 'total_amount', 'downpayment_amount', 'status', 'notes', 'created_at',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'downpayment_amount' => 'decimal:2',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function calendar(): BelongsTo
    {
        return $this->belongsTo(AvailabilityCalendar::class, 'calendar_id', 'calendar_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'order_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'order_id', 'order_id');
    }

    public function booking(): HasOne
    {
        return $this->hasOne(CateringBooking::class, 'order_id', 'order_id');
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class, 'order_id', 'order_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'order_id', 'order_id');
    }
}
