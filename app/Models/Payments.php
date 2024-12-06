<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payments extends Model
{
    protected $fillable = ['order_id', 'payment_gateway', 'transaction_id', 'amount', 'status'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
