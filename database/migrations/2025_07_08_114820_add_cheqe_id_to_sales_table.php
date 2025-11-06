<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (!Schema::hasColumn('sales', 'cheque_id')) {
                if (Schema::hasTable('cheques')) {
                    $table->foreignId('cheque_id')->nullable()->constrained('cheques')->nullOnDelete();
                } else {
                    // Add column without constraint if cheques table doesn't exist
                    $table->unsignedBigInteger('cheque_id')->nullable();
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (Schema::hasColumn('sales', 'cheque_id')) {
                // Try to drop foreign key first if it exists
                try {
                    $table->dropForeign(['cheque_id']);
                } catch (Exception $e) {
                    // Foreign key doesn't exist, continue
                }
                $table->dropColumn('cheque_id');
            }
        });
    }
};
