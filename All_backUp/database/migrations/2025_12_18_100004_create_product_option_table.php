<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 'product_option' 피벗 테이블 생성을 위한 마이그레이션
 *
 * 어떤 품목(product)이 어떤 옵션(option)을 가질 수 있는지 연결하는 다대다 관계 테이블입니다.
 * 예: '전단지' 품목은 'A4', 'A5' 사이즈 옵션을 가질 수 있음
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
        Schema::create('product_option', function (Blueprint $table) {
            // 복합 기본 키 (두 ID의 조합이 고유해야 함)
            $table->primary(['product_id', 'option_id']);

            // 외래 키 정의
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('option_id')->constrained()->onDelete('cascade');
            
            // 이 테이블 자체에는 created_at, updated_at이 필요 없는 경우가 많습니다.
        });
    }

    /**
     * 마이그레이션을 롤백합니다.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_option');
    }
};
