<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->autoIncrement();
            $table->unsignedBigInteger('customer_id');
            $table->enum('order_status', ['Completed', 'Ordered'])->nullable(true)->default(null);
            $table->json('order_data');
            $table->enum('payment_mode',['Cash', 'Online', 'Card']);
            $table->enum('payment_status', ['Completed', 'Pending', '-']);
            $table->integer('bill_amount')->nullable(true)->default(null);
            $table->json('rating')->nullable(true)->default(null);
            $table->string('comment', 180)->nullable(true)->default(null);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
