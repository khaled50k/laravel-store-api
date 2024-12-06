<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Refunds extends Model
{
    protected $fillable = ['order_id', 'payment_id', 'amount', 'reason'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payments::class);
    }
}
