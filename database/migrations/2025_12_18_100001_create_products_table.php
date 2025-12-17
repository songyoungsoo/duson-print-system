<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 'products' 테이블 생성을 위한 마이그레이션
 *
 * 이 테이블은 판매하는 9개 핵심 품목의 정보를 저장합니다.
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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            
            // 제품 기본 정보
            $table->string('name'); // 제품명 (예: "전단지", "명함")
            $table->string('slug')->unique(); // URL에 사용될 식별자 (예: "flyer", "business-card")
            $table->text('description')->nullable(); // 제품 상세 설명
            $table->boolean('is_active')->default(true); // 사이트 노출 여부

            // 핵심: 가격 계산 엔진과 연결하기 위한 키
            // 이 키를 사용하여 CalculatorFactory가 적절한 계산기 클래스를 찾아냅니다.
            $table->string('calculator_key')->comment('가격을 계산하는데 사용할 계산기 클래스를 식별하는 키');

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
        Schema::dropIfExists('products');
    }
};
