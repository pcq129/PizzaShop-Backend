<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('modifier_modifier_group', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->autoIncrement();
            $table->unsignedBigInteger('modifier_id');
            $table->unsignedBigInteger('modifier_group_id');
            $table->foreign('modifier_id')
            ->references('id')
            ->on('modifiers')
            ->onDelete('cascade');
            $table->foreign('modifier_group_id')
            ->references('id')
            ->on('modifier_groups')
            ->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modifier_modifier_group');
    }
};
