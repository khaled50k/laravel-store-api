<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = ['user_id', 'order_number', 'subtotal', 'tax', 'discount', 'total', 'currency', 'status'];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
    // Define the user relationship
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function payment()
    {
        return $this->hasOne(Payments::class);
    }

    public function shipping()
    {
        return $this->hasOne(Shippings::class);
    }
}

