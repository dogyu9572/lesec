@extends('backoffice.layouts.app')

@section('title', '학교 상세 정보')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/backoffice/group-programs.css') }}">
<link rel="stylesheet" href="{{ asset('css/backoffice/member-groups.css') }}">
@endsection

@section('content')
<div class="board-container">
    <div class="board-header">
        <a href="{{ route('backoffice.schools.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> 목록으로
        </a>
    </div>

    <div class="board-card">
<div class="board-card-body">
            <div class="program-section">
                <div class="section-title">기본 정보</div>
                <div class="form-grid grid-2">
                    <div class="form-group">
                        <label>구분</label>
                        <div class="form-control-readonly">{{ $school->source_type_name }}</div>
                    </div>

                    <div class="form-group">
                        <label>시/도</label>
                        <div class="form-control-readonly">{{ $school->city ?? '-' }}</div>
                    </div>

                    <div class="form-group">
                        <label>시/군/구</label>
                        <div class="form-control-readonly">{{ $school->district ?? '-' }}</div>
                    </div>

                    <div class="form-group">
                        <label>학교급</label>
                        <div class="form-control-readonly">{{ $school->school_level_name ?? '-' }}</div>
                    </div>

                    <div class="form-group">
                        <label>학교명</label>
                        <div class="form-control-readonly">{{ $school->school_name }}</div>
                    </div>

                    <div class="form-group">
                        <label>학교 코드</label>
                        <div class="form-control-readonly">{{ $school->school_code ?? '-' }}</div>
                    </div>

                    <div class="form-group">
                        <label>주소</label>
                        <div class="form-control-readonly">{{ $school->address ?? '-' }}</div>
                    </div>

                    <div class="form-group">
                        <label>전화번호</label>
                        <div class="form-control-readonly">{{ $school->phone ?? '-' }}</div>
                    </div>

                    <div class="form-group">
                        <label>홈페이지</label>
                        <div class="form-control-readonly">
                            @if($school->homepage)
                                <a href="{{ $school->homepage }}" target="_blank">{{ $school->homepage }}</a>
                            @else
                                -
                            @endif
                        </div>
                    </div>

                    <div class="form-group">
                        <label>남녀공학 여부</label>
                        <div class="form-control-readonly">{{ $school->is_coed ? '남녀공학' : ($school->is_coed === false ? '단성' : '-') }}</div>
                    </div>

                    <div class="form-group">
                        <label>주야구분</label>
                        <div class="form-control-readonly">{{ $school->day_night_division ?? '-' }}</div>
                    </div>

                    <div class="form-group">
                        <label>개교기념일</label>
                        <div class="form-control-readonly">{{ $school->founding_date ? $school->founding_date->format('Y-m-d') : '-' }}</div>
                    </div>

                    <div class="form-group">
                        <label>상태</label>
                        <div class="form-control-readonly">{{ $school->status_name }}</div>
                    </div>

                    @if($school->source_type === 'api')
                        <div class="form-group">
                            <label>마지막 동기화 일시</label>
                            <div class="form-control-readonly">{{ $school->last_synced_at ? $school->last_synced_at->format('Y-m-d H:i:s') : '-' }}</div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="form-actions">
                <a href="{{ route('backoffice.schools.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> 목록으로
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.form-control-readonly {
    padding: 8px 12px;
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    min-height: 38px;
    display: flex;
    align-items: center;
}
</style>
@endsection

