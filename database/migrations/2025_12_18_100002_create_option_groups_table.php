<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 'option_groups' 테이블 생성을 위한 마이그레이션
 *
 * 옵션들의 "카테고리"를 정의합니다. (예: "용지", "사이즈", "후가공")
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
        Schema::create('option_groups', function (Blueprint $table) {
            $table->id();
            
            // 옵션 그룹 정보
            $table->string('name'); // 그룹명 (예: "용지 종류", "코팅 옵션")
            $table->string('key')->unique(); // 시스템에서 사용할 영문 키 (예: "paper_type", "coating")
            $table->string('display_type')->default('select'); // UI 표시 형태 (예: 'select', 'radio', 'checkbox')
            $table->integer('order_column')->default(0); // 표시 순서

            $table->timestamps();
        });
    }

    /**
     * 마이그레이션을 롤백합니다.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('option_groups');
    }
};
