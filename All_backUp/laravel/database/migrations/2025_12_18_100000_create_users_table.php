<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 'la_users' 테이블 생성을 위한 마이그레이션
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('la_users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('company_name')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('address1')->nullable();
            $table->string('address2')->nullable();
            $table->string('zip_code')->nullable();
            $table->string('role')->default('user');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('la_users');
    }
};
