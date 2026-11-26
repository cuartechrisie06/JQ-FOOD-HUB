<?php

namespace App\Models\Concerns;

/**
 * Resolves a model's `image_path` to a public URL, falling back to the
 * branded placeholder. Replaces the copy-pasted logic that used to live in
 * several Blade views.
 */
trait HasImageUrl
{
    public function getHasImageAttribute(): bool
    {
        $path = $this->image_path;

        if (! $path) {
            return false;
        }

        return str_starts_with($path, 'http://')
            || str_starts_with($path, 'https://')
            || file_exists(storage_path('app/public/'.$path));
    }

    public function getImageUrlAttribute(): string
    {
        if (! $this->has_image) {
            return asset('images/placeholder-product.svg');
        }

        return str_starts_with($this->image_path, 'http')
            ? $this->image_path
            : asset('storage/'.$this->image_path);
    }
}
