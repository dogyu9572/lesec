# AWS GitHub Actions 자동 배포 설정 가이드

## 개요
GitHub Actions를 통해 AWS EC2 서버로 자동 배포하기 위한 AWS 리소스 설정 가이드입니다.

## 설정된 리소스 목록

### 1. Network Load Balancer (NLB)
- **이름**: `homepagekorea-NLB`
- **DNS**: `homepagekorea-NLB-b6c09626850aa1d1.elb.ap-northeast-2.amazonaws.com`
- **IP**: `52.78.220.82`
- **용도**: 외부 고정 IP 제공 및 트래픽 라우팅

### 2. NLB 리스너 설정

#### 기존 리스너 (HTTP/HTTPS)
- **포트**: 443 (HTTPS)
- **대상 그룹**: `homepagekorea-NLB-800`
- **포트 매핑**: 443 → EC2:800

#### 새로 추가된 리스너 (SSH)
- **프로토콜**: TCP
- **포트**: 22 (SSH)
- **대상 그룹**: `homepagekorea-NLB-ssh`
- **포트 매핑**: 22 → EC2:22
- **용도**: GitHub Actions SSH 접속용

### 3. 대상 그룹 (Target Group)

#### SSH용 대상 그룹
- **이름**: `homepagekorea-NLB-ssh`
- **프로토콜**: TCP
- **포트**: 22
- **대상**: EC2 인스턴스 (포트 22)
- **헬스 체크**: TCP:22

### 4. 보안 그룹 (Security Group) 설정

#### NLB 보안 그룹 (`homepagekorea-SG-NLB`)
**인바운드 규칙:**
- **HTTP/HTTPS**: 
  - 포트: 443
  - 소스: `0.0.0.0/0` (추후 특정 IP로 제한 권장)
  
- **SSH** (새로 추가):
  - 포트: 22
  - 소스: `0.0.0.0/0` (GitHub Actions IP 대역 또는 특정 IP로 제한 권장)

#### EC2 인스턴스 보안 그룹 (`homepagekorea-SG-Office`)
**인바운드 규칙:**
- **SSH from NLB** (새로 추가):
  - 포트: 22
  - 소스: NLB 보안 그룹 ID (`sg-0262968a4f6c588d2`)

## 설정 단계별 가이드

### 1단계: NLB에 SSH 리스너 추가

1. **AWS 콘솔 접속**
   - EC2 → Load Balancers → `homepagekorea-NLB` 선택

2. **리스너 추가**
   - "Listeners" 탭 → "Add listener" 클릭
   - 설정:
     - **Protocol**: TCP
     - **Port**: 22
     - **Default action**: Forward to → 새 대상 그룹 생성

### 2단계: SSH용 대상 그룹 생성

1. **대상 그룹 생성**
   - 이름: `homepagekorea-NLB-ssh`
   - 프로토콜: TCP
   - 포트: 22
   - VPC: 기존 VPC 선택

2. **대상 등록**
   - EC2 인스턴스 선택
   - 포트: 22
   - "Include as pending below" 체크 후 등록

### 3단계: NLB 보안 그룹 설정

1. **보안 그룹 선택**
   - EC2 → Security Groups → `homepagekorea-SG-NLB` 선택

2. **인바운드 규칙 추가**
   - "Edit inbound rules" 클릭
   - 규칙 추가:
     - **Type**: SSH
     - **Port**: 22
     - **Source**: `0.0.0.0/0` (또는 GitHub Actions IP 대역)
     - 설명: "GitHub Actions SSH 접속용"

### 4단계: EC2 인스턴스 보안 그룹 설정

1. **보안 그룹 선택**
   - EC2 → Security Groups → `homepagekorea-SG-Office` 선택

2. **인바운드 규칙 추가**
   - "Edit inbound rules" 클릭
   - 규칙 추가:
     - **Type**: SSH
     - **Port**: 22
     - **Source**: Custom → NLB 보안 그룹 ID (`sg-0262968a4f6c588d2`)
     - 설명: "NLB를 통한 SSH 접속"

### 5단계: GitHub Actions Secrets 설정

1. **GitHub 저장소 설정**
   - Settings → Secrets and variables → Actions

2. **Secrets 추가/수정**
   - `HOST`: `homepagekorea-NLB-b6c09626850aa1d1.elb.ap-northeast-2.amazonaws.com`
   - `PORT`: `22`
   - `USERNAME`: SSH 사용자명
   - `SSH_KEY`: SSH 개인 키

## 네트워크 흐름도

```
GitHub Actions
    ↓
NLB (homepagekorea-NLB)
    ├─ 포트 443 → EC2:800 (웹 트래픽)
    └─ 포트 22 → EC2:22 (SSH 트래픽)
        ↓
    EC2 인스턴스
```

## 보안 권장사항

1. **NLB 보안 그룹 제한**
   - 현재: `0.0.0.0/0` (모든 IP 허용)
   - 권장: GitHub Actions IP 대역으로 제한
   - GitHub Actions IP: `140.82.112.0/20`, `143.55.64.0/20`, `185.199.108.0/22`, `192.30.252.0/22`, `2a0a:a440::/29`, `2606:50c0::/32`

2. **SSH 키 관리**
   - 강력한 SSH 키 사용
   - 정기적인 키 로테이션
   - GitHub Secrets에 안전하게 저장

## 문제 해결

### SSH 연결 실패 시 확인 사항

1. **NLB 리스너 확인**
   - TCP:22 리스너가 존재하는지 확인
   - 대상 그룹이 올바르게 연결되어 있는지 확인

2. **보안 그룹 확인**
   - NLB 보안 그룹: SSH(22) 인바운드 규칙 확인
   - EC2 보안 그룹: NLB 보안 그룹에서 오는 SSH(22) 허용 확인

3. **대상 그룹 상태 확인**
   - 대상 그룹의 헬스 체크 상태 확인
   - EC2 인스턴스가 "healthy" 상태인지 확인

4. **GitHub Secrets 확인**
   - HOST: NLB DNS 이름이 올바른지 확인
   - PORT: 22로 설정되어 있는지 확인

## 참고 정보

- **리전**: ap-northeast-2 (서울)
- **NLB 보안 그룹 ID**: `sg-0262968a4f6c588d2`
- **NLB DNS**: `homepagekorea-NLB-b6c09626850aa1d1.elb.ap-northeast-2.amazonaws.com`
- **NLB IP**: `52.78.220.82`

## 추가 설정 (선택사항)

### GitHub Actions IP 대역으로 제한하기

NLB 보안 그룹 인바운드 규칙을 다음과 같이 수정:

```
Type: SSH
Port: 22
Source: 
  - 140.82.112.0/20
  - 143.55.64.0/20
  - 185.199.108.0/22
  - 192.30.252.0/22
```

또는 GitHub Actions Meta API를 사용하여 동적으로 IP 대역 가져오기:
```bash
curl https://api.github.com/meta | jq '.actions[]'
```

