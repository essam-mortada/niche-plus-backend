<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'city', 'company', 'tier', 'avatar'
    ];

    protected $hidden = ['password'];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function supplier()
    {
        return $this->hasOne(Supplier::class);
    }

    public function conciergeRequests()
    {
        return $this->hasMany(Concierge::class);
    }

    public function nominations()
    {
        return $this->hasMany(Nomination::class, 'submitted_by');
    }
}
