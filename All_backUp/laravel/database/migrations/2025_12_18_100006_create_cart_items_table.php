<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('la_cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained('la_carts')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('la_products')->onDelete('cascade');
            $table->integer('quantity');
            $table->integer('unit_price')->nullable();
            $table->integer('supply_price');
            $table->integer('vat_amount');
            $table->integer('total_price');
            $table->json('selected_options'); 
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('la_cart_items');
    }
};
