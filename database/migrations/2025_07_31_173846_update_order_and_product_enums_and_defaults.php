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
        Schema::table('orders', function (Blueprint $table) {
            $table
                ->enum('status', [
                    'waiting_payment',
                    'payment_failed',
                    'in_queue',
                    'in_progress',
                    'ready_to_serve',
                    'completed',
                    'cancelled',
                ])
                ->default('waiting_payment')
                ->change();

            $table
                ->enum('payment_status', [
                    'pending',
                    'authorize',
                    'capture',
                    'settlement',
                    'deny',
                    'cancel',
                    'refund',
                    'partial_refund',
                    'chargeback',
                    'partial_chargeback',
                    'expire',
                    'failure',
                ])
                ->default('pending')
                ->change();

            $table->decimal('total_amount', 15, 0)->change();
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('price', 15, 0)->change();
            $table->decimal('subtotal', 15, 0)->change();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_stock_managed')->default(false)->change();

            $table->decimal('price', 15, 0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table
                ->enum('status', [
                    'waiting_payment',
                    'payment_failed',
                    'in_queue',
                    'in_progress',
                    'completed',
                    'cancelled',
                ])
                ->default('waiting_payment')
                ->change();

            $table
                ->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])
                ->default('pending')
                ->change();

            $table->decimal('total_amount', 10, 2)->change();
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('price', 8, 2)->change();
            $table->decimal('subtotal', 10, 2)->change();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_stock_managed')->default(true)->change();

            $table->decimal('price', 8, 2)->change();
        });
    }
};
