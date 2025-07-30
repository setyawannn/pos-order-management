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
            $table->string('table_number');
            $table->string('customer_name');
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();
            $table->decimal('total_amount', 10, 2);
            $table
                ->enum('status', [
                    'waiting_payment',
                    'payment_failed',
                    'in_queue',
                    'in_progress',
                    'completed',
                    'cancelled',
                ])
                ->default('waiting_payment');
            $table->string('payment_method')->nullable();
            $table
                ->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])
                ->default('pending');
            $table->string('transaction_id')->nullable()->unique();
            $table->json('payment_payload')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('order_code');
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
