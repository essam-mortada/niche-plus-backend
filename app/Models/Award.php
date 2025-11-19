<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasFileCleanup;

class Award extends Model
{
    use HasFileCleanup;

    protected $fillable = [
        'city', 'venue', 'date', 'description', 'image', 'ticket_link'
    ];

    protected $casts = [
        'date' => 'date'
    ];

    protected $appends = ['image_url'];

    /**
     * Fields that contain file paths to be cleaned up
     */
    protected $fileFields = ['image'];

    /**
     * Get the full URL for the image
     */
    public function getImageUrlAttribute()
    {
        if (!$this->image) {
            return null;
        }

        // If it's already a full URL, return as is
        if (str_starts_with($this->image, 'http://') || str_starts_with($this->image, 'https://')) {
            return $this->image;
        }

        // Build the full URL
        $baseUrl = config('app.url');
        return $baseUrl . '/storage/' . $this->image;
    }

    public function nominations()
    {
        return $this->hasMany(Nomination::class, 'event_id');
    }
}
