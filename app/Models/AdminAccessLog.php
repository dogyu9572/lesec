<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminAccessLog extends Model
{
    protected $table = 'admin_access_logs';
    
    protected $fillable = [
        'admin_id',
        'name',
        'ip_address',
        'user_agent',
        'referer',
        'accessed_at',
    ];

    protected $casts = [
        'accessed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 관리자와의 관계
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * 검색 필터 스코프
     */
    public function scopeSearch($query, $request)
    {
        // 관리자명 검색
        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        // 기간 검색
        if ($request->filled('from')) {
            $query->whereDate('accessed_at', '>=', $request->from);
        }
        
        if ($request->filled('to')) {
            $query->whereDate('accessed_at', '<=', $request->to);
        }

        return $query;
    }
}
