<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ZohoToken extends Model
{
    protected $fillable = [
        'access_token',
        'refresh_token',
        'expires_at',
    ];

    protected $casts = [
        'exprires_at' => 'datetime',
    ];

    public function isExpired(): bool 
    {
        return now()->gte($this->expires_at);    
    }

}
