<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('la_products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('calculator_key')->comment('가격을 계산하는데 사용할 계산기 클래스를 식별하는 키');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('la_products');
    }
};
