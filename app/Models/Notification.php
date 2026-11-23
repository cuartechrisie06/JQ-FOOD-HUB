<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $primaryKey = 'notification_id';

    public $timestamps = false;

    protected $fillable = [
        'user_id', 'order_id', 'type', 'message', 'is_read', 'email_sent', 'created_at',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'email_sent' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'order_id');
    }
}
