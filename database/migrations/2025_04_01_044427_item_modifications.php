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
        Schema::table('items', function(Blueprint $table){
           $table->renameColumn('tax', 'tax_percentage');
           $table->enum('item_type', ['veg', 'non-veg', 'vegan'])->default('veg')->after('name');
           $table->boolean('default_tax')->default(1)->after('rate');
           $table->boolean('available')->default(1);
           $table->integer('short_code')->nullable(true);
           $table->string('image')->nullable(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function(Blueprint $table){
            $table->renameColumn('tax_percentage', 'tax');
            $table->dropColumn('item_type');
            $table->dropColumn('default_tax');
            $table->dropColumn('available');
            $table->dropColumn('short_code');
            $table->dropColumn('image');
        });
    }
};
