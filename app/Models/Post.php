<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasFileCleanup;

class Post extends Model
{
    use HasFileCleanup;

    protected $fillable = [
        'title', 'cover', 'category', 'excerpt', 'body', 'published_at'
    ];

    protected $casts = [
        'published_at' => 'datetime'
    ];

    protected $appends = ['cover_url'];

    /**
     * Fields that contain file paths to be cleaned up
     */
    protected $fileFields = ['cover'];

    /**
     * Get the full URL for the cover image
     */
    public function getCoverUrlAttribute()
    {
        if (!$this->cover) {
            return null;
        }

        // If it's already a full URL, return as is
        if (str_starts_with($this->cover, 'http://') || str_starts_with($this->cover, 'https://')) {
            return $this->cover;
        }

        // Otherwise, prepend the storage URL
        return url('storage/' . $this->cover);
    }
}
