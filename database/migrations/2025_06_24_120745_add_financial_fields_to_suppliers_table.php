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
        Schema::table('suppliers', function (Blueprint $table) {
            $table->decimal('total_cost', 15, 2)->default(0)->after('address');
            $table->decimal('pay', 15, 2)->default(0)->after('total_cost');
            $table->decimal('balance', 15, 2)->default(0)->after('pay');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn(['total_cost', 'pay', 'balance']);
        });
    }
};
