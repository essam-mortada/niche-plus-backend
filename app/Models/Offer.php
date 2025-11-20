<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasFileCleanup;

class Offer extends Model
{
    use HasFileCleanup;

    protected $fillable = [
        'supplier_id', 'title', 'photo', 'price', 'description', 'city', 'whatsapp',
        'status', 'rejection_reason', 'reviewed_at', 'reviewed_by'
    ];

    protected $appends = ['photo_url'];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    /**
     * Fields that contain file paths to be cleaned up
     */
    protected $fileFields = ['photo'];

    /**
     * Get the full URL for the photo
     */
    public function getPhotoUrlAttribute()
    {
        if (!$this->photo) {
            return null;
        }

        if (str_starts_with($this->photo, 'http://') || str_starts_with($this->photo, 'https://')) {
            return $this->photo;
        }

        return url('storage/' . $this->photo);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function views()
    {
        return $this->hasMany(OfferView::class);
    }

    public function getViewsCountAttribute()
    {
        return $this->views()->count();
    }

    public function getUniqueViewsCountAttribute()
    {
        return $this->views()->distinct('ip_address')->count('ip_address');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isApproved()
    {
        return $this->status === 'approved';
    }

    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }
}
