<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Concierge extends Model
{
    protected $table = 'concierge';

    protected $fillable = [
        'user_id', 
        'type', 
        'message', 
        'contact_name',
        'contact_email',
        'contact_phone',
        'company_name',
        'preferred_date',
        'preferred_time',
        'location',
        'number_of_people',
        'budget',
        'special_requirements',
        'status',
        'admin_notes'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
