<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAccessLog extends Model
{
    protected $table = 'user_access_logs';
    
    protected $fillable = [
        'user_id',
        'name',
        'ip_address',
        'user_agent',
        'referer',
        'login_at',
    ];

    protected $casts = [
        'login_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 사용자와의 관계
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * 일반 사용자만 조회하는 스코프
     */
    public function scopeUsers($query)
    {
        return $query->whereHas('user', function($q) {
            $q->where('role', 'member');
        });
    }

    /**
     * 관리자만 조회하는 스코프
     */
    public function scopeAdmins($query)
    {
        return $query->whereHas('user', function($q) {
            $q->whereIn('role', ['admin', 'super_admin']);
        });
    }

    /**
     * 검색 필터 스코프
     */
    public function scopeSearch($query, $request)
    {
        // 회원명/관리자명 검색
        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        // 기간 검색
        if ($request->filled('from')) {
            $query->whereDate('login_at', '>=', $request->from);
        }
        
        if ($request->filled('to')) {
            $query->whereDate('login_at', '<=', $request->to);
        }

        return $query;
    }
}
