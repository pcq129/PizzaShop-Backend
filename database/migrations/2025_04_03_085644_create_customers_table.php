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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('mobile', 20);
            $table->string('email', 50);
            $table->string('name', 20);
            $table->integer('status',5)->default(null)->nullable(true);
            // null = no status, 1 = waiting, 2 = ordered, 3 = dining

            // $table->string('status', 30)->nullable(true)->default(null);
            $table->integer('head_count')->default(0);
            $table->unsignedBigInteger('section_id')->default(null)->nullable(true)->references('id')->on('sections');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
