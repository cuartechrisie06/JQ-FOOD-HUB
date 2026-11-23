<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $primaryKey = 'product_id';

    public $timestamps = false;

    protected $fillable = [
        'category_id', 'product_name', 'description', 'unit', 'unit_price',
        'stock_qty', 'lead_time_days', 'image_path', 'is_available', 'created_at',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'is_available' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id', 'category_id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'product_id', 'product_id');
    }
}
