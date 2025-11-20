<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RevenueStatisticsItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'revenue_statistics_id',
        'item_name',
        'participants_count',
        'school_name',
        'revenue',
        'sort_order',
    ];

    protected $casts = [
        'participants_count' => 'integer',
        'revenue' => 'integer',
        'sort_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 수익 통계와의 관계
     */
    public function revenueStatistics()
    {
        return $this->belongsTo(RevenueStatistics::class, 'revenue_statistics_id');
    }
}
