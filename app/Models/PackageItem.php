<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackageItem extends Model
{
    protected $primaryKey = 'package_item_id';

    public $timestamps = false;

    protected $fillable = ['package_id', 'item_description', 'quantity'];

    public function foodPackage(): BelongsTo
    {
        return $this->belongsTo(FoodPackage::class, 'package_id', 'package_id');
    }
}
