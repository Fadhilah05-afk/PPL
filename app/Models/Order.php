<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_code',
        'table_number',
        'order_type',
        'customer_name',
        'customer_phone',
        'customer_email',
        'subtotal',
        'service_fee',
        'discount',
        'voucher_code',
        'total_price',
        'payment_method',
        'payment_status',
        'status',
        'notes',
    ];

    protected $casts = [
        'subtotal' => 'float',
        'service_fee' => 'float',
        'discount' => 'float',
        'total_price' => 'float',
        'table_number' => 'integer',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
