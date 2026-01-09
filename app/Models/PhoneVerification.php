<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class PhoneVerification extends Model
{
    protected $fillable = [
        'phone',
        'verification_code',
        'verified_at',
        'expires_at',
        'attempts',
        'purpose',
        'session_id',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'expires_at' => 'datetime',
        'attempts' => 'integer',
    ];

    /**
     * 인증 완료된 인증번호만 조회
     */
    public function scopeVerified(Builder $query): Builder
    {
        return $query->whereNotNull('verified_at');
    }

    /**
     * 만료되지 않은 인증번호만 조회
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('verified_at')
            ->where('expires_at', '>', now());
    }

    /**
     * 만료된 인증번호만 조회
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNotNull('verified_at')
                ->orWhere('expires_at', '<=', now());
        });
    }
}
