<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 'users' 테이블 생성을 위한 마이그레이션
 *
 * 이 테이블은 사이트의 모든 사용자(고객 및 관리자) 정보를 저장합니다.
 */
return new class extends Migration
{
    /**
     * 마이그레이션을 실행합니다.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id(); // bigint, auto-increment, primary key
            
            // 기본 로그인 정보
            $table->string('name'); // 사용자 이름 또는 회사명
            $table->string('email')->unique(); // 로그인 ID로 사용될 이메일
            $table->timestamp('email_verified_at')->nullable(); // 이메일 인증 시간
            $table->string('password'); // 해시된 비밀번호

            // 추가 회원 정보 (인쇄몰 특화)
            $table->string('company_name')->nullable(); // 회사명 (개인 회원의 경우 null)
            $table->string('phone_number')->nullable(); // 연락처
            $table->string('address1')->nullable(); // 기본 주소
            $table->string('address2')->nullable(); // 상세 주소
            $table->string('zip_code')->nullable(); // 우편번호

            // 사용자 역할(등급) 구분
            // 예: 'user', 'admin', 'manager' 등
            $table->string('role')->default('user');

            // Laravel 기본 필드
            $table->rememberToken(); // '로그인 유지' 기능용 토큰
            $table->timestamps(); // created_at, updated_at 자동 생성
        });
    }

    /**
     * 마이그레이션을 롤백합니다.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};
