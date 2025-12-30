@extends('backoffice.layouts.app')

@section('title', ($board->name ?? '게시판'))

@section('styles')
     <link rel="stylesheet" href="{{ asset('css/backoffice/summernote-custom.css') }}">
    <!-- Summernote CSS (Bootstrap 기반, 완전 무료) -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
@endsection

@section('content')
<div class="board-container">
    <div class="board-header">
        <a href="{{ route('backoffice.board-posts.index', $board->slug ?? 'notice') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> 목록으로
        </a>
    </div>

    <div class="board-card">
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

            <form action="{{ route('backoffice.board-posts.store', $board->slug ?? 'notice') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <input type="hidden" name="title" value="{{ old('title', '연구원 연락처') }}">
                <input type="hidden" name="content" value="{{ old('content', '연구원 연락처 정보를 저장합니다.') }}">

                @if($board->isNoticeEnabled())
                <div class="board-form-group">
                    <div class="board-checkbox-item">
                        <input type="checkbox" 
                               class="board-checkbox-input" 
                               id="is_notice" 
                               name="is_notice" 
                               value="1" 
                               @checked(old('is_notice') == '1')>
                        <label for="is_notice" class="board-form-label">
                            <i class="fas fa-bullhorn"></i> 공지글
                        </label>
                    </div>
                    <small class="board-form-text">체크하면 공지글로 설정되어 최상단에 표시됩니다.</small>
                </div>
                @endif

                @if($board->isFieldEnabled('category') && $categoryOptions && $categoryOptions->count() > 0)
                <div class="board-form-group">
                    <label for="category" class="board-form-label">
                        카테고리 분류
                        @if($board->isFieldRequired('category'))
                            <span class="required">*</span>
                        @endif
                    </label>
                    <select class="board-form-control" id="category" name="category" @if($board->isFieldRequired('category')) required @endif>
                        <option value="">카테고리를 선택하세요</option>
                        @foreach($categoryOptions as $category)
                            <option value="{{ $category->name }}" @selected(old('category') == $category->name)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                @if($board->isFieldEnabled('title'))
                <div class="board-form-group">
                    <label for="title" class="board-form-label">
                        제목
                        @if($board->isFieldRequired('title'))
                            <span class="required">*</span>
                        @endif
                    </label>
                    <input type="text" class="board-form-control" id="title" name="title" value="{{ old('title') }}" @if($board->isFieldRequired('title')) required @endif>
                </div>
                @endif

                @if($board->isFieldEnabled('content'))
                <div class="board-form-group">
                    <label for="content" class="board-form-label">
                        내용
                        @if($board->isFieldRequired('content'))
                            <span class="required">*</span>
                        @endif
                    </label>
                    <textarea class="board-form-control board-form-textarea" id="content" name="content" rows="15" @if($board->isFieldRequired('content')) required @endif>{{ old('content') }}</textarea>
                </div>
                @endif

                <!-- 커스텀 필드 입력 폼 -->
                @if($board->custom_fields_config && count($board->custom_fields_config) > 0)
                    @foreach($board->custom_fields_config as $fieldConfig)
                        <div class="board-form-group">
                            <label for="custom_field_{{ $fieldConfig['name'] }}" class="board-form-label">
                                {{ $fieldConfig['label'] }}
                                @if($fieldConfig['required'])
                                    <span class="required">*</span>
                                @endif
                            </label>
                            
                            @if($fieldConfig['type'] === 'text')
                                <input type="text" 
                                       class="board-form-control" 
                                       id="custom_field_{{ $fieldConfig['name'] }}" 
                                       name="custom_field_{{ $fieldConfig['name'] }}" 
                                       value="{{ old('custom_field_' . $fieldConfig['name']) }}"
                                       placeholder="{{ $fieldConfig['placeholder'] ?? '' }}"
                                       @if($fieldConfig['required']) required @endif>
                            @elseif($fieldConfig['type'] === 'select')
                                @if($fieldConfig['options'])
                                    <select class="board-form-control" 
                                            id="custom_field_{{ $fieldConfig['name'] }}" 
                                            name="custom_field_{{ $fieldConfig['name'] }}"
                                            @if($fieldConfig['required']) required @endif>
                                        <option value="">선택하세요</option>
                                        @foreach(explode(",", $fieldConfig['options']) as $option)
                                            @php $option = trim($option); @endphp
                                            @if(!empty($option))
                                                <option value="{{ $option }}" @selected(old('custom_field_' . $fieldConfig['name']) == $option)>
                                                    {{ $option }}
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                @else
                                    <div class="board-form-text text-muted">셀렉박스는 선택 옵션이 필요합니다.</div>
                                @endif
                            @elseif($fieldConfig['type'] === 'checkbox')
                                @if($fieldConfig['options'])
                                    <div class="board-options-list board-options-horizontal">
                                        @foreach(explode(",", $fieldConfig['options']) as $option)
                                            @php $option = trim($option); @endphp
                                            @if(!empty($option))
                                                <div class="board-option-item">
                                                    <input type="checkbox" 
                                                           id="option_{{ $fieldConfig['name'] }}_{{ $loop->index }}" 
                                                           name="custom_field_{{ $fieldConfig['name'] }}[]" 
                                                           value="{{ $option }}"
                                                           {{ in_array($option, old('custom_field_' . $fieldConfig['name'], [])) ? 'checked' : '' }}>
                                                    <label for="option_{{ $fieldConfig['name'] }}_{{ $loop->index }}">{{ $option }}</label>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @else
                                    <div class="board-checkbox-item">
                                        <input type="checkbox" 
                                               class="board-checkbox-input" 
                                               id="custom_field_{{ $fieldConfig['name'] }}" 
                                               name="custom_field_{{ $fieldConfig['name'] }}" 
                                               value="1"
                                               {{ old('custom_field_' . $fieldConfig['name']) == '1' ? 'checked' : '' }}>
                                        <label for="custom_field_{{ $fieldConfig['name'] }}" class="board-form-label">
                                            {{ $fieldConfig['label'] }}
                                        </label>
                                    </div>
                                @endif
                            @elseif($fieldConfig['type'] === 'radio')
                                @if($fieldConfig['options'])
                                    <div class="board-options-list board-options-horizontal">
                                        @foreach(explode(",", $fieldConfig['options']) as $option)
                                            @php $option = trim($option); @endphp
                                            @if(!empty($option))
                                                <div class="board-option-item">
                                                    <input type="radio" 
                                                           id="option_{{ $fieldConfig['name'] }}_{{ $loop->index }}" 
                                                           name="custom_field_{{ $fieldConfig['name'] }}" 
                                                           value="{{ $option }}"
                                                           @checked(old('custom_field_' . $fieldConfig['name']) == $option)
                                                           @if($fieldConfig['required']) required @endif>
                                                    <label for="option_{{ $fieldConfig['name'] }}_{{ $loop->index }}">{{ $option }}</label>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @else
                                    <div class="board-form-text text-muted">라디오 버튼은 선택 옵션이 필요합니다.</div>
                                @endif
                            @elseif($fieldConfig['type'] === 'date')
                                <input type="date" 
                                       class="board-form-control" 
                                       id="custom_field_{{ $fieldConfig['name'] }}" 
                                       name="custom_field_{{ $fieldConfig['name'] }}" 
                                       value="{{ old('custom_field_' . $fieldConfig['name']) }}"
                                       @if($fieldConfig['required']) required @endif>
                            @elseif($fieldConfig['type'] === 'editor')
                                <textarea class="board-form-control board-form-textarea summernote-editor" 
                                          id="custom_field_{{ $fieldConfig['name'] }}" 
                                          name="custom_field_{{ $fieldConfig['name'] }}" 
                                          rows="10"
                                          @if($fieldConfig['required']) required @endif>{{ old('custom_field_' . $fieldConfig['name']) }}</textarea>
                            @endif
                            
                            @if($fieldConfig['max_length'] && in_array($fieldConfig['type'], ['text']))
                                <small class="board-form-text">최대 {{ $fieldConfig['max_length'] }}자 (영어 기준)까지 입력 가능합니다.</small>
                            @endif
                        </div>
                    @endforeach
                @endif

                @if($board->isFieldEnabled('author_name'))
                <div class="board-form-group">
                    <label for="author_name" class="board-form-label">
                        작성자
                        @if($board->isFieldRequired('author_name'))
                            <span class="required">*</span>
                        @endif
                    </label>
                    <input type="text" class="board-form-control" id="author_name" name="author_name" value="{{ old('author_name') }}" @if($board->isFieldRequired('author_name')) required @endif>
                </div>
                @endif

                @if($board->isFieldEnabled('password'))
                <div class="board-form-group">
                    <label for="password" class="board-form-label">
                        비밀번호
                        @if($board->isFieldRequired('password'))
                            <span class="required">*</span>
                        @endif
                    </label>
                    <input type="password" class="board-form-control" id="password" name="password" @if($board->isFieldRequired('password')) required @endif>
                    <small class="board-form-text">게시글 수정/삭제 시 사용할 비밀번호를 입력하세요.</small>
                </div>
                @endif

                @if($board->isFieldEnabled('is_secret'))
                <div class="board-form-group">
                    <div class="board-checkbox-item">
                        <input type="checkbox" 
                               class="board-checkbox-input" 
                               id="is_secret" 
                               name="is_secret" 
                               value="1" 
                               @checked(old('is_secret') == '1')>
                        <label for="is_secret" class="board-form-label">
                            <i class="fas fa-lock"></i> 비밀글
                        </label>
                    </div>
                    <small class="board-form-text">체크하면 본인만 조회할 수 있습니다.</small>
                </div>
                @endif

                @if($board->enable_sorting)
                <div class="board-form-group">
                    <label for="sort_order" class="board-form-label">정렬 순서</label>
                    <input type="number" class="board-form-control" id="sort_order" name="sort_order" value="{{ old('sort_order', $nextSortOrder ?? 0) }}" min="0">
                    <small class="board-form-text">숫자가 클수록 위에 표시됩니다.</small>
                </div>
                @endif

                <div class="board-form-group">
                    <div class="board-checkbox-item">
                        <input type="checkbox" 
                               class="board-checkbox-input" 
                               id="is_active" 
                               name="is_active" 
                               value="1" 
                               @checked(old('is_active', true) == '1')>
                        <label for="is_active" class="board-form-label">
                            노출여부
                        </label>
                    </div>
                    <small class="board-form-text">체크하면 게시글이 노출됩니다.</small>
                </div>

                @if($board->isFieldEnabled('attachments'))
                <div class="board-form-group">
                    <label class="board-form-label">
                        첨부파일
                        @if($board->isFieldRequired('attachments'))
                            <span class="required">*</span>
                        @endif
                    </label>
                    <div class="board-file-upload">
                        <div class="board-file-input-wrapper">
                            <input type="file" class="board-file-input " id="attachments" name="attachments[]" multiple accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.xls,.xlsx,.txt,.zip,.rar" @if($board->isFieldRequired('attachments')) required @endif>
                            <div class="board-file-input-content">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <span class="board-file-input-text">파일을 선택하거나 여기로 드래그하세요</span>
                                <span class="board-file-input-subtext">최대 5개, 각 파일 10MB 이하</span>
                            </div>
                        </div>
                        <div class="board-file-preview" id="filePreview"></div>
                    </div>
                </div>
                @endif

                <div class="board-form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> 저장
                    </button>
                    <a href="{{ route('backoffice.board-posts.index', $board->slug ?? 'notice') }}" class="btn btn-secondary">취소</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <!-- jQuery, Bootstrap, Summernote JS (순서 중요!) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
    <script src="{{ asset('js/backoffice/board-post-form.js') }}"></script>
@endsection
