# 나이스엠교육센터 - 데이터베이스 설계 분석

> **기획서 기반 데이터 구조 설계 가이드**  
> 작성일: 2025-01-27  
> 기획서: 나이스엠교육센터 관리자 화면설계서 (67페이지)

## 📋 개요

이 문서는 나이스엠교육센터 관리자 화면 기획서를 분석하여 교육센터 특성에 맞는 데이터베이스 설계를 제안합니다. **개인/단체 신청, 학교 관리, 프로그램 일정 관리** 등 교육센터의 핵심 기능을 지원하는 데이터 구조를 다룹니다.

---

## 🎯 핵심 설계 원칙

1. **개인/단체 신청 구분**: 모든 신청은 개인용/단체용으로 명확히 구분
2. **학교 정보 관리**: 별도 테이블로 학교 정보를 체계적으로 관리
3. **일정별 정원 관리**: 프로그램은 여러 날짜에 운영되며, 일정별로 정원 관리
4. **승인 프로세스**: 모든 신청은 pending → approved/rejected 상태 관리
5. **명단 관리**: 단체 신청 시 참가자 상세 명단 별도 관리

---

## 📊 테이블 설계 상세

### 1️⃣ 회원(users) 테이블 - 교육 신청을 위한 필수 필드

#### 현재 구조 (확인됨)
```sql
-- 기존 필드
login_id, name, email, password
role, is_active, last_login_at
department, position, contact
```

#### ⚠️ 교육센터 특성상 추가 필요 필드

```sql
-- 신청자 유형 구분
member_type ENUM('individual', 'group', 'school') DEFAULT 'individual'

-- 학교/기관 정보 (단체 신청 시)
organization_name VARCHAR(255) -- 소속 학교/기관명
organization_type VARCHAR(100) -- 학교급 (초등/중등/고등)
school_id BIGINT -- schools 테이블 FK

-- 연령대/학년 정보 (프로그램 타겟팅용)
grade VARCHAR(50) -- 학년 정보
age_group VARCHAR(50) -- 연령대

-- 주소 정보 (통계/지역별 분석용)
address VARCHAR(255) -- 주소
zipcode VARCHAR(20) -- 우편번호

-- 추가 연락처
phone VARCHAR(50) -- 휴대폰 (contact와 구분)
emergency_contact VARCHAR(50) -- 비상연락처
emergency_contact_relation VARCHAR(50) -- 비상연락처 관계

-- 마케팅 동의
marketing_agree BOOLEAN DEFAULT false
sms_agree BOOLEAN DEFAULT false
email_agree BOOLEAN DEFAULT false

-- 가입 경로
signup_route VARCHAR(100) -- 가입 경로 (통계용)
```

**중요 판단 기준:**
- **개인 신청 vs 단체 신청** 구분 필수 (기획서에 명확히 분리됨)
- **학교 정보** 별도 테이블 관리 (학교 관리 메뉴 존재)
- **주소 정보** 교육센터 방문/교통편 안내에 필요

---

### 2️⃣ 프로그램(programs) 테이블 - 핵심 설계

```sql
CREATE TABLE programs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    
    -- 기본 정보
    title VARCHAR(255) NOT NULL,
    description TEXT,
    category_id BIGINT, -- 프로그램 분류 (categories 테이블)
    
    -- 프로그램 유형
    program_type ENUM('individual', 'group') NOT NULL, -- 개인/단체
    target_age_group VARCHAR(100), -- 대상 연령/학년
    
    -- 정원 관리
    capacity INT, -- 총 정원
    min_participants INT, -- 최소 인원 (단체용)
    max_participants INT, -- 최대 인원 (단체용)
    
    -- 운영 기간
    operation_start_date DATE, -- 운영 시작일
    operation_end_date DATE, -- 운영 종료일
    
    -- 신청 기간
    application_start_date DATETIME, -- 신청 시작일시
    application_end_date DATETIME, -- 신청 마감일시
    
    -- 운영 시간
    operation_time VARCHAR(100), -- 운영 시간대 (예: 09:00-12:00)
    duration INT, -- 소요 시간 (분)
    
    -- 비용
    price DECIMAL(10, 2) DEFAULT 0, -- 가격
    is_free BOOLEAN DEFAULT true, -- 무료 여부
    
    -- 장소
    location VARCHAR(255), -- 장소
    venue VARCHAR(255), -- 세부 장소 (예: 3층 체험실)
    
    -- 담당자
    manager_id BIGINT, -- 담당 관리자
    instructor VARCHAR(100), -- 강사명
    
    -- 상태
    status ENUM('draft', 'open', 'closed', 'full', 'cancelled') DEFAULT 'draft',
    is_active BOOLEAN DEFAULT true,
    
    -- 추가 정보
    materials TEXT, -- 준비물
    notes TEXT, -- 유의사항
    image VARCHAR(255), -- 대표 이미지
    
    -- 순서
    sort_order INT DEFAULT 0,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_program_type (program_type),
    INDEX idx_operation_dates (operation_start_date, operation_end_date),
    INDEX idx_application_dates (application_start_date, application_end_date),
    INDEX idx_status (status, is_active)
);
```

**⚠️ 중요 포인트:**
- **개인/단체 구분** 필수 (기획서에 명확히 구분됨)
- **신청 기간 vs 운영 기간** 구분 필요
- **정원 관리**: 단체는 최소/최대 인원, 개인은 총 정원
- **일정별 관리**: `program_schedules` 테이블 별도 필요

---

### 3️⃣ 프로그램 일정(program_schedules) 테이블

```sql
CREATE TABLE program_schedules (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    program_id BIGINT NOT NULL, -- programs FK
    
    -- 일정 정보
    schedule_date DATE NOT NULL, -- 실제 운영일
    start_time TIME NOT NULL, -- 시작 시간
    end_time TIME NOT NULL, -- 종료 시간
    
    -- 정원 관리 (일정별)
    capacity INT, -- 해당 일정 정원
    current_participants INT DEFAULT 0, -- 현재 신청 인원
    
    -- 상태
    status ENUM('available', 'full', 'cancelled') DEFAULT 'available',
    is_active BOOLEAN DEFAULT true,
    
    -- 특이사항
    notes TEXT,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE CASCADE,
    INDEX idx_program_date (program_id, schedule_date),
    INDEX idx_date_status (schedule_date, status)
);
```

**⚠️ 중요 포인트:**
- 프로그램은 **여러 날짜에 반복 운영** 가능
- **일정별로 정원 관리** 필요 (같은 프로그램도 날짜별로 신청 인원 다름)

---

### 4️⃣ 신청 관리(applications) 테이블 - 핵심 설계

```sql
CREATE TABLE applications (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    
    -- 신청자 정보
    user_id BIGINT NOT NULL, -- 신청자
    applicant_name VARCHAR(100), -- 신청자명 (user와 다를 수 있음)
    applicant_contact VARCHAR(50), -- 신청자 연락처
    applicant_email VARCHAR(255),
    
    -- 프로그램 정보
    program_id BIGINT NOT NULL,
    program_schedule_id BIGINT, -- 특정 일정 신청
    
    -- 신청 유형
    application_type ENUM('individual', 'group') NOT NULL,
    
    -- 단체 신청 정보
    organization_name VARCHAR(255), -- 단체/학교명
    organization_type VARCHAR(100), -- 학교급/기관 유형
    school_id BIGINT, -- schools 테이블 FK
    group_size INT, -- 신청 인원 수
    
    -- 참가자 정보 (개인)
    participant_name VARCHAR(100),
    participant_age INT,
    participant_grade VARCHAR(50),
    
    -- 신청 상태
    status ENUM('pending', 'approved', 'rejected', 'cancelled', 'completed') DEFAULT 'pending',
    
    -- 결제 정보
    payment_status ENUM('unpaid', 'paid', 'refunded') DEFAULT 'unpaid',
    payment_amount DECIMAL(10, 2),
    payment_date DATETIME,
    
    -- 추가 정보
    special_requests TEXT, -- 특이사항
    created_by_admin BOOLEAN DEFAULT false, -- 관리자가 대신 신청
    admin_notes TEXT, -- 관리자 메모
    
    -- 승인 정보
    approved_by BIGINT, -- 승인자
    approved_at DATETIME,
    rejected_reason TEXT,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (program_id) REFERENCES programs(id),
    FOREIGN KEY (program_schedule_id) REFERENCES program_schedules(id),
    FOREIGN KEY (school_id) REFERENCES schools(id),
    FOREIGN KEY (approved_by) REFERENCES users(id),
    
    INDEX idx_user_status (user_id, status),
    INDEX idx_program_status (program_id, status),
    INDEX idx_application_type (application_type),
    INDEX idx_created_at (created_at)
);
```

**⚠️ 핵심 체크포인트:**
1. **개인 신청**: 1명의 참가자 정보
2. **단체 신청**: 단체 정보 + 참가자 명단 (rosters 테이블 별도)
3. **승인 프로세스**: pending → approved/rejected 필요
4. **결제 정보**: 유료 프로그램 대비

---

### 5️⃣ 명단 관리(rosters) 테이블

```sql
CREATE TABLE rosters (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    application_id BIGINT NOT NULL, -- 단체 신청 FK
    
    -- 참가자 정보
    name VARCHAR(100) NOT NULL,
    gender ENUM('male', 'female', 'other'),
    age INT,
    grade VARCHAR(50), -- 학년
    
    -- 연락처
    contact VARCHAR(50),
    email VARCHAR(255),
    
    -- 건강/특이사항
    health_notes TEXT, -- 알레르기, 건강상 주의사항 등
    special_needs TEXT,
    
    -- 출결 관리
    attendance_status ENUM('present', 'absent', 'late') DEFAULT 'present',
    
    -- 순서
    sort_order INT DEFAULT 0,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE,
    INDEX idx_application (application_id),
    INDEX idx_attendance (attendance_status)
);
```

**⚠️ 중요 포인트:**
- 단체 신청 시 **엑셀 업로드로 명단 일괄 등록** 기능 고려
- **출결 관리** 필요 여부 확인

---

### 6️⃣ 학교 관리(schools) 테이블

```sql
CREATE TABLE schools (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    
    -- 학교 기본 정보
    name VARCHAR(255) NOT NULL,
    school_type ENUM('elementary', 'middle', 'high', 'university', 'other'), -- 학교급
    
    -- 연락 정보
    address VARCHAR(255),
    zipcode VARCHAR(20),
    phone VARCHAR(50),
    fax VARCHAR(50),
    email VARCHAR(255),
    website VARCHAR(255),
    
    -- 담당자 정보
    contact_person VARCHAR(100), -- 담당 교사명
    contact_person_phone VARCHAR(50),
    contact_person_email VARCHAR(255),
    
    -- 통계 정보
    total_students INT, -- 총 학생 수
    
    -- 비고
    notes TEXT,
    
    -- 상태
    is_active BOOLEAN DEFAULT true,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_school_type (school_type),
    INDEX idx_active (is_active)
);
```

**⚠️ 중요 포인트:**
- **학교 정보를 사전 등록**해두고, 신청 시 선택
- **학교별 신청 이력/통계** 관리 용이

---

### 7️⃣ 회원 그룹(user_groups) 테이블

```sql
CREATE TABLE user_groups (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    color VARCHAR(20), -- UI 표시용
    icon VARCHAR(100),
    
    -- 권한/혜택
    discount_rate DECIMAL(5, 2) DEFAULT 0, -- 할인율
    priority_booking BOOLEAN DEFAULT false, -- 우선 신청권
    
    -- 상태
    is_active BOOLEAN DEFAULT true,
    sort_order INT DEFAULT 0,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_active (is_active),
    INDEX idx_sort (sort_order)
);

CREATE TABLE user_group_members (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    user_group_id BIGINT NOT NULL,
    joined_at DATETIME,
    expired_at DATETIME, -- 그룹 만료일 (선택)
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (user_group_id) REFERENCES user_groups(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_group (user_id, user_group_id),
    INDEX idx_user (user_id),
    INDEX idx_group (user_group_id)
);
```

**⚠️ 용도 예시:**
- VIP 회원, 교사 회원, 학생 회원 등 구분
- 그룹별 할인 혜택, 우선 신청권 등

---

### 8️⃣ 메일/SMS 관리

```sql
CREATE TABLE mail_sms_templates (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    type ENUM('email', 'sms', 'both'),
    
    -- 템플릿 내용
    subject VARCHAR(255), -- 이메일 제목
    content TEXT NOT NULL,
    
    -- 변수 사용 (예: {name}, {program_title})
    available_variables JSON,
    
    -- 사용처
    usage_type VARCHAR(100), -- 'application_approved', 'application_rejected' 등
    
    -- 상태
    is_active BOOLEAN DEFAULT true,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_type_usage (type, usage_type),
    INDEX idx_active (is_active)
);

CREATE TABLE mail_sms_logs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    
    -- 수신자
    user_id BIGINT,
    recipient_name VARCHAR(100),
    recipient_contact VARCHAR(50), -- SMS용
    recipient_email VARCHAR(255), -- Email용
    
    -- 발송 정보
    type ENUM('email', 'sms'),
    template_id BIGINT, -- 템플릿 사용 시
    subject VARCHAR(255),
    content TEXT,
    
    -- 발송 상태
    status ENUM('pending', 'sent', 'failed'),
    sent_at DATETIME,
    error_message TEXT,
    
    -- 관련 정보
    related_type VARCHAR(100), -- 'application', 'program' 등
    related_id BIGINT,
    
    -- 발송자
    sent_by BIGINT, -- 발송한 관리자
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (template_id) REFERENCES mail_sms_templates(id),
    FOREIGN KEY (sent_by) REFERENCES users(id),
    
    INDEX idx_user (user_id),
    INDEX idx_status (status),
    INDEX idx_related (related_type, related_id),
    INDEX idx_sent_at (sent_at)
);
```

**⚠️ 중요 포인트:**
- **신청 승인/거부 시 자동 발송** 기능
- **템플릿 관리**로 반복 작업 줄이기
- **발송 로그**로 추적 가능

---

### 9️⃣ 통계 테이블 추가 고려사항

**이미 있는 테이블:**
- `visitor_logs` ✅
- `daily_visitor_stats` ✅

**추가 고려:**
```sql
-- 프로그램 통계
CREATE TABLE program_statistics (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    program_id BIGINT,
    date DATE,
    
    -- 신청 통계
    total_applications INT DEFAULT 0,
    approved_applications INT DEFAULT 0,
    rejected_applications INT DEFAULT 0,
    cancelled_applications INT DEFAULT 0,
    
    -- 참여 통계
    total_participants INT DEFAULT 0,
    actual_participants INT DEFAULT 0, -- 실제 참여
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (program_id) REFERENCES programs(id),
    UNIQUE KEY unique_program_date (program_id, date),
    INDEX idx_date (date)
);
```

---

## 🔗 테이블 관계도 (ERD)

```
users (1) ──→ (N) applications
users (1) ──→ (N) user_group_members
users (1) ──→ (N) mail_sms_logs (sent_by)

programs (1) ──→ (N) program_schedules
programs (1) ──→ (N) applications
programs (1) ──→ (N) program_statistics

program_schedules (1) ──→ (N) applications

applications (1) ──→ (N) rosters (단체 신청만)
applications (N) ──→ (1) schools

schools (1) ──→ (N) applications

user_groups (1) ──→ (N) user_group_members
user_groups (1) ──→ (N) users (N:M 관계)

mail_sms_templates (1) ──→ (N) mail_sms_logs
```

---

## ✅ 핵심 체크리스트

### 회원 테이블
- [ ] 개인/단체/학교 구분 필드 추가
- [ ] 학교/기관명 필드 추가
- [ ] 학년/연령대 필드 추가
- [ ] 주소 정보 필요 여부
- [ ] 마케팅 동의 필드

### 프로그램 테이블
- [ ] 개인용/단체용 구분 필수
- [ ] 신청 기간 vs 운영 기간 분리
- [ ] 최소/최대 인원 (단체용)
- [ ] 일정별 관리를 위한 schedules 테이블 분리

### 신청 테이블
- [ ] 개인/단체 신청 구분
- [ ] 승인 프로세스 (pending/approved/rejected)
- [ ] 단체 신청 시 명단(rosters) 테이블 연결
- [ ] 결제 정보 필드

### 관계 설정
- [ ] `users` ↔ `applications` (1:N)
- [ ] `programs` ↔ `program_schedules` (1:N)
- [ ] `program_schedules` ↔ `applications` (1:N)
- [ ] `applications` ↔ `rosters` (1:N, 단체만)
- [ ] `schools` ↔ `applications` (1:N)
- [ ] `users` ↔ `user_groups` (N:M)

---

## 🚀 개발 우선순위

### Phase 1: 핵심 기능 (1-2주)
1. **프로그램 관리** 기능 구현
2. **학교 관리** 기능 구현
3. **신청 관리** 기본 구현

### Phase 2: 확장 기능 (1주)
4. **예약 캘린더** 구현
5. **회원 그룹 관리** 구현
6. **홈페이지 콘텐츠 관리** 완성

### Phase 3: 부가 기능 (1주)
7. **메일/SMS 발송** 기능 구현
8. **통계 기능** 구현
9. **UI/UX 최종 점검**

---

## 📝 참고사항

- 이 문서는 기획서 분석을 바탕으로 작성되었습니다
- 실제 구현 시 비즈니스 요구사항에 따라 필드 조정이 필요할 수 있습니다
- 마이그레이션 파일 생성 시 이 문서를 참고하여 순서대로 진행하세요
- 각 테이블의 인덱스는 성능 최적화를 위해 신중히 설계되었습니다

---

*문서 버전: 1.0*  
*최종 수정: 2025-01-27*
