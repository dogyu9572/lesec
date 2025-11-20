<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisitorLog extends Model
{
    protected $fillable = [
        'user_id',
        'member_id',
        'ip_address',
        'user_agent',
        'page_url',
        'referer',
        'session_id',
        'is_unique',
    ];

    protected $casts = [
        'is_unique' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 관리자와의 관계
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * 일반 사용자와의 관계
     */
    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id');
    }

    /**
     * 로그인한 사용자만 조회하는 스코프
     */
    public function scopeLoggedIn($query)
    {
        return $query->where(function($q) {
            $q->whereNotNull('user_id')->orWhereNotNull('member_id');
        });
    }

    /**
     * 비로그인 사용자만 조회하는 스코프
     */
    public function scopeGuest($query)
    {
        return $query->whereNull('user_id')->whereNull('member_id');
    }

    /**
     * 일반 사용자(일반 회원)만 조회하는 스코프
     */
    public function scopeUsers($query)
    {
        return $query->whereNotNull('member_id');
    }

    /**
     * 검색 필터 스코프
     */
    public function scopeSearch($query, $request)
    {
        // 회원명 검색
        if ($request->filled('name')) {
            $query->whereHas('member', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->name . '%');
            });
        }

        // 기간 검색
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        return $query;
    }
}
