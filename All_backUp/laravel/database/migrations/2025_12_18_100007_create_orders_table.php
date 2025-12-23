<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('la_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('la_users')->onDelete('cascade');
            $table->string('guest_name')->nullable();
            $table->string('guest_email')->nullable();
            $table->string('guest_phone_number')->nullable();
            $table->integer('total_supply_price');
            $table->integer('total_vat_amount');
            $table->integer('total_final_price');
            $table->string('shipping_name');
            $table->string('shipping_phone_number');
            $table->string('shipping_zip_code');
            $table->string('shipping_address1');
            $table->string('shipping_address2')->nullable();
            $table->text('shipping_notes')->nullable();
            $table->string('payment_method');
            $table->string('payment_status')->default('pending');
            $table->string('transaction_id')->nullable();
            $table->string('order_status')->default('pending');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('la_orders');
    }
};
