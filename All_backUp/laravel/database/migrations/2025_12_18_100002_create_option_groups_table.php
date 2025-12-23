<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('la_option_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('key')->unique();
            $table->string('display_type')->default('select');
            $table->integer('order_column')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('la_option_groups');
    }
};
