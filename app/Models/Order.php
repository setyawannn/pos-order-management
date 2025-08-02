<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_code',
        'table_number',
        'customer_name',
        'customer_email',
        'customer_phone',
        'order_type',
        'total_amount',
        'status',
        'payment_method',
        'payment_status',
        'transaction_id',
        'payment_payload',
        'notes',
        'sequence',
    ];

    protected $casts = [
        'payment_payload' => 'array',
        'total_amount' => 'decimal:2',
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            // Generate order_code and sequence before saving the model
            if (empty($order->order_code)) {
                $orderData = self::generateOrderCodeAndSequence();
                $order->order_code = $orderData['order_code'];
                $order->sequence = $orderData['sequence'];
            }
        });
    }

    /**
     * Generate a unique sequential order code and its daily sequence number.
     * Format: OM-YYYYMMDD00001
     *
     * @return array Returns an array with 'order_code' and 'sequence'.
     * @throws \Exception If a unique code cannot be generated after retries.
     */
    protected static function generateOrderCodeAndSequence(): array
    {
        $today = Carbon::now();
        $datePrefix = 'OM-' . $today->format('Ymd');

        return DB::transaction(function () use ($datePrefix, $today) {
            $lastSequence = self::whereDate('created_at', $today)->max('sequence');

            $nextSequence = ($lastSequence ?? 0) + 1;

            $paddedSequence = str_pad($nextSequence, 5, '0', STR_PAD_LEFT);

            $newOrderCode = $datePrefix . $paddedSequence;

            return [
                'order_code' => $newOrderCode,
                'sequence' => $nextSequence,
            ];
        });
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
