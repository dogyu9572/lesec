@extends('backoffice.layouts.app')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/common/buttons.css') }}">
<link rel="stylesheet" href="{{ asset('css/backoffice/summernote-custom.css') }}">
<link rel="stylesheet" href="{{ asset('css/backoffice/programs.css') }}">
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
@endsection

@section('title', '프로그램 관리')

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
<script src="{{ asset('js/backoffice/board-post-form.js') }}"></script>
@endsection

@section('content')
@if (session('success'))
    <div class="alert alert-success board-hidden-alert">
        {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger board-hidden-alert">
        {{ session('error') }}
    </div>
@endif

@if ($errors->any())
    <div class="board-alert board-alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="board-container">   
    <!-- 상단 탭 -->
    <div class="program-tabs">
        @foreach($types as $typeKey => $typeName)
            <a href="{{ route('backoffice.programs.index', ['type' => $typeKey]) }}" 
               class="program-tab {{ $currentType === $typeKey ? 'active' : '' }}">
                {{ $typeName }}
            </a>
        @endforeach
    </div>

    <div class="board-card">       
        <div class="board-card-body">
            <form action="{{ route('backoffice.programs.update', $program) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- 프로그램 개요 -->
                <div class="program-section">
                    <div class="section-title">프로그램 개요</div>
                    
                    <div class="board-form-row">
                        <div class="board-form-col board-form-col-12">
                            <div class="board-form-group">
                                <label for="host" class="board-form-label">주최</label>
                                <input type="text" class="board-form-control @error('host') is-invalid @enderror" 
                                       id="host" name="host" value="{{ old('host', $program->host) }}">
                                @error('host')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="board-form-row">
                        <div class="board-form-col board-form-col-6">
                            <div class="board-form-group">
                                <label for="period_start" class="board-form-label">기간 시작일</label>
                                <input type="date" class="board-form-control @error('period_start') is-invalid @enderror" 
                                       id="period_start" name="period_start" 
                                       value="{{ old('period_start', $program->period_start ? $program->period_start->format('Y-m-d') : '') }}">
                                @error('period_start')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="board-form-col board-form-col-6">
                            <div class="board-form-group">
                                <label for="period_end" class="board-form-label">기간 종료일</label>
                                <input type="date" class="board-form-control @error('period_end') is-invalid @enderror" 
                                       id="period_end" name="period_end" 
                                       value="{{ old('period_end', $program->period_end ? $program->period_end->format('Y-m-d') : '') }}">
                                @error('period_end')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="board-form-row">
                        <div class="board-form-col board-form-col-12">
                            <div class="board-form-group">
                                <label for="location" class="board-form-label">장소</label>
                                <input type="text" class="board-form-control @error('location') is-invalid @enderror" 
                                       id="location" name="location" value="{{ old('location', $program->location) }}">
                                @error('location')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="board-form-row">
                        <div class="board-form-col board-form-col-12">
                            <div class="board-form-group">
                                <label for="target" class="board-form-label">대상</label>
                                <input type="text" class="board-form-control @error('target') is-invalid @enderror" 
                                       id="target" name="target" value="{{ old('target', $program->target) }}">
                                @error('target')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 상세내용 -->
                <div class="program-section">
                    <div class="section-title">상세 내용</div>
                    <div class="board-form-group">
                        <label for="detail_content" class="board-form-label">상세 내용</label>
                        <textarea class="board-form-control summernote-editor @error('detail_content') is-invalid @enderror" 
                                  id="detail_content" name="detail_content" rows="10">{{ old('detail_content', $program->detail_content) }}</textarea>
                        @error('detail_content')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- 기타 안내 -->
                <div class="program-section">
                    <div class="section-title">기타 안내</div>
                    <div class="board-form-group">
                        <label for="other_info" class="board-form-label">기타 안내</label>
                        <textarea class="board-form-control summernote-editor @error('other_info') is-invalid @enderror" 
                                  id="other_info" name="other_info" rows="10">{{ old('other_info', $program->other_info) }}</textarea>
                        @error('other_info')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- 활성화 여부 -->
                <div class="board-form-group">
                    <label class="board-form-label">활성화 여부</label>
                    <div class="board-radio-group">
                        <div class="board-radio-item">
                            <input type="radio" id="is_active_1" name="is_active" value="1" class="board-radio-input" 
                                   @checked(old('is_active', $program->is_active) == '1' || old('is_active', $program->is_active) === true)>
                            <label for="is_active_1">Y</label>
                        </div>
                        <div class="board-radio-item">
                            <input type="radio" id="is_active_0" name="is_active" value="0" class="board-radio-input" 
                                   @checked(old('is_active', $program->is_active) == '0' || old('is_active', $program->is_active) === false)>
                            <label for="is_active_0">N</label>
                        </div>
                    </div>
                </div>

                <div class="board-form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> 저장
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
