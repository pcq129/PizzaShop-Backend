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
        Schema::create('items', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            // $table->foreign('id')->references('item_category_id')->on('item_categories');
            $table->string('name', 40);
            $table->string('description', 200)->nullable(true);
            $table->integer('category_id');
            $table->integer('quantity');
            $table->integer('rate');
            $table->integer('tax')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
