<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RevenueStatisticsItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'revenue_statistics_id',
        'school_type',
        'item_name',
        'participants_count',
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

    /**
     * 목록·CSV 등에 표시할 구분 라벨
     */
    public function categoryLabelForDisplay(): string
    {
        return match ($this->school_type) {
            'middle' => '중등',
            'high' => '고등',
            'custom' => $this->item_name !== null && $this->item_name !== '' ? $this->item_name : '직접입력',
            default => '-',
        };
    }
}
