@extends('backoffice.layouts.app')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/backoffice/boards.css') }}">
@endsection

@section('title', $pageTitle ?? '')

@section('content')
<div class="board-container">
    <div class="board-header">
        <a href="{{ route('backoffice.boards.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> 목록으로
        </a>
    </div>

    <div class="board-card">
        <div class="board-card-header">
            <h6>게시판 생성</h6>
        </div>
        <div class="board-card-body">
            @if ($errors->any())
                <div class="board-alert board-alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('backoffice.boards.store') }}" method="POST">
                @csrf

                <!-- 템플릿 선택 (필수) -->
                <div class="board-form-group">
                    <label for="template_id" class="board-form-label">템플릿 선택 <span class="required">*</span></label>
                    <select class="board-form-control" id="template_id" name="template_id" required>
                        <option value="">템플릿을 선택하세요</option>
                        @foreach ($templates as $template)
                            <option value="{{ $template->id }}" @selected(old('template_id') == $template->id)>
                                {{ $template->name }}
                            </option>
                        @endforeach
                    </select>
                    <small class="board-form-text">템플릿을 선택하면 게시판 설정이 자동으로 적용됩니다.</small>
                </div>

                <!-- 템플릿 정보 표시 영역 -->
                <div id="templateInfo" class="board-template-info" style="display: none;">
                    <div class="board-template-info-header">
                        <i class="fas fa-info-circle"></i> 선택한 템플릿 정보
                    </div>
                    <div class="board-template-info-body">
                        <div class="board-template-info-row">
                            <span class="label">스킨:</span>
                            <span id="infoSkin">-</span>
                        </div>
                        <div class="board-template-info-row">
                            <span class="label">카테고리 그룹:</span>
                            <span id="infoCategoryGroup">-</span>
                        </div>
                        <div class="board-template-info-row">
                            <span class="label">공지 기능:</span>
                            <span id="infoNotice">-</span>
                        </div>
                        <div class="board-template-info-row">
                            <span class="label">정렬 기능:</span>
                            <span id="infoSorting">-</span>
                        </div>
                        <div class="board-template-info-row">
                            <span class="label">페이지당 글수:</span>
                            <span id="infoListCount">-</span>
                        </div>
                        <div class="board-template-info-row">
                            <span class="label">읽기 권한:</span>
                            <span id="infoPermissionRead">-</span>
                        </div>
                        <div class="board-template-info-row">
                            <span class="label">쓰기 권한:</span>
                            <span id="infoPermissionWrite">-</span>
                        </div>
                        <div class="board-template-info-row">
                            <span class="label">댓글 권한:</span>
                            <span id="infoPermissionComment">-</span>
                        </div>
                        <div class="board-template-info-row">
                            <span class="label">기본 필드:</span>
                            <span id="infoFieldConfig">-</span>
                        </div>
                        <div class="board-template-info-row">
                            <span class="label">커스텀 필드:</span>
                            <span id="infoCustomFields">-</span>
                        </div>
                    </div>
                </div>

                <hr style="margin: 20px 0; border: 0; border-top: 1px solid #e9ecef;">

                <!-- 기본 정보 입력 -->
                <div class="board-form-row">
                    <div class="board-form-col board-form-col-6">
                        <div class="board-form-group">
                            <label for="name" class="board-form-label">게시판 이름 <span class="required">*</span></label>
                            <input type="text" class="board-form-control" id="name" name="name" value="{{ old('name') }}" required>
                            <small class="board-form-text">사용자에게 표시될 게시판 이름입니다.</small>
                        </div>
                    </div>

                    <div class="board-form-col board-form-col-6">
                        <div class="board-form-group">
                            <label for="slug" class="board-form-label">게시판 슬러그 <span class="required">*</span></label>
                            <input type="text" class="board-form-control" id="slug" name="slug" value="{{ old('slug') }}" required>
                            <small class="board-form-text">URL에 사용될 식별자입니다. 영문, 숫자, 대시(-), 언더스코어(_)만 사용 가능합니다.</small>
                        </div>
                    </div>
                </div>

                <div class="board-form-group">
                    <label for="description" class="board-form-label">설명</label>
                    <textarea class="board-form-control board-form-textarea" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                    <small class="board-form-text">게시판에 대한 간단한 설명을 입력하세요. (선택사항)</small>
                </div>

                <div class="board-form-group">
                    <div class="board-checkbox-item">
                        <input type="checkbox" class="board-checkbox-input" id="is_active" name="is_active" value="1" @checked(old('is_active', '1') == '1')>
                        <label for="is_active" class="board-form-label">게시판 활성화</label>
                    </div>
                    <small class="board-form-text">체크하면 게시판이 활성화되어 사용할 수 있습니다.</small>
                </div>

                <div class="board-form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check"></i> 게시판 생성
                    </button>
                    <a href="{{ route('backoffice.boards.index') }}" class="btn btn-secondary">취소</a>                    
                </div>
            </form>
        </div>
    </div>
</div>

@section('scripts')
<script src="{{ asset('js/backoffice/boards.js') }}"></script>
@endsection
@endsection
