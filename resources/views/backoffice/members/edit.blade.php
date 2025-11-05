@extends('backoffice.layouts.app')

@section('title', '회원 수정')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/common/buttons.css') }}">
<link rel="stylesheet" href="{{ asset('css/backoffice/admins.css') }}">
<link rel="stylesheet" href="{{ asset('css/backoffice/users.css') }}">
@endsection

@section('content')
<div class="admin-form-container">
    <div class="form-header">      
        <a href="{{ route('backoffice.members.index') }}" class="btn btn-secondary">
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
                    <h6>회원 정보</h6>
                </div>
                <div class="admin-card-body">
                    <form id="memberForm" action="{{ route('backoffice.members.update', $member) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="program-section">
                            <div class="section-title">기본 정보</div>
                            <div class="form-grid grid-2">
                                <div>
                                    <label>회원구분</label>
                                    <div class="radio-group">
                                        <label class="radio-label">
                                            <input type="radio" name="member_type" value="teacher" class="member_type_tab" @checked(old('member_type', $member->member_type) == 'teacher')>
                                            <span>교사</span>
                                        </label>
                                        <label class="radio-label">
                                            <input type="radio" name="member_type" value="student" class="member_type_tab member_type_student" @checked(old('member_type', $member->member_type) == 'student')>
                                            <span>학생</span>
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="login_id">아이디</label>
                                    <input type="text" id="login_id" name="login_id" value="{{ old('login_id', $member->login_id) }}" required readonly>
                                </div>
                                
                                <div class="form-group">
                                    <label for="member_group_id">회원 그룹</label>
                                    <select id="member_group_id" name="member_group_id">
                                        <option value="">그룹을 선택하세요</option>
                                        @foreach($memberGroups as $group)
                                            <option value="{{ $group->id }}" @selected(old('member_group_id', $member->member_group_id) == $group->id)>
                                                {{ $group->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="password">비밀번호</label>
                                    <input type="password" id="password" name="password">
                                    <small class="form-text">비밀번호 변경 시에만 입력해주세요.</small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="password_confirmation">비밀번호 확인</label>
                                    <input type="password" id="password_confirmation" name="password_confirmation">
                                </div>
                                
                                <div class="form-group">
                                    <label for="name">이름</label>
                                    <input type="text" id="name" name="name" value="{{ old('name', $member->name) }}" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="birth_date">생년월일</label>
                                    <input type="date" id="birth_date" name="birth_date" value="{{ old('birth_date', $member->birth_date?->format('Y-m-d')) }}">
                                </div>
                                
                                <div>
                                    <label>성별</label>
                                    <div class="radio-group">
                                        <label class="radio-label">
                                            <input type="radio" name="gender" value="male" @checked(old('gender', $member->gender) == 'male')>
                                            <span>남자</span>
                                        </label>
                                        <label class="radio-label">
                                            <input type="radio" name="gender" value="female" @checked(old('gender', $member->gender) == 'female')>
                                            <span>여자</span>
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="form-group student-only">
                                    <label for="contact">학생 연락처</label>
                                    <input type="text" id="contact" name="contact" value="{{ old('contact', $member->contact) }}">
                                </div>
                                
                                <div class="form-group student-only">
                                    <label for="parent_contact">보호자 연락처</label>
                                    <input type="text" id="parent_contact" name="parent_contact" value="{{ old('parent_contact', $member->parent_contact) }}">
                                </div>
                                
                                <div class="form-group teacher-only">
                                    <label for="contact_teacher">연락처</label>
                                    <input type="text" id="contact_teacher" name="contact" value="{{ old('contact', $member->contact) }}">
                                </div>
                                
                                <div class="form-group">
                                    <label for="email">이메일</label>
                                    <input type="email" id="email" name="email" value="{{ old('email', $member->email) }}" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="joined_at">가입일</label>
                                    <input type="text" value="{{ $member->joined_at ? $member->joined_at->format('Y.m.d') : '' }}" readonly>
                                </div>
                                
                                <div>
                                    <label>수신동의</label>
                                    <div class="checkbox-group">
                                        <label class="checkbox-label">
                                            <input type="checkbox" name="email_consent" value="1" @checked(old('email_consent', $member->email_consent))>
                                            <span>EMAIL</span>
                                        </label>
                                        <label class="checkbox-label">
                                            <input type="checkbox" name="sms_consent" value="1" @checked(old('sms_consent', $member->sms_consent))>
                                            <span>SMS</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="program-section">
                            <div class="section-title">소속 정보</div>
                            <div class="form-grid grid-2">
                                <div class="form-group">
                                    <label for="city">시</label>
                                    <input type="text" id="city" name="city" value="{{ old('city', $member->city) }}">
                                </div>
                                
                                <div class="form-group">
                                    <label for="city2">도</label>
                                    <input type="text" id="city2" name="city2" value="{{ old('city2', $member->city) }}">
                                </div>
                                
                                <div class="form-group">
                                    <label for="school_name">학교명</label>
                                    <input type="hidden" id="school_id" name="school_id" value="{{ old('school_id', $member->school_id) }}">
                                    <div class="school-search-wrapper">
                                        <input type="text" id="school_name" name="school_name" value="{{ old('school_name', $member->school_name) }}" class="school-search-input" readonly>
                                        <button type="button" id="school-search-btn" class="btn btn-secondary btn-sm">
                                            <i class="fas fa-search"></i> 검색
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="student-only grid-span-2">
                                    <div class="grade-class-wrapper">
                                        <div class="grade-class-item">
                                            <div class="form-group">
                                                <label>학년</label>
                                                <select id="grade" name="grade">
                                                    <option value="">학년</option>
                                                    <option value="1" @selected(old('grade', $member->grade) == 1)>1</option>
                                                    <option value="2" @selected(old('grade', $member->grade) == 2)>2</option>
                                                    <option value="3" @selected(old('grade', $member->grade) == 3)>3</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="grade-class-item">
                                            <div class="form-group">
                                                <label>반</label>
                                                <select id="class_number" name="class_number">
                                                    <option value="">반</option>
                                                    @for($i = 1; $i <= 20; $i++)
                                                        <option value="{{ $i }}" @selected(old('class_number', $member->class_number) == $i)>{{ $i }}</option>
                                                    @endfor
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="program-section">
                            <div class="section-title">신청 현황</div>
                            <div class="table-responsive">
                                <table class="board-table">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>신청 번호</th>
                                            <th>신청 상태</th>
                                            <th>신청자명</th>
                                            <th>학교명</th>
                                            <th>교육 유형</th>
                                            <th>프로그램명</th>
                                            <th>신청 인원</th>
                                            <th>접수 상태</th>
                                            <th>결제 수단</th>
                                            <th>교육료</th>
                                            <th>교육일</th>
                                            <th>신청일시</th>
                                            <th>출석 여부</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr style="border: none;">
                                            <td colspan="14" class="text-center" style="padding: 40px 20px; border: none !important; border-bottom: none !important;">신청 내역이 없습니다.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="program-section">
                            <div class="section-title">기타</div>
                            <div class="form-group">
                                <label>메모</label>
                                <textarea id="memo" name="memo" rows="3">{{ old('memo', $member->memo) }}</textarea>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> 저장
                            </button>
                            <a href="{{ route('backoffice.members.index') }}" class="btn btn-secondary">
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

<!-- 학교 검색 모달 -->
<div id="school-search-modal" class="modal" style="display: none;">
    <div class="modal-backdrop" onclick="this.closest('.modal').style.display='none'"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h5>학교 검색</h5>
            <button type="button" class="close-modal" onclick="this.closest('.modal').style.display='none'">&times;</button>
        </div>
        <div class="modal-body">
            <form id="school-search-form" data-url="{{ route('backoffice.schools.search') }}">
                <div class="form-group">
                    <label for="search_school_name">학교명</label>
                    <input type="text" id="search_school_name" name="search_keyword" class="form-control" placeholder="학교명을 입력하세요">
                </div>
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="search_city">시/도</label>
                        <input type="text" id="search_city" name="city" class="form-control" placeholder="시/도">
                    </div>
                    <div class="form-group col-md-4">
                        <label for="search_district">시/군/구</label>
                        <input type="text" id="search_district" name="district" class="form-control" placeholder="시/군/구">
                    </div>
                    <div class="form-group col-md-4">
                        <label for="search_school_level">학교급</label>
                        <select id="search_school_level" name="school_level" class="form-control">
                            <option value="">전체</option>
                            <option value="elementary">초등학교</option>
                            <option value="middle">중학교</option>
                            <option value="high">고등학교</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> 검색
                    </button>
                </div>
            </form>
            <div class="table-responsive mt-3">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>학교명</th>
                            <th>시/도</th>
                            <th>시/군/구</th>
                            <th>학교급</th>
                            <th>관리</th>
                        </tr>
                    </thead>
                    <tbody id="school-search-results">
                        <tr>
                            <td colspan="5" class="text-center">검색 조건을 입력하고 검색 버튼을 클릭하세요.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div id="school-search-pagination" class="mt-3 text-center"></div>
        </div>
    </div>
</div>

@section('scripts')
<script src="{{ asset('js/backoffice/members.js') }}"></script>
<script src="{{ asset('js/backoffice/school-search.js') }}"></script>
@endsection
