@extends('backoffice.layouts.app')

@section('title', '회원 그룹 등록')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/common/buttons.css') }}">
<link rel="stylesheet" href="{{ asset('css/backoffice/admins.css') }}">
<link rel="stylesheet" href="{{ asset('css/backoffice/users.css') }}">
<link rel="stylesheet" href="{{ asset('css/backoffice/member-groups.css') }}">
@endsection

@section('content')
<div class="admin-form-container">
    <div class="form-header">      
        <a href="{{ route('backoffice.member-groups.index') }}" class="btn btn-secondary">
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
                <div class="admin-card-header">
                    <h6>회원 그룹 정보</h6>
                </div>
                <div class="admin-card-body">
                    <form id="memberGroupForm" action="{{ route('backoffice.member-groups.store') }}" method="POST">
                        @csrf                       
                        
                        <div class="form-section">
                            <h3>회원 그룹 관리</h3>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="name">그룹명</label>
                                    <input type="text" id="name" name="name" value="{{ old('name') }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h3>소속 정보</h3>
                            <div class="member-list-header">
                                <button type="button" id="add-member-btn" class="btn btn-success">
                                    <i class="fas fa-plus"></i> 회원 추가
                                </button>
                            </div>
                            <div class="table-responsive">
                                <table class="board-table">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>이름</th>
                                            <th>(학생)연락처</th>
                                            <th>부모 연락처</th>
                                            <th>이메일</th>
                                            <th>등록일</th>
                                            <th>삭제</th>
                                        </tr>
                                    </thead>
                                    <tbody id="member-list-body">
                                        <tr style="border: none;">
                                            <td colspan="7" class="text-center" style="padding: 40px 20px; border: none !important; border-bottom: none !important;">등록된 회원이 없습니다.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> 저장
                            </button>
                            <a href="{{ route('backoffice.member-groups.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> 취소
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@include('backoffice.modals.member-search', [
    'selectionMode' => 'multiple',
    'formAction' => route('backoffice.member-groups.search-members')
])

@endsection
@section('scripts')
<script src="{{ asset('js/backoffice/member-groups.js') }}"></script>
@endsection

