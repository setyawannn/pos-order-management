<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items', 'status')) {
                $indexes = collect(DB::select("SHOW INDEXES FROM order_items WHERE Column_name = 'status'"))->pluck('Key_name')->toArray();
                foreach ($indexes as $index) {
                    $table->dropIndex($index);
                }
                $table->dropColumn('status');
            }

            if (!Schema::hasColumn('order_items', 'is_done')) {
                $table->boolean('is_done')->default(false)->after('notes');
                $table->index('is_done');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items', 'is_done')) {
                $table->dropIndex(['is_done']);
                $table->dropColumn('is_done');
            }
        });
    }
};
