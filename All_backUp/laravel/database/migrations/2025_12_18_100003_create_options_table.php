<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('la_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('option_group_id')->constrained('la_option_groups')->onDelete('cascade');
            $table->string('name');
            $table->string('value')->unique();
            $table->integer('order_column')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('la_options');
    }
};
