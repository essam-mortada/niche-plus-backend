<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Nomination extends Model
{
    protected $fillable = [
        'event_id', 'category', 'nominee', 'instagram', 'submitted_by'
    ];

    public function award()
    {
        return $this->belongsTo(Award::class, 'event_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }
}
