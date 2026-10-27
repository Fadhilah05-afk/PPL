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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_code')->unique();
            $table->integer('table_number')->nullable();
            $table->enum('order_type', ['dine-in', 'take-away'])->default('dine-in');
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->string('customer_email')->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('service_fee', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->string('voucher_code')->nullable();
            $table->decimal('total_price', 12, 2)->default(0);
            $table->string('payment_method')->default('cashier');
            $table->enum('payment_status', ['pending', 'paid'])->default('pending');
            $table->enum('status', ['pending', 'processing', 'completed', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
