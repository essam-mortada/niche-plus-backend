<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasFileCleanup;

class Issue extends Model
{
    use HasFileCleanup;

    protected $fillable = [
        'issue_no', 'cover', 'pdf_url', 'premium'
    ];

    protected $casts = [
        'premium' => 'boolean'
    ];

    protected $appends = ['cover_url', 'pdf_full_url'];

    /**
     * Fields that contain file paths to be cleaned up
     */
    protected $fileFields = ['cover', 'pdf_url'];

    /**
     * Get the full URL for the cover image
     */
    public function getCoverUrlAttribute()
    {
        if (!$this->cover) {
            return null;
        }

        if (str_starts_with($this->cover, 'http://') || str_starts_with($this->cover, 'https://')) {
            return $this->cover;
        }

        return url('storage/' . $this->cover);
    }

    /**
     * Get the full URL for the PDF
     */
    public function getPdfFullUrlAttribute()
    {
        if (!$this->pdf_url) {
            return null;
        }

        if (str_starts_with($this->pdf_url, 'http://') || str_starts_with($this->pdf_url, 'https://')) {
            return $this->pdf_url;
        }

        return url('storage/' . $this->pdf_url);
    }
}
