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
        Schema::create('modifiers', function (Blueprint $table) {
            // $table->id();
            $table->unsignedBigInteger('id')->autoIncrement()->unique();
            $table->string('name');
            $table->integer('rate');
            $table->enum('unit', ['grams','pieces']);
            $table->integer('quantity');
            $table->string('description', 150)->nullable(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // Schema::table('modifiers', function(Blueprint $table){
        //     $table->foreign('modifier_group_id')->references('id')->on('modifier_groups');
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modifiers');
    }
};
