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
                                    <label for="title">제목</label>
                                    <input type="text" id="title" name="title" value="{{ old('title', $statistics->title) }}" required placeholder="제목을 입력하세요">
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
                                            <th>항목</th>
                                            <th>참가인원</th>
                                            <th>참가학교</th>
                                            <th>수익</th>
                                            <th style="width: 150px;">관리</th>
                                        </tr>
                                    </thead>
                                    <tbody id="items-body">
                                        @if($statistics->items && $statistics->items->count() > 0)
                                            @foreach($statistics->items as $index => $item)
                                                <tr data-item-index="{{ $index }}">
                                                    <td>
                                                        <input type="text" name="items[{{ $index }}][item_name]" class="form-control" value="{{ $item->item_name }}" required>
                                                    </td>
                                                    <td>
                                                        <input type="number" name="items[{{ $index }}][participants_count]" class="form-control" value="{{ $item->participants_count }}" min="0">
                                                    </td>
                                                    <td>
                                                        <input type="text" name="items[{{ $index }}][school_name]" class="form-control" value="{{ $item->school_name }}">
                                                    </td>
                                                    <td>
                                                        <input type="number" name="items[{{ $index }}][revenue]" class="form-control" value="{{ $item->revenue }}" min="0">
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
                                                <td colspan="5" class="text-center" style="padding: 40px 20px; border: none !important; border-bottom: none !important;">등록된 항목이 없습니다.</td>
                                            </tr>
                                        @endif
                                    </tbody>
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

