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
            $table->decimal('whole_price', 10, 2)->nullable()->after('expiry_date_margin');
            $table->decimal('wholesale_discount', 5, 2)->nullable()->after('whole_price');
            $table->decimal('final_whole_price', 10, 2)->nullable()->after('wholesale_discount');
               $table->string('certificate_path')->nullable()->after('wholesale_discount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['whole_price', 'wholesale_discount', 'final_whole_price','certificate_path']);
        });
    }
};
