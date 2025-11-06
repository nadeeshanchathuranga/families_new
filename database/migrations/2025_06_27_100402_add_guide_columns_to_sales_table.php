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
        Schema::table('sales', function (Blueprint $table) {
            $table->boolean('is_whole')->default(0)->after('cash');
            $table->string('guide_name')->nullable()->after('is_whole');
            $table->decimal('guide_comi', 10, 2)->nullable()->after('guide_name');
            $table->decimal('guide_cash', 10, 2)->nullable()->after('guide_comi');
            $table->decimal('total_to_include_guide', 10, 2)->nullable()->after('guide_cash');
            $table->boolean('credit_bill')->default(0)->after('total_to_include_guide');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
             $table->dropColumn([
                'is_whole',
                'guide_name',
                'guide_comi',
                'guide_cash',
                'total_to_include_guide',
                'credit_bill',
            ]);
        });
    }
};
