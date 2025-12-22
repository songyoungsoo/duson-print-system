<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('la_product_option', function (Blueprint $table) {
            $table->primary(['product_id', 'option_id']);
            $table->foreignId('product_id')->constrained('la_products')->onDelete('cascade');
            $table->foreignId('option_id')->constrained('la_options')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('la_product_option');
    }
};
