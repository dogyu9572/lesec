@extends('backoffice.layouts.app')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/backoffice/boards.css') }}">
@endsection

@section('title', '회원 통계')

@section('content')
<div class="member-stats-page board-container">
    <div class="board-card">
        <div class="board-card-header">
            <div class="board-page-card-title">
                <h6>회원 통계</h6>
                <span class="board-page-count">Total : {{ number_format($total_members) }}명</span>
            </div>
        </div>
        <div class="board-card-body">
            <div class="member-stats-section">
                <div class="section-header">
                    <h5>지역별 통계</h5>
                </div>
                <div class="member-stats-table">
                    <div class="member-stats-row member-stats-row--labels">
                        @foreach($region_stats as $region)
                            <div class="member-stats-cell">{{ $region['label'] }}</div>
                        @endforeach
                    </div>
                    <div class="member-stats-row member-stats-row--values">
                        @foreach($region_stats as $region)
                            <div class="member-stats-cell">{{ $region['percentage'] }}</div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="member-stats-section">
                <div class="section-header">
                    <h5>학년별 통계</h5>
                    <p class="section-description">
                        전체 회원 중 학생 비율: {{ $student_ratio }}, 교사 비율: {{ $teacher_ratio }}
                    </p>
                </div>
                <div class="member-stats-table">
                    <div class="member-stats-row member-stats-row--labels">
                        @foreach($grade_stats as $grade)
                            <div class="member-stats-cell">{{ $grade['label'] }}</div>
                        @endforeach
                    </div>
                    <div class="member-stats-row member-stats-row--values">
                        @foreach($grade_stats as $grade)
                            <div class="member-stats-cell">{{ $grade['percentage'] }}</div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="member-stats-section">
                <div class="section-header">
                    <h5>학교급 통계</h5>
                    <p class="section-description">학년 구분 값이 없거나 교사 회원은 ‘기타’ 항목으로 분류됩니다.</p>
                </div>
                <div class="member-stats-table member-stats-table--wide">
                    <div class="member-stats-row member-stats-row--labels">
                        @foreach($school_level_stats as $level)
                            <div class="member-stats-cell">{{ $level['label'] }}</div>
                        @endforeach
                    </div>
                    <div class="member-stats-row member-stats-row--values">
                        @foreach($school_level_stats as $level)
                            <div class="member-stats-cell">{{ $level['percentage'] }}</div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

