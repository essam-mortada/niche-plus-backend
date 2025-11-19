<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventPackage extends Model
{
    protected $fillable = [
        'award_id',
        'package_type',
        'price',
        'description',
        'benefits',
        'is_available',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'benefits' => 'array',
        'is_available' => 'boolean',
    ];

    public function award()
    {
        return $this->belongsTo(Award::class);
    }

    public function applications()
    {
        return $this->hasMany(PackageApplication::class, 'package_type', 'package_type')
            ->where('award_id', $this->award_id);
    }
}
