<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RevenueStatistics extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 통계 항목들과의 관계
     */
    public function items()
    {
        return $this->hasMany(RevenueStatisticsItem::class, 'revenue_statistics_id')->orderBy('sort_order');
    }
}
