@extends('backoffice.layouts.app')

@section('title', '수익 통계 수정')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/common/buttons.css') }}">
<link rel="stylesheet" href="{{ asset('css/backoffice/admins.css') }}">
<link rel="stylesheet" href="{{ asset('css/backoffice/users.css') }}">
<link rel="stylesheet" href="{{ asset('css/backoffice/member-groups.css') }}">
@endsection

@section('content')
<div class="admin-form-container">
    <div class="form-header">      
        <a href="{{ route('backoffice.revenue-statistics.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> <span class="btn-text">목록으로</span>
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="admin-card">
                <div class="admin-card-body">
                    <form id="revenueStatisticsForm" action="{{ route('backoffice.revenue-statistics.update', $statistics) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="form-section">
                            <h3>제목</h3>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="title">제목 (년/월 선택)</label>
                                    @php
                                        // "2026년 1월" 형식을 "2026-01" 형식으로 변환
                                        $monthValue = old('title', $statistics->title);
                                        if (preg_match('/(\d{4})년\s*(\d{1,2})월/', $monthValue, $matches)) {
                                            $year = $matches[1];
                                            $month = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
                                            $monthValue = $year . '-' . $month;
                                        }
                                    @endphp
                                    <input type="month" id="title" name="title" value="{{ $monthValue }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h3>통계 내용</h3>
                            <div class="member-list-header">
                                <button type="button" id="add-item-btn" class="btn btn-success btn-sm">
                                    <i class="fas fa-plus"></i> 추가
                                </button>
                                <a href="{{ route('backoffice.revenue-statistics.download', $statistics) }}" class="btn btn-secondary btn-sm" id="download-btn">
                                    <i class="fas fa-download"></i> 다운로드
                                </a>
                            </div>
                            <div class="table-responsive">
                                <table class="board-table" id="items-table">
                                    <thead>
                                        <tr>
                                            <th>구분</th>
                                            <th>참가인원</th>
                                            <th>수익</th>
                                            <th style="width: 150px;">관리</th>
                                        </tr>
                                    </thead>
                                    <tbody id="items-body">
                                        @if($statistics->items && $statistics->items->count() > 0)
                                            @foreach($statistics->items as $index => $item)
                                                @php
                                                    $rowSchoolType = old("items.{$index}.school_type", $item->school_type);
                                                    $rowItemName = old("items.{$index}.item_name", $item->item_name);
                                                    $rowParticipants = old("items.{$index}.participants_count", $item->participants_count);
                                                    $rowRevenue = old("items.{$index}.revenue", $item->revenue);
                                                    $itemEditorError = $errors->has("items.{$index}.school_type")
                                                        || $errors->has("items.{$index}.item_name")
                                                        || $errors->has("items.{$index}.participants_count")
                                                        || $errors->has("items.{$index}.revenue");
                                                    $isEditing = $itemEditorError;
                                                    $schoolTypeLabel = match ($rowSchoolType) {
                                                        'middle' => '중등',
                                                        'high' => '고등',
                                                        'custom' => $rowItemName ?: '직접입력',
                                                        default => '',
                                                    };
                                                @endphp
                                                <tr data-item-index="{{ $index }}" data-row-editing="{{ $isEditing ? '1' : '0' }}">
                                                    <td>
                                                        <div class="school-type-readonly" @if($isEditing) style="display: none;" @endif>{{ $schoolTypeLabel }}</div>
                                                        <div class="school-type-editor" @if(!$isEditing) style="display: none;" @endif>
                                                            <select name="items[{{ $index }}][school_type]" class="form-control school-type-select" required>
                                                                <option value="">선택하세요</option>
                                                                <option value="middle" {{ $rowSchoolType === 'middle' ? 'selected' : '' }}>중등</option>
                                                                <option value="high" {{ $rowSchoolType === 'high' ? 'selected' : '' }}>고등</option>
                                                                <option value="custom" {{ $rowSchoolType === 'custom' ? 'selected' : '' }}>직접입력</option>
                                                            </select>
                                                            <input type="text" name="items[{{ $index }}][item_name]" class="form-control school-type-custom-input mt-2" placeholder="구분명을 입력하세요" maxlength="100" value="{{ $rowItemName }}" @if($rowSchoolType !== 'custom') style="display:none;" @endif>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="participants-readonly" @if($isEditing) style="display: none;" @endif>{{ $rowParticipants }}</div>
                                                        <input type="number" name="items[{{ $index }}][participants_count]" class="form-control participants-count" value="{{ $rowParticipants }}" min="0" data-index="{{ $index }}" @if(!$isEditing) style="display: none;" @endif>
                                                    </td>
                                                    <td>
                                                        <div class="revenue-readonly" @if($isEditing) style="display: none;" @endif>{{ number_format((int) $rowRevenue) }}</div>
                                                        <input type="number" name="items[{{ $index }}][revenue]" class="form-control revenue-input" value="{{ $rowRevenue }}" min="0" data-index="{{ $index }}" @if(!$isEditing) style="display: none;" @endif>
                                                    </td>
                                                    <td>
                                                        <div class="board-btn-group">
                                                            <button type="button" class="btn btn-primary btn-sm edit-item-btn">
                                                                <i class="fas fa-edit"></i> 수정
                                                            </button>
                                                            <button type="button" class="btn btn-danger btn-sm remove-item-btn">
                                                                <i class="fas fa-trash"></i> 삭제
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr class="empty-row" style="border: none; display: table-row;">
                                                <td colspan="4" class="text-center" style="padding: 40px 20px; border: none !important; border-bottom: none !important;">등록된 항목이 없습니다.</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="2" class="text-right" style="font-weight: bold;">수익 합계:</td>
                                            <td id="total-revenue" style="font-weight: bold; font-size: 1.1em;">{{ $statistics->items ? $statistics->items->sum('revenue') : 0 }}</td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> 저장
                            </button>
                            <a href="{{ route('backoffice.revenue-statistics.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> 취소
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
window.itemIndex = {{ ($statistics->items && $statistics->items->count() > 0) ? $statistics->items->count() : 0 }};
</script>
<script src="{{ asset('js/backoffice/revenue-statistics.js') }}"></script>
@endsection

