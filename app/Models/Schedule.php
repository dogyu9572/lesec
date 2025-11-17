<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Schedule extends Model
{
    protected $fillable = [
        'title',
        'start_date',
        'end_date',
        'content',
        'disable_application',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'disable_application' => 'boolean',
    ];

    /**
     * 특정 날짜가 범위 내에 있는 일정 조회
     */
    public function scopeInDateRange($query, $date)
    {
        $dateCarbon = Carbon::parse($date);
        return $query->where('start_date', '<=', $dateCarbon->format('Y-m-d'))
                     ->where('end_date', '>=', $dateCarbon->format('Y-m-d'));
    }

    /**
     * 교육신청 불가 일정만 조회
     */
    public function scopeDisableApplication($query)
    {
        return $query->where('disable_application', true);
    }

    /**
     * 특정 날짜 범위와 겹치는 일정 조회
     */
    public function scopeOverlapping($query, $startDate, $endDate)
    {
        return $query->where(function ($q) use ($startDate, $endDate) {
            $q->whereBetween('start_date', [$startDate, $endDate])
              ->orWhereBetween('end_date', [$startDate, $endDate])
              ->orWhere(function ($subQ) use ($startDate, $endDate) {
                  $subQ->where('start_date', '<=', $startDate)
                       ->where('end_date', '>=', $endDate);
              });
        });
    }
}
