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
            $table->integer('preorder_level_qty')->nullable()->after('grn_id');
            $table->integer('expiry_date_margin')->nullable()->after('preorder_level_qty');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
         $table->dropColumn(['preorder_level_qty', 'expiry_date_margin']);
        });
    }
};
