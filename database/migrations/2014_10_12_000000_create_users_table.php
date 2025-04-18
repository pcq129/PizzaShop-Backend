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
        Schema::create('users', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->increments('id')->unique();
            $table->string('phone', 20);
            $table->string('email', 50)->unique();
            $table->string('first_name', 20);
            $table->string('last_name', 20);
            $table->string('user_name', 30);
            $table->string('address', 150);
            $table->string('country', 50)->default('India');
            $table->string('state', 17)->default('Gujarat');
            $table->string('city', 17)->default('Ahmedabad');
            $table->integer('zipcode')->default('380041');
            $table->integer('role',5)->required()->default(1);
            // 1=admin, 2=acc. manager, 3=customer
            $table->string('password', 180)->required();
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }


};
