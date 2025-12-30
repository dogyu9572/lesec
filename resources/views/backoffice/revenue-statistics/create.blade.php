@extends('backoffice.layouts.app')

@section('title', '수익 통계 등록')

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
                    <form id="revenueStatisticsForm" action="{{ route('backoffice.revenue-statistics.store') }}" method="POST">
                        @csrf                       
                        
                        <div class="form-section">
                            <h3>제목</h3>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="title">제목</label>
                                    <input type="text" id="title" name="title" value="{{ old('title') }}" required placeholder="제목을 입력하세요">
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h3>통계 내용</h3>
                            <div class="member-list-header">
                                <button type="button" id="download-btn" class="btn btn-secondary btn-sm" disabled>
                                    <i class="fas fa-download"></i> 다운로드
                                </button>
                                <button type="button" id="add-item-btn" class="btn btn-success btn-sm">
                                    <i class="fas fa-plus"></i> 추가
                                </button>                              
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
                                        <tr class="empty-row" style="border: none;">
                                            <td colspan="5" class="text-center" style="padding: 40px 20px; border: none !important; border-bottom: none !important;">등록된 항목이 없습니다.</td>
                                        </tr>
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
<script src="{{ asset('js/backoffice/revenue-statistics.js') }}"></script>
@endsection

