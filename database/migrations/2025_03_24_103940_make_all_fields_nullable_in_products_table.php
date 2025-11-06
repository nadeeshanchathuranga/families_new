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
        Schema::table('products', function (Blueprint $table) {
            $table->string('name')->nullable()->change();
            $table->decimal('cost_price', 8, 2)->nullable()->change();
            $table->decimal('selling_price', 8, 2)->nullable()->change();
            $table->integer('stock_quantity')->nullable()->change();
            $table->string('barcode')->nullable()->change();
            // image already nullable, so no need to change it
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('name')->nullable(false)->change();
            $table->decimal('cost_price', 8, 2)->nullable(false)->change();
            $table->decimal('selling_price', 8, 2)->nullable(false)->change();
            $table->integer('stock_quantity')->nullable(false)->change();
            $table->string('barcode')->nullable(false)->change();
        });
    }
};
