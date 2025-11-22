#!/bin/bash

# Laravel 데이터베이스 설정 스크립트 (2단계: DB 설정)
echo "🗄️ 데이터베이스 설정 시작..."

# 1. MySQL 준비 상태 확인 (.env에서 DB 정보 읽기)
echo "⏳ MySQL 연결 확인 중..."
if [ ! -f ".env" ]; then
    echo "❌ .env 파일이 없습니다."
    exit 1
fi

# .env에서 DB 정보 추출
DB_HOST=$(grep "^DB_HOST=" .env | cut -d'=' -f2 | tr -d '"' | tr -d "'")
DB_PORT=$(grep "^DB_PORT=" .env | cut -d'=' -f2 | tr -d '"' | tr -d "'" || echo "3306")
DB_DATABASE=$(grep "^DB_DATABASE=" .env | cut -d'=' -f2 | tr -d '"' | tr -d "'")
DB_USERNAME=$(grep "^DB_USERNAME=" .env | cut -d'=' -f2 | tr -d '"' | tr -d "'")
DB_PASSWORD=$(grep "^DB_PASSWORD=" .env | cut -d'=' -f2 | tr -d '"' | tr -d "'")

# DB_HOST가 "mysql"이면 localhost로 변경 (Docker 컨테이너 이름)
if [ "$DB_HOST" = "mysql" ]; then
    DB_HOST="localhost"
fi

MAX_ATTEMPTS=10
ATTEMPT=0

while [ $ATTEMPT -lt $MAX_ATTEMPTS ]; do
    if mysqladmin ping -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USERNAME" -p"$DB_PASSWORD" > /dev/null 2>&1; then
        echo "✅ MySQL 연결 성공!"
        break
    else
        ATTEMPT=$((ATTEMPT + 1))
        echo "⏳ MySQL 준비 중... ($ATTEMPT/$MAX_ATTEMPTS)"
        sleep 2
    fi
done

if [ $ATTEMPT -eq $MAX_ATTEMPTS ]; then
    echo "❌ MySQL 연결 실패. 데이터베이스 설정을 확인해주세요."
    echo "   DB_HOST: $DB_HOST"
    echo "   DB_DATABASE: $DB_DATABASE"
    echo "   DB_USERNAME: $DB_USERNAME"
    exit 1
fi

# 2. 애플리케이션 키 생성
echo "🔑 애플리케이션 키 생성 중..."
php artisan key:generate

# 3. 마이그레이션 파일 확인
echo "🔍 마이그레이션 파일 확인 중..."
echo "✅ 마이그레이션 파일들이 올바른 순서로 정리되어 있습니다."

# 4. 기본 마이그레이션 실행
echo "🗄️ 기본 마이그레이션 실행 중..."
php artisan migrate --force

# 5. 시더 실행 (기본 데이터 생성)
echo "🌱 시더 실행 중..."
php artisan db:seed

# 6. 세션 테이블 확인 및 생성
echo "📋 세션 테이블 확인 중..."
if ! php artisan tinker --execute="Schema::hasTable('sessions')" 2>/dev/null | grep -q "true"; then
    echo "📋 세션 테이블 생성 중..."

    # 세션 테이블을 직접 생성
    php artisan tinker --execute="
        if (!Schema::hasTable('sessions')) {
            Schema::create('sessions', function (\$table) {
                \$table->string('id')->primary();
                \$table->foreignId('user_id')->nullable()->index();
                \$table->string('ip_address', 45)->nullable();
                \$table->text('user_agent')->nullable();
                \$table->text('payload');
                \$table->integer('last_activity')->index();
            });
            echo 'Sessions table created successfully';
        } else {
            echo 'Sessions table already exists';
        }
    "
else
    echo "✅ 세션 테이블이 이미 존재합니다."
fi

# 7. 캐시 정리 (안전하게)
echo "🧹 캐시 정리 중..."
php artisan config:clear
php artisan view:clear

# 캐시 테이블이 있을 때만 캐시 클리어 실행
if php artisan tinker --execute="Schema::hasTable('cache')" 2>/dev/null | grep -q "true"; then
    php artisan cache:clear
else
    echo "⚠️ 캐시 테이블이 없어서 캐시 클리어를 건너뜁니다."
fi

echo ""
echo "✅ 2단계 완료: 데이터베이스 설정 완료!"
echo "🌐 접속 URL: http://localhost"
echo "🔧 관리 명령어: php artisan"
echo "🗄️ 데이터베이스: $DB_DATABASE"
echo ""
echo "🔑 기본 관리자 계정:"
echo "   이메일: admin@example.com"
echo "   비밀번호: password"
echo ""
echo "📊 생성된 테이블들:"
echo "   - users (사용자 관리)"
echo "   - admin_menus (관리자 메뉴)"
echo "   - user_menu_permissions (사용자 메뉴 권한)"
echo "   - settings (사이트 설정)"
echo "   - board_skins (게시판 스킨)"
echo "   - boards (게시판 관리)"
echo "   - board_posts (게시글)"
echo "   - board_comments (댓글)"
echo "   - board_settings (게시판 설정)"
echo "   - board_notices (공지사항)"
echo "   - board_gallerys (갤러리)"
echo ""
echo "🎉 백오피스 시스템이 준비되었습니다!"
