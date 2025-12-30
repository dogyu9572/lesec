@extends('backoffice.layouts.app')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/backoffice/boards.css') }}">
@endsection

@section('title', $pageTitle ?? '게시판 템플릿 생성')

@section('content')
<div class="board-container">
    <div class="board-header">
        <a href="{{ route('backoffice.board-templates.index') }}" class="btn btn-secondary">
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

            <form action="{{ route('backoffice.board-templates.store') }}" method="POST">
                @csrf

                <!-- 기본 정보 -->
                <div class="board-form-row">
                    <div class="board-form-col board-form-col-6">
                        <div class="board-form-group">
                            <label for="name" class="board-form-label">템플릿 이름 <span class="required">*</span></label>
                            <input type="text" class="board-form-control" id="name" name="name" value="{{ old('name') }}" required>
                        </div>
                    </div>

                    <div class="board-form-col board-form-col-6">
                        <div class="board-form-group">
                            <label for="skin_id" class="board-form-label">스킨 <span class="required">*</span></label>
                            <select class="board-form-control" id="skin_id" name="skin_id" required>
                                <option value="">스킨을 선택하세요</option>
                                @foreach ($skins as $skin)
                                    <option value="{{ $skin->id }}" @selected(old('skin_id') == $skin->id)>
                                        {{ $skin->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="board-form-group">
                    <label for="description" class="board-form-label">설명</label>
                    <textarea class="board-form-control board-form-textarea" id="description" name="description" rows="2">{{ old('description') }}</textarea>
                </div>

                <hr style="margin: 30px 0; border: 0; border-top: 1px solid #e9ecef;">

                <!-- 기본 필드 설정 -->
                <div class="board-form-group">
                    <label class="board-form-label">기본 필드 설정</label>
                    <small class="board-form-text">게시글에서 사용할 기본 필드를 선택하세요.</small>
                    
                    <div class="field-config-wrapper" style="margin-top: 15px;">
                        @php
                            $defaultFields = [
                                'title' => ['label' => '제목', 'enabled' => true, 'required' => true],
                                'content' => ['label' => '내용', 'enabled' => true, 'required' => true],
                                'attachments' => ['label' => '첨부파일', 'enabled' => true, 'required' => false],
                                'category' => ['label' => '카테고리', 'enabled' => false, 'required' => false],
                                'author_name' => ['label' => '작성자', 'enabled' => false, 'required' => false],
                                'password' => ['label' => '비밀번호', 'enabled' => false, 'required' => false],                                
                                'is_secret' => ['label' => '비밀글', 'enabled' => false, 'required' => false],
                                'created_at' => ['label' => '등록일', 'enabled' => false, 'required' => false],
                            ];
                        @endphp

                        @foreach($defaultFields as $fieldName => $fieldInfo)
                            <div class="field-config-item">
                                <div class="field-config-checkbox">
                                    <input type="checkbox" 
                                           id="field_{{ $fieldName }}_enabled" 
                                           name="field_{{ $fieldName }}_enabled" 
                                           value="1"
                                           @checked(old('field_' . $fieldName . '_enabled', $fieldInfo['enabled']))>
                                    <label for="field_{{ $fieldName }}_enabled">{{ $fieldInfo['label'] }}</label>
                                </div>
                                <div class="field-config-required">
                                    <input type="checkbox" 
                                           id="field_{{ $fieldName }}_required" 
                                           name="field_{{ $fieldName }}_required" 
                                           value="1"
                                           @checked(old('field_' . $fieldName . '_required', $fieldInfo['required']))>
                                    <label for="field_{{ $fieldName }}_required">필수</label>
                                </div>
                                <input type="hidden" name="field_{{ $fieldName }}_label" value="{{ $fieldInfo['label'] }}">
                            </div>
                        @endforeach
                    </div>
                </div>

                <hr style="margin: 30px 0; border: 0; border-top: 1px solid #e9ecef;">

                <!-- 커스텀 필드 설정 -->
                <div class="board-form-group">
                    <label class="board-form-label">커스텀 필드 설정</label>
                    <small class="board-form-text">
                        게시글 작성 시 추가로 입력받을 필드들을 설정할 수 있습니다. 
                        select, radio, checkbox 타입의 경우 옵션을 쉼표(,)로 구분하여 입력하세요.
                    </small>
                    <div class="custom-fields-container">
                        <div class="custom-fields-list" id="customFieldsList">
                            <!-- 커스텀 필드들이 동적으로 추가됩니다 -->
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="addCustomField()">
                            <i class="fas fa-plus"></i> 커스텀 필드 추가
                        </button>
                    </div>
                </div>

                <hr style="margin: 30px 0; border: 0; border-top: 1px solid #e9ecef;">

                <!-- 기능 설정 -->
                <div class="board-form-row">
                    <div class="board-form-col board-form-col-3">
                        <div class="board-form-group">
                            <div class="board-checkbox-group">
                                <input type="checkbox" id="enable_notice" name="enable_notice" value="1" @checked(old('enable_notice', false))>
                                <label for="enable_notice">공지사항 기능 사용</label>
                            </div>
                        </div>
                    </div>
                    <div class="board-form-col board-form-col-3">
                        <div class="board-form-group">
                            <div class="board-checkbox-group">
                                <input type="checkbox" id="enable_sorting" name="enable_sorting" value="1" @checked(old('enable_sorting'))>
                                <label for="enable_sorting">정렬 기능 사용</label>
                            </div>
                        </div>
                    </div>
                    <div class="board-form-col board-form-col-3">
                        <div class="board-form-group">
                            <div class="board-checkbox-group">
                                <input type="checkbox" id="enable_category" name="enable_category" value="1" @checked(old('enable_category', false))>
                                <label for="enable_category">카테고리 기능 사용</label>
                            </div>
                        </div>
                    </div>
                    <div class="board-form-col board-form-col-3">
                        <div class="board-form-group">
                            <div class="board-checkbox-group">
                                <input type="checkbox" id="is_single_page" name="is_single_page" value="1" @checked(old('is_single_page', false))>
                                <label for="is_single_page">단일 페이지 모드</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 카테고리 그룹 설정 (카테고리 기능 사용 시에만 표시) -->
                <div class="board-form-group" id="category_id_wrapper" style="display: none;">
                    <label for="category_id" class="board-form-label">카테고리 그룹</label>
                    <select class="board-form-control" id="category_id" name="category_id">
                        <option value="">카테고리 그룹 선택</option>
                        @foreach($categoryGroups as $group)
                            <option value="{{ $group->id }}" @selected(old('category_id') == $group->id)>
                                {{ $group->name }}
                            </option>
                        @endforeach
                    </select>
                    <small class="board-form-text">카테고리를 사용하려면 그룹을 선택하세요. 카테고리 관리 메뉴에서 등록된 그룹(depth=0)들이 표시됩니다.</small>
                </div>

                <!-- 목록 및 권한 설정 -->
                <div class="board-form-row">
                    <div class="board-form-col board-form-col-6">
                        <div class="board-form-group">
                            <label for="list_count" class="board-form-label">페이지당 글 수</label>
                            <select class="board-form-control" id="list_count" name="list_count">
                                <option value="10" @selected(old('list_count', 15) == 10)>10</option>
                                <option value="15" @selected(old('list_count', 15) == 15)>15</option>
                                <option value="20" @selected(old('list_count', 15) == 20)>20</option>
                                <option value="30" @selected(old('list_count', 15) == 30)>30</option>
                                <option value="50" @selected(old('list_count', 15) == 50)>50</option>
                                <option value="100" @selected(old('list_count', 15) == 100)>100</option>
                                <option value="999999" @selected(old('list_count', 15) == 999999)>전체</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="board-form-row">
                    <div class="board-form-col board-form-col-4">
                        <div class="board-form-group">
                            <label class="board-form-label">읽기 권한 <span class="required">*</span></label>
                            <div class="board-radio-group">
                                <div class="board-radio-item">
                                    <input type="radio" id="permission_read_all" name="permission_read" value="all" @checked(old('permission_read', 'all') == 'all')>
                                    <label for="permission_read_all">모두</label>
                                </div>
                                <div class="board-radio-item">
                                    <input type="radio" id="permission_read_member" name="permission_read" value="member" @checked(old('permission_read') == 'member')>
                                    <label for="permission_read_member">회원만</label>
                                </div>
                                <div class="board-radio-item">
                                    <input type="radio" id="permission_read_admin" name="permission_read" value="admin" @checked(old('permission_read') == 'admin')>
                                    <label for="permission_read_admin">관리자만</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="board-form-col board-form-col-4">
                        <div class="board-form-group">
                            <label class="board-form-label">쓰기 권한 <span class="required">*</span></label>
                            <div class="board-radio-group">
                                <div class="board-radio-item">
                                    <input type="radio" id="permission_write_all" name="permission_write" value="all" @checked(old('permission_write') == 'all')>
                                    <label for="permission_write_all">모두</label>
                                </div>
                                <div class="board-radio-item">
                                    <input type="radio" id="permission_write_member" name="permission_write" value="member" @checked(old('permission_write', 'member') == 'member')>
                                    <label for="permission_write_member">회원만</label>
                                </div>
                                <div class="board-radio-item">
                                    <input type="radio" id="permission_write_admin" name="permission_write" value="admin" @checked(old('permission_write') == 'admin')>
                                    <label for="permission_write_admin">관리자만</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="board-form-col board-form-col-4">
                        <div class="board-form-group">
                            <label class="board-form-label">댓글 권한 <span class="required">*</span></label>
                            <div class="board-radio-group">
                                <div class="board-radio-item">
                                    <input type="radio" id="permission_comment_all" name="permission_comment" value="all" @checked(old('permission_comment') == 'all')>
                                    <label for="permission_comment_all">모두</label>
                                </div>
                                <div class="board-radio-item">
                                    <input type="radio" id="permission_comment_member" name="permission_comment" value="member" @checked(old('permission_comment', 'member') == 'member')>
                                    <label for="permission_comment_member">회원만</label>
                                </div>
                                <div class="board-radio-item">
                                    <input type="radio" id="permission_comment_admin" name="permission_comment" value="admin" @checked(old('permission_comment') == 'admin')>
                                    <label for="permission_comment_admin">관리자만</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 상태 설정 -->
                <div class="board-form-group">
                    <div class="board-checkbox-group">
                        <input type="checkbox" id="is_active" name="is_active" value="1" @checked(old('is_active', true))>
                        <label for="is_active">활성화</label>
                    </div>
                </div>

                <div class="board-form-actions">
                    <button type="submit" class="btn btn-primary">저장</button>
                    <a href="{{ route('backoffice.board-templates.index') }}" class="btn btn-secondary">취소</a>
                </div>
            </form>
        </div>
    </div>
</div>

@section('scripts')
<script src="{{ asset('js/backoffice/board-templates.js') }}"></script>
@endsection
@endsection

