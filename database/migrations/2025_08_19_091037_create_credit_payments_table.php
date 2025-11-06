<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('credit_payments', function (Blueprint $table) {
            $table->id();

            // Foreign keys
            $table->unsignedBigInteger('sale_id');
            $table->string('order_id')->nullable(); // store order code as string
            $table->unsignedBigInteger('customer_id')->nullable(); // numeric foreign key

            // Payment fields
            $table->decimal('pending_payment', 10, 2)->default(0);
            $table->decimal('part_payment', 10, 2)->default(0);
            $table->text('description')->nullable();

            // New field from second migration
            $table->string('status')->default('Pending'); // default pending

            $table->timestamps();

            // Foreign key constraints
            $table->foreign('sale_id')->references('id')->on('sales')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_payments');
    }
};
