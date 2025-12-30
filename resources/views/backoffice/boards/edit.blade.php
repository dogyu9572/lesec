@extends('backoffice.layouts.app')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/backoffice/boards.css') }}">
@endsection

@section('title', $pageTitle ?? '')

@section('content')
<div class="board-container">
    <div class="board-header">      
        <a href="{{ route('backoffice.boards.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> <span class="btn-text">목록으로</span>
        </a>
    </div>

    @if ($errors->any())
        <div class="board-alert board-alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="board-card">
        <div class="board-card-body">
                    <!-- 템플릿 정보 표시 -->
                    @if($board->template)
                        <div class="board-template-info">
                            <div class="template-info-header">
                                <h6><i class="fas fa-template"></i> 사용 중인 템플릿</h6>
                            </div>
                            <div class="template-info-content">
                                <div class="template-info-item">
                                    <span class="template-info-label">템플릿명:</span>
                                    <span class="template-info-value">{{ $board->template->name }}</span>
                                </div>
                                @if($board->template->description)
                                    <div class="template-info-item">
                                        <span class="template-info-label">설명:</span>
                                        <span class="template-info-value">{{ $board->template->description }}</span>
                                    </div>
                                @endif
                                <div class="template-info-item">
                                    <span class="template-info-label">스킨:</span>
                                    <span class="template-info-value">{{ $board->template->skin->name ?? '기본 스킨' }}</span>
                                </div>
                                <div class="template-info-item">
                                    <span class="template-info-label">페이지당 글 수:</span>
                                    <span class="template-info-value">{{ $board->list_count }}개</span>
                                </div>
                                <div class="template-info-item">
                                    <span class="template-info-label">권한 설정:</span>
                                    <span class="template-info-value">
                                        읽기: {{ $board->permission_read == 'all' ? '모두' : ($board->permission_read == 'member' ? '회원만' : '관리자만') }} | 
                                        쓰기: {{ $board->permission_write == 'all' ? '모두' : ($board->permission_write == 'member' ? '회원만' : '관리자만') }} | 
                                        댓글: {{ $board->permission_comment == 'all' ? '모두' : ($board->permission_comment == 'member' ? '회원만' : '관리자만') }}
                                    </span>
                                </div>
                                <div class="template-info-item">
                                    <span class="template-info-label">기능:</span>
                                    <span class="template-info-value">
                                        @if($board->enable_notice) 공지사항 @endif
                                        @if($board->enable_sorting) 정렬 @endif
                                        @if($board->enable_category) 카테고리 @endif
                                        @if($board->is_single_page) 단일페이지 @endif
                                    </span>
                                </div>
                                @if($board->custom_fields_config && count($board->custom_fields_config) > 0)
                                    <div class="template-info-item">
                                        <span class="template-info-label">커스텀 필드:</span>
                                        <span class="template-info-value">{{ count($board->custom_fields_config) }}개</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <form action="{{ route('backoffice.boards.update', $board) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="board-form-row">
                            <div class="board-form-col board-form-col-6">
                                <div class="board-form-group">
                                    <label for="name" class="board-form-label">게시판 이름 <span class="required">*</span></label>
                                    <input type="text" class="board-form-control" id="name" name="name" value="{{ old('name', $board->name) }}" required>
                                </div>
                            </div>

                            <div class="board-form-col board-form-col-6">
                                <div class="board-form-group">
                                    <label for="slug" class="board-form-label">게시판명 <span class="required">*</span></label>
                                    <input type="text" class="board-form-control" id="slug" name="slug" value="{{ old('slug', $board->slug) }}" readonly>
                                    <small class="board-form-text">게시판명은 수정할 수 없습니다.</small>
                                </div>
                            </div>
                        </div>

                        <div class="board-form-group">
                            <label for="template_id" class="board-form-label">템플릿 <span class="required">*</span></label>
                            <select class="board-form-control" id="template_id" name="template_id" required>
                                <option value="">템플릿을 선택하세요</option>
                                @foreach ($templates as $template)
                                    <option value="{{ $template->id }}" @selected(old('template_id', $board->template_id) == $template->id)>
                                        {{ $template->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="board-form-text">템플릿을 변경하면 게시판 설정이 자동으로 업데이트됩니다.</small>
                        </div>

                        <div class="board-form-group">
                            <label for="description" class="board-form-label">설명</label>
                            <textarea class="board-form-control board-form-textarea" id="description" name="description" rows="2">{{ old('description', $board->description) }}</textarea>
                        </div>

                        <div class="board-form-group">
                            <div class="board-checkbox-item">
                                <input type="checkbox" class="board-checkbox-input" id="is_active" name="is_active" value="1" @checked(old('is_active', $board->is_active))>
                                <label for="is_active" class="board-form-label">사용 여부</label>
                            </div>
                        </div>

                        <div class="board-form-row">
                            <div class="board-form-col board-form-col-6">
                                <div class="board-form-group">
                                    <label class="board-form-label">생성일</label>
                                    <div class="board-form-text">{{ $board->created_at->format('Y-m-d H:i') }}</div>
                                </div>
                            </div>
                            <div class="board-form-col board-form-col-6">
                                <div class="board-form-group">
                                    <label class="board-form-label">마지막 수정일</label>
                                    <div class="board-form-text">{{ $board->updated_at->format('Y-m-d H:i') }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="board-form-actions">
                            <button type="submit" class="btn btn-primary">수정</button>
                            <a href="{{ route('backoffice.boards.index') }}" class="btn btn-secondary">취소</a>                            
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/backoffice/boards.js') }}"></script>
@endsection
