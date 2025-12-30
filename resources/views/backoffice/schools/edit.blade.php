@extends('backoffice.layouts.app')

@section('title', '학교 수정')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/backoffice/group-programs.css') }}">
<link rel="stylesheet" href="{{ asset('css/backoffice/member-groups.css') }}">
@endsection

@section('content')
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
    <div class="board-header">
        <a href="{{ route('backoffice.schools.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> 목록으로
        </a>
    </div>

    <div class="board-card">
<div class="board-card-body">
            <form action="{{ route('backoffice.schools.update', $school) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- 구분 -->
                <div class="board-form-group">
                    <label class="board-form-label">구분 <span class="required">*</span></label>
                    <div class="board-radio-group">
                        <div class="board-radio-item">
                            <input type="radio" id="source_type_api" name="source_type" value="api" 
                                   class="board-radio-input" @checked(old('source_type', $school->source_type) == 'api') required>
                            <label for="source_type_api">학교정보</label>
                        </div>
                        <div class="board-radio-item">
                            <input type="radio" id="source_type_user" name="source_type" value="user_registration" 
                                   class="board-radio-input" @checked(old('source_type', $school->source_type) == 'user_registration')>
                            <label for="source_type_user">사용자 등록</label>
                        </div>
                        <div class="board-radio-item">
                            <input type="radio" id="source_type_admin" name="source_type" value="admin_registration" 
                                   class="board-radio-input" @checked(old('source_type', $school->source_type) == 'admin_registration')>
                            <label for="source_type_admin">관리자 등록</label>
                        </div>
                    </div>
                    @error('source_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- 시/도 -->
                <div class="board-form-group">
                    <label for="city" class="board-form-label">시/도</label>
                    <select class="board-form-control @error('city') is-invalid @enderror" 
                            id="city" name="city">
                        <option value="">선택하세요</option>
                        @foreach($cities as $cityName)
                            <option value="{{ $cityName }}" @selected(old('city', $school->city) == $cityName)>{{ $cityName }}</option>
                        @endforeach
                    </select>
                    @error('city')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- 시/군/구 -->
                <div class="board-form-group">
                    <label for="district" class="board-form-label">시/군/구</label>
                    <select class="board-form-control @error('district') is-invalid @enderror" 
                            id="district" name="district">
                        <option value="">선택하세요</option>
                        @foreach($districts as $districtName)
                            <option value="{{ $districtName }}" @selected(old('district', $school->district) == $districtName)>{{ $districtName }}</option>
                        @endforeach
                    </select>
                    @error('district')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- 학교급 -->
                <div class="board-form-group">
                    <label for="school_level" class="board-form-label">학교급</label>
                    <select class="board-form-control @error('school_level') is-invalid @enderror" 
                            id="school_level" name="school_level">
                        <option value="">선택하세요</option>
                        @foreach($schoolLevels as $key => $name)
                            <option value="{{ $key }}" @selected(old('school_level', $school->school_level) == $key)>{{ $name }}</option>
                        @endforeach
                    </select>
                    @error('school_level')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- 학교명 -->
                <div class="board-form-group">
                    <label for="school_name" class="board-form-label">학교명 <span class="required">*</span></label>
                    <input type="text" class="board-form-control @error('school_name') is-invalid @enderror" 
                           id="school_name" name="school_name" value="{{ old('school_name', $school->school_name) }}" required>
                    @error('school_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- 버튼 -->
                <div class="board-form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> 저장
                    </button>
                    <a href="{{ route('backoffice.schools.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> 취소
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const citySelect = document.getElementById('city');
    const districtSelect = document.getElementById('district');

    // 시/도 선택 시 시/군/구 목록 업데이트
    if (citySelect && districtSelect) {
        citySelect.addEventListener('change', function() {
            const selectedCity = this.value;
            
            // 시/군/구 옵션 초기화
            districtSelect.innerHTML = '<option value="">선택하세요</option>';
            
            if (selectedCity) {
                // AJAX로 시/군/구 목록 가져오기
                const currentDistrict = '{{ old("district", $school->district) }}';
                fetch('{{ route("backoffice.schools.get-districts") }}?city=' + encodeURIComponent(selectedCity), {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.districts && data.districts.length > 0) {
                        data.districts.forEach(function(district) {
                            const option = document.createElement('option');
                            option.value = district;
                            option.textContent = district;
                            // 기존 선택값 복원
                            if (district === currentDistrict) {
                                option.selected = true;
                            }
                            districtSelect.appendChild(option);
                        });
                    }
                })
                .catch(error => {
                    console.error('시/군/구 목록을 가져오는 중 오류가 발생했습니다:', error);
                });
            }
        });
    }
});
</script>
@endsection

