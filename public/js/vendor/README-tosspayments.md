# 토스 결제위젯(standard) 연동

## 토스페이 작업 절차

### 1. 환경 설정

- **`.env`**: `TOSS_CLIENT_KEY`, `TOSS_SECRET_KEY` (결제위젯 연동 키·시크릿 키)
- **`config/services.php`**: `toss` 설정 (`client_key`, `secret_key` from env)

### 2. 백오피스

- 개인 프로그램 편집 → 결제수단에 **온라인 카드결제** 체크 후 저장  
- 해당 프로그램만 "신청하기" 클릭 시 토스 결제 흐름으로 진입

### 3. 결제 흐름

1. **개인 프로그램 선택** (`/program/{type}/select-individual`)  
   - 날짜·프로그램 선택 후 "신청하기" 클릭

2. **온라인 카드 결제인 경우**
   - `POST /program/{type}/payment/prepare`  
     - `program_reservation_id`, `participation_date` 전달  
     - 응답: `client_key`, `orderId`, `amount`, `success_url`, `fail_url`
   - 토스 SDK(결제위젯) 로드 → `#toss-payment-widget`, `#toss-agreement` 렌더
   - 결제 모달(`#pop_toss_payment`) 표시 → 결제수단 선택 후 "결제하기"

3. **결제 완료/실패**
   - 성공: `GET .../payment/success?orderId=...&paymentKey=...&amount=...`  
     - 서버에서 confirm API 호출 후 DB 반영, 완료 페이지 리다이렉트
   - 실패: `GET .../payment/fail?code=...&message=...`  
     - 에러 메시지 표시 후 이전 단계 유도

### 4. 주요 파일

| 구분 | 경로 |
|------|------|
| 결제 API | `App\Http\Controllers\ProgramPaymentController` (prepare, success, fail) |
| 토스 API | `App\Services\TossPaymentsService` |
| 라우트 | `routes/web.php` → `program.payment.prepare` / `success` / `fail` |
| 뷰 | `resources/views/program/select-individual.blade.php` (모달 `#pop_toss_payment`, `data-prepare-url`) |
| 스크립트 | `public/js/program/program.js` (prepare AJAX, 위젯 렌더, `requestPayment`) |
| SDK | `public/js/vendor/tosspayments-v2.js` (셀프호스팅용, 아래 참고) |

### 5. 배포·테스트 체크

- `.env`에 토스 키가 올바르게 들어가는지 확인
- `tosspayments-v2.js` 셀프호스팅 시 **배포 산출물에 포함** 여부 확인
- 개인 프로그램에 "온라인 카드결제"가 체크된 항목으로 신청 → 결제 모달 정상 노출 여부 확인

---

## 셀프호스팅 (CDN 차단 시)

`js.tosspayments.com` 접근이 막혀 있을 때 사용합니다.

### 설치 방법

1. **토스 CDN에 접근 가능한 환경**에서 (예: 나이스엠 결제되는 PC, 집 네트워크):
   - 브라우저로 `https://js.tosspayments.com/v2/standard` 접속
   - 페이지 전체 선택(Ctrl+A) → 복사 → 새 파일에 붙여넣기  
   - 또는 **링크 우클릭 → 다른 이름으로 링크 저장** 등으로 파일 저장

2. 저장한 파일 이름을 **`tosspayments-v2.js`** 로 변경

3. 이 파일을 **`public/js/vendor/`** 폴더에 넣기  
   최종 경로: `public/js/vendor/tosspayments-v2.js`

4. 배포 후 개인 프로그램 **온라인 카드 결제** → 신청하기 → 결제 모달이 뜨면 성공

### 주의

- **Access Denied** XML 화면이 뜨면 저장하지 말 것. 실제 **JavaScript 코드**가 보일 때만 저장하세요.
- 파일을 추가하지 않으면 결제 화면에서 "결제 스크립트를 불러올 수 없습니다" 오류가 납니다. 반드시 위 1~3을 진행한 뒤 `tosspayments-v2.js`를 배포에 포함하세요.

### 참고

- 토스 SDK 업데이트 시, 위 1~3을 다시 수행해 파일을 교체하면 됩니다.
