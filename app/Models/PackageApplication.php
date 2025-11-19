<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackageApplication extends Model
{
    protected $fillable = [
        'user_id',
        'award_id',
        'package_type',
        'amount',
        'applicant_name',
        'applicant_email',
        'applicant_phone',
        'company_name',
        'company_size',
        'evidence_links',
        'payment_status',
        'stripe_payment_intent_id',
        'stripe_session_id',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function award()
    {
        return $this->belongsTo(Award::class);
    }
}
