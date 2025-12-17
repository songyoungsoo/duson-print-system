<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 'options' 테이블 생성을 위한 마이그레이션
 *
 * 실제 개별 옵션 항목들을 정의합니다. (예: "아트지", "A4", "UV코팅")
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
        Schema::create('options', function (Blueprint $table) {
            $table->id();

            // 어떤 옵션 그룹에 속하는지 정의
            $table->foreignId('option_group_id')->constrained('option_groups')->onDelete('cascade');
            
            // 옵션 값 정보
            $table->string('name'); // 옵션 표시명 (예: "아트지 150g")
            $table->string('value')->unique(); // 시스템에서 사용할 값 (예: "art150")
            $table->integer('order_column')->default(0); // 그룹 내 표시 순서

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
        Schema::dropIfExists('options');
    }
};
