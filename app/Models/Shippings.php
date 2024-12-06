<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shippings extends Model
{
    protected $fillable = ['order_id', 'name', 'address', 'city', 'state', 'postal_code', 'country', 'phone'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}